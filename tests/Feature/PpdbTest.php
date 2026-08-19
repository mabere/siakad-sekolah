<?php

namespace Tests\Feature;

use App\Livewire\Admin\Ppdb\Applications;
use App\Livewire\Admin\Ppdb\Index as PpdbIndex;
use App\Livewire\Public\Ppdb\ActivateStudentAccount;
use App\Livewire\Public\Ppdb\Register;
use App\Livewire\Public\Ppdb\ReRegistration;
use App\Livewire\Public\Ppdb\Status;
use App\Models\AcademicYear;
use App\Models\PpdbApplication;
use App\Models\PpdbPathway;
use App\Models\PpdbPeriod;
use App\Models\PpdbStudentActivation;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\PpdbApplicationService;
use App\Services\PpdbConversionService;
use App\Services\PpdbPeriodService;
use App\Services\PpdbReceiptService;
use App\Services\PpdbSelectionFinalizationService;
use App\Services\PpdbStudentActivationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PpdbTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_period_creates_required_default_pathways(): void
    {
        $school = $this->createSchool();
        $academicYear = $this->createAcademicYear($school);

        $period = app(PpdbPeriodService::class)->create($school->id, [
            'academic_year_id' => $academicYear->id,
            'name' => 'PPDB 2026',
            'code' => 'PPDB-2026',
            'level' => 'SMP',
            'registration_starts_at' => now(),
            'registration_ends_at' => now()->addDays(30),
            'status' => PpdbPeriod::STATUS_OPEN,
            'payment_required' => false,
            'default_registration_fee' => 0,
        ]);

        $this->assertSame(['umum', 'prestasi', 'pindahan'], $period->pathways->pluck('code')->all());
        $this->assertGreaterThan(0, $period->pathways->first()->requirements->count());

        app(PpdbPeriodService::class)->addOptionalPathway($period, PpdbPathway::ZONASI);
        $this->assertDatabaseHas('ppdb_pathways', [
            'ppdb_period_id' => $period->id,
            'code' => PpdbPathway::ZONASI,
            'is_active' => true,
        ]);
    }

    public function test_public_registration_creates_online_application_and_protected_status_lookup(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();

        $component = Livewire::test(Register::class, ['period' => $period->id])
            ->set('pathwayId', (string) $pathway->id)
            ->set('candidateName', 'Calon Siswa Online')
            ->set('candidateGender', 'L')
            ->set('birthPlace', 'Kendari')
            ->set('birthDate', '2012-01-10')
            ->set('previousSchool', 'SD Negeri 1')
            ->set('address', 'Jalan Pendidikan')
            ->set('guardianRelationship', 'ayah')
            ->set('guardianName', 'Orang Tua Online')
            ->set('guardianPhone', '08123456789')
            ->set('contactPhone', '08123456789');

        foreach ($pathway->requirements as $requirement) {
            $component->set('documents.'.$requirement->id, UploadedFile::fake()->create($requirement->code.'.pdf', 100, 'application/pdf'));
        }

        $component->call('submit')->assertHasNoErrors();

        $application = PpdbApplication::query()->with('candidate')->firstOrFail();
        $this->assertSame(PpdbApplication::SOURCE_ONLINE, $application->source);
        $this->assertSame('Calon Siswa Online', $application->candidate->name);
        $this->assertSame($pathway->id, $application->ppdb_pathway_id);
        $this->assertCount($pathway->requirements->count(), $application->documents);

        $admin = User::create([
            'name' => 'Admin Berkas PPDB',
            'email' => 'admin-berkas-ppdb@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');
        $document = $application->documents()->firstOrFail();
        Storage::disk('local')->put($document->file_path, 'pdf-content');
        $this->actingAs($admin)
            ->withSession(['active_role' => 'Admin Sekolah'])
            ->get('/admin/ppdb/documents/'.$document->id.'/download')
            ->assertOk();

        $status = Livewire::test(Status::class)
            ->set('applicationNumber', $application->application_number)
            ->set('accessCode', $component->get('accessCode'))
            ->call('check')
            ->assertHasNoErrors();

        $this->assertSame($application->id, $status->get('application')->id);
    }

    public function test_public_registration_does_not_require_optional_document(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $optionalRequirement = $pathway->requirements()->firstOrFail();
        $optionalRequirement->update(['is_required' => false]);

        $component = Livewire::test(Register::class, ['period' => $period->id])
            ->set('pathwayId', (string) $pathway->id)
            ->set('candidateName', 'Calon Tanpa Berkas Opsional')
            ->set('candidateGender', 'P')
            ->set('birthPlace', 'Kendari')
            ->set('birthDate', '2012-01-10')
            ->set('previousSchool', 'SD Negeri 1')
            ->set('address', 'Jalan Pendidikan')
            ->set('guardianRelationship', 'ibu')
            ->set('guardianName', 'Orang Tua Opsional')
            ->set('guardianPhone', '08123456789')
            ->set('contactPhone', '08123456789');

        foreach ($pathway->requirements()->where('is_required', true)->get() as $requirement) {
            $component->set('documents.'.$requirement->id, UploadedFile::fake()->create($requirement->code.'.pdf', 100, 'application/pdf'));
        }

        $component->call('submit')->assertHasNoErrors();

        $this->assertDatabaseCount('ppdb_documents', $pathway->requirements()->where('is_required', true)->count());
    }

    public function test_public_registration_receipt_is_a_downloadable_pdf(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$application, $accessCode] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Bukti Pendaftaran',
                'gender' => 'L',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Bukti',
            ],
            [
                'relationship' => 'ayah',
                'name' => 'Wali Bukti',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );

        $response = app(PpdbReceiptService::class)->download($application, $accessCode);

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());

        $status = Livewire::test(Status::class)
            ->set('applicationNumber', $application->application_number)
            ->set('accessCode', $accessCode)
            ->call('check')
            ->assertHasNoErrors();
        $receiptUrl = $status->get('receiptUrl');
        $this->assertIsString($receiptUrl);
        $this->assertStringNotContainsString($accessCode, $receiptUrl);

        $download = $this->get($receiptUrl);
        $this->assertSame('application/pdf', $download->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $download->getContent());
    }

    public function test_public_registration_wizard_validates_each_step_before_moving_forward(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $component = Livewire::test(Register::class, ['period' => $period->id])
            ->set('pathwayId', (string) $pathway->id)
            ->set('candidateName', 'Calon Wizard')
            ->set('candidateGender', 'L')
            ->set('birthPlace', 'Kendari')
            ->set('birthDate', '2012-01-10')
            ->set('previousSchool', 'SD Wizard')
            ->set('address', 'Jalan Wizard')
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->set('guardianRelationship', 'ayah')
            ->set('guardianName', 'Wali Wizard')
            ->set('guardianPhone', '08123456789')
            ->set('contactPhone', '08123456789')
            ->call('nextStep')
            ->assertSet('currentStep', 3);

        foreach ($pathway->requirements as $requirement) {
            $component->set('documents.'.$requirement->id, UploadedFile::fake()->create($requirement->code.'.pdf', 100, 'application/pdf'));
        }

        $component->call('nextStep')->assertSet('currentStep', 4);
    }

    public function test_public_registration_can_upload_payment_proof_from_final_step(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $period->update(['payment_required' => true]);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $pathway->update(['registration_fee' => 150000]);

        $component = Livewire::test(Register::class, ['period' => $period->id])
            ->set('pathwayId', (string) $pathway->id)
            ->set('candidateName', 'Calon Pembayaran Langsung')
            ->set('candidateGender', 'P')
            ->set('birthPlace', 'Kendari')
            ->set('birthDate', '2012-01-10')
            ->set('previousSchool', 'SD Asal')
            ->set('address', 'Jalan Pembayaran Langsung')
            ->set('guardianRelationship', 'ibu')
            ->set('guardianName', 'Wali Pembayaran Langsung')
            ->set('guardianPhone', '08123456789')
            ->set('contactPhone', '08123456789');

        foreach ($pathway->requirements as $requirement) {
            $component->set('documents.'.$requirement->id, UploadedFile::fake()->create($requirement->code.'.pdf', 100, 'application/pdf'));
        }

        $component->call('submit')->assertSet('paymentStatus', 'pending');
        $component
            ->set('paymentProof', UploadedFile::fake()->create('bukti-transfer.pdf', 100, 'application/pdf'))
            ->set('paymentNotes', 'Pembayaran biaya pendaftaran')
            ->call('uploadPaymentProof')
            ->assertHasNoErrors()
            ->assertSet('paymentStatus', 'submitted');

        $this->assertDatabaseHas('ppdb_payments', [
            'status' => 'submitted',
            'payment_method' => 'bank_transfer',
        ]);
        $this->assertDatabaseHas('ppdb_payment_histories', [
            'to_status' => 'submitted',
            'notes' => 'Pembayaran biaya pendaftaran',
        ]);
        $this->assertDatabaseHas('ppdb_applications', [
            'payment_status' => 'submitted',
        ]);
    }

    public function test_panitia_can_reset_lost_access_code_without_revealing_the_old_code(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$application, $oldAccessCode] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Reset PIN',
                'gender' => 'P',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Reset',
            ],
            [
                'relationship' => 'ibu',
                'name' => 'Wali Reset',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );
        $panitia = User::create([
            'name' => 'Panitia Reset PIN',
            'email' => 'panitia-reset-pin@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $panitia->assignRole('Panitia PPDB');

        $component = Livewire::actingAs($panitia)
            ->test(Applications::class)
            ->call('showApplication', $application->id)
            ->call('resetAccessCode', $application->id);
        $newAccessCode = $component->get('rotatedAccessCode');

        $application->refresh();
        $this->assertIsString($newAccessCode);
        $this->assertNotSame($oldAccessCode, $newAccessCode);
        $this->assertFalse(Hash::check($oldAccessCode, $application->access_code_hash));
        $this->assertTrue(Hash::check($newAccessCode, $application->access_code_hash));
        $this->assertDatabaseHas('ppdb_audit_logs', [
            'ppdb_application_id' => $application->id,
            'action' => 'access_code_reset',
        ]);
    }

    public function test_panitia_ppdb_role_can_access_ppdb_management_without_using_admin_role(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $panitia = User::create([
            'name' => 'Operator Panitia PPDB',
            'email' => 'operator-panitia-ppdb@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $panitia->assignRole('Panitia PPDB');

        $this->actingAs($panitia)
            ->withSession(['active_role' => 'Panitia PPDB'])
            ->get('/tu/ppdb')
            ->assertOk()
            ->assertSee('Manajemen PPDB');
    }

    public function test_admin_can_reopen_closed_period_for_verification_with_reason(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $period->update([
            'status' => PpdbPeriod::STATUS_CLOSED,
            'verification_ends_at' => now()->addDays(3),
        ]);
        $admin = User::create([
            'name' => 'Admin Buka Verifikasi',
            'email' => 'admin-buka-verifikasi@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');

        Livewire::actingAs($admin)
            ->test(PpdbIndex::class)
            ->call('openReopenVerification', $period->id)
            ->set('reopenVerificationReason', 'Gangguan teknis saat pemeriksaan berkas.')
            ->call('reopenVerification')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ppdb_periods', [
            'id' => $period->id,
            'status' => PpdbPeriod::STATUS_VERIFICATION,
        ]);
        $this->assertDatabaseHas('ppdb_audit_logs', [
            'school_id' => $school->id,
            'action' => 'period_reopened_for_verification',
            'from_status' => PpdbPeriod::STATUS_CLOSED,
            'to_status' => PpdbPeriod::STATUS_VERIFICATION,
        ]);
    }

    public function test_admin_can_cancel_selection_finalization_before_downstream_steps(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$application] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Batal Finalisasi',
                'gender' => 'P',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Koreksi',
            ],
            [
                'relationship' => 'ibu',
                'name' => 'Wali Koreksi',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );
        $application->update([
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
        ]);
        $period->update(['status' => PpdbPeriod::STATUS_SELECTION]);
        app(PpdbSelectionFinalizationService::class)->finalize($period->refresh());

        $admin = User::create([
            'name' => 'Admin Batal Finalisasi',
            'email' => 'admin-batal-finalisasi@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');

        Livewire::actingAs($admin)
            ->test(PpdbIndex::class)
            ->call('openCancelFinalization', $period->id)
            ->set('cancelFinalizationReason', 'Kesalahan teknis pada peringkat seleksi.')
            ->call('cancelFinalization')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ppdb_periods', [
            'id' => $period->id,
            'status' => PpdbPeriod::STATUS_VERIFICATION,
            'selection_finalized_at' => null,
        ]);
        $this->assertDatabaseHas('ppdb_selection_results', [
            'ppdb_application_id' => $application->id,
        ]);
        $this->assertDatabaseHas('ppdb_audit_logs', [
            'school_id' => $school->id,
            'action' => 'selection_finalization_cancelled',
            'to_status' => PpdbPeriod::STATUS_VERIFICATION,
        ]);
    }

    public function test_admin_can_input_offline_application_into_same_workflow(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $admin = User::create([
            'name' => 'Panitia PPDB',
            'email' => 'panitia-ppdb@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Staf Tata Usaha');

        Livewire::actingAs($admin)
            ->test(Applications::class)
            ->call('openOfflineForm')
            ->set('offlinePeriodId', (string) $period->id)
            ->set('offlinePathwayId', (string) $period->pathways()->where('code', PpdbPathway::PINDAHAN)->value('id'))
            ->set('offlineCandidateName', 'Calon Siswa Offline')
            ->set('offlineGender', 'P')
            ->set('offlineBirthPlace', 'Unaaha')
            ->set('offlineBirthDate', '2011-04-10')
            ->set('offlinePreviousSchool', 'SMP Asal')
            ->set('offlineAddress', 'Jalan Offline')
            ->set('offlineGuardianName', 'Wali Offline')
            ->set('offlineGuardianPhone', '08111111111')
            ->set('offlineContactPhone', '08111111111')
            ->call('saveOffline')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ppdb_applications', [
            'school_id' => $school->id,
            'source' => PpdbApplication::SOURCE_OFFLINE,
        ]);

        $application = PpdbApplication::query()->where('source', PpdbApplication::SOURCE_OFFLINE)->firstOrFail();
        $requirement = $period->pathways()->whereKey($application->ppdb_pathway_id)->firstOrFail()->requirements()->firstOrFail();
        Livewire::actingAs($admin)
            ->test(Applications::class)
            ->call('showApplication', $application->id)
            ->set('offlineRequirementId', (string) $requirement->id)
            ->set('offlineDocument', UploadedFile::fake()->create('kartu-keluarga.pdf', 100, 'application/pdf'))
            ->call('uploadDocument')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ppdb_documents', [
            'ppdb_application_id' => $application->id,
            'ppdb_requirement_id' => $requirement->id,
            'original_name' => 'kartu-keluarga.pdf',
        ]);

        $export = (new Applications)->exportApplications();
        ob_start();
        $export->sendContent();
        $csv = ob_get_clean();
        $this->assertIsString($csv);
        $this->assertStringContainsString('Nomor Pendaftaran', $csv);
        $this->assertStringContainsString($application->application_number, $csv);
    }

    public function test_admin_can_manage_detailed_selection_scores_for_verified_application(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$application] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Siswa Scoring',
                'gender' => 'L',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Seleksi',
            ],
            [
                'relationship' => 'ayah',
                'name' => 'Wali Scoring',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );
        $application->update(['verification_status' => PpdbApplication::VERIFICATION_VERIFIED]);

        $admin = User::create([
            'name' => 'Panitia Scoring',
            'email' => 'panitia-scoring@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Panitia PPDB');

        Livewire::actingAs($admin)
            ->test(Applications::class)
            ->call('showApplication', $application->id)
            ->set('scoreCriterion', 'Nilai rapor')
            ->set('scoreValue', '88.50')
            ->set('scoreNotes', 'Nilai rata-rata semester terakhir')
            ->call('saveSelectionScore')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ppdb_selection_scores', [
            'ppdb_application_id' => $application->id,
            'criterion' => 'Nilai rapor',
            'score' => 88.50,
        ]);

        Livewire::actingAs($admin)
            ->test(Applications::class)
            ->call('showApplication', $application->id)
            ->set('scoreCriterion', 'Nilai rapor')
            ->set('scoreValue', '90')
            ->call('saveSelectionScore')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('ppdb_selection_scores', 1);
        $this->assertDatabaseHas('ppdb_selection_scores', [
            'ppdb_application_id' => $application->id,
            'criterion' => 'Nilai rapor',
            'score' => 90,
        ]);
    }

    public function test_public_mass_announcement_is_available_only_after_announcement_time(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$accepted] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Diterima Massal',
                'gender' => 'P',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-02-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Pengumuman',
            ],
            [
                'relationship' => 'ibu',
                'name' => 'Wali Massal',
                'phone' => '08222222222',
            ],
            '',
            '08222222222',
        );
        $accepted->update([
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
        ]);
        $period->update([
            'status' => PpdbPeriod::STATUS_SELECTION,
            'announcement_at' => now()->addHour(),
        ]);

        app(PpdbSelectionFinalizationService::class)->finalize($period);

        $this->get('/ppdb/pengumuman/'.$period->id)->assertNotFound();

        $period->update([
            'status' => PpdbPeriod::STATUS_ANNOUNCED,
            'announcement_at' => now()->subMinute(),
        ]);

        $this->get('/ppdb/pengumuman/'.$period->id)
            ->assertOk()
            ->assertDontSee('Calon Diterima Massal')
            ->assertDontSee($accepted->application_number)
            ->assertSee('C*** D*** M***')
            ->assertSee('Diterima');
    }

    public function test_public_routes_do_not_expose_private_application_data_without_access_code(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);

        $this->get('/ppdb')->assertOk()->assertSee('Penerimaan Peserta Didik Baru');
        $this->get('/ppdb/panduan')->assertOk()->assertSee('Panduan peserta')->assertSee('Pendaftaran online')->assertSee('Verifikasi');
        $this->get('/ppdb/daftar/'.$period->id)->assertOk()->assertSee('Formulir PPDB');
        $this->get('/ppdb/status')->assertOk()->assertSee('Cek status pendaftaran');
    }

    public function test_panitia_can_reject_payment_with_a_required_reason(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $period->update(['payment_required' => true]);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $pathway->update(['registration_fee' => 150000]);
        [$application] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Pembayaran',
                'gender' => 'L',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Pembayaran',
            ],
            [
                'relationship' => 'ayah',
                'name' => 'Wali Pembayaran',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );
        $payment = $application->payments()->firstOrFail();
        $panitia = User::create([
            'name' => 'Panitia Verifikasi Pembayaran',
            'email' => 'panitia-pembayaran@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $panitia->assignRole('Panitia PPDB');

        Livewire::actingAs($panitia)
            ->test(Applications::class)
            ->call('showApplication', $application->id)
            ->set('decisionNote', 'Bukti pembayaran tidak terbaca.')
            ->call('rejectPayment', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ppdb_payments', [
            'id' => $payment->id,
            'status' => 'rejected',
            'notes' => 'Bukti pembayaran tidak terbaca.',
        ]);
        $this->assertDatabaseHas('ppdb_applications', [
            'id' => $application->id,
            'payment_status' => 'rejected',
        ]);
        $this->assertDatabaseHas('ppdb_payment_histories', [
            'ppdb_payment_id' => $payment->id,
            'to_status' => 'rejected',
            'notes' => 'Bukti pembayaran tidak terbaca.',
        ]);
    }

    public function test_panitia_can_verify_application_while_registration_is_still_open(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $pathway->requirements()->update(['is_required' => false]);
        [$application] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Verifikasi Saat Open',
                'gender' => 'L',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Verifikasi',
            ],
            [
                'relationship' => 'ayah',
                'name' => 'Wali Verifikasi',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );
        $panitia = User::create([
            'name' => 'Panitia Verifikasi Saat Open',
            'email' => 'panitia-verifikasi-open@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $panitia->assignRole('Panitia PPDB');

        Livewire::actingAs($panitia)
            ->test(Applications::class)
            ->call('setVerificationStatus', $application->id, PpdbApplication::VERIFICATION_VERIFIED)
            ->assertHasNoErrors();

        $this->assertSame(PpdbPeriod::STATUS_OPEN, $period->refresh()->status);
        $this->assertDatabaseHas('ppdb_applications', [
            'id' => $application->id,
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
        ]);
    }

    public function test_new_application_is_rejected_after_period_enters_verification_stage(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $period->update(['status' => PpdbPeriod::STATUS_VERIFICATION]);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();

        $this->expectExceptionMessage('Pendaftaran baru hanya dapat dilakukan saat periode berstatus open');
        app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Ditutup',
                'gender' => 'L',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Ditutup',
            ],
            [
                'relationship' => 'ayah',
                'name' => 'Wali Ditutup',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );
    }

    public function test_accepted_candidate_can_confirm_reregistration_and_be_converted_to_student(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$application, $accessCode] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Diterima',
                'gender' => 'P',
                'birth_place' => 'Konawe',
                'birth_date' => '2012-02-10',
                'previous_school' => 'SD Asal',
                'address' => 'Jalan Diterima',
            ],
            [
                'relationship' => 'ibu',
                'name' => 'Ibu Diterima',
                'email' => 'ibu-diterima@siakad.test',
                'phone' => '08222222222',
            ],
            'ibu-diterima@siakad.test',
            '08222222222',
        );
        $application->update([
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
            'reregistration_status' => 'pending',
        ]);
        $period->update([
            'status' => PpdbPeriod::STATUS_REREGISTRATION,
            'announcement_at' => now()->subDay(),
            're_registration_ends_at' => now()->addDays(7),
            'selection_finalized_at' => now()->subDays(2),
        ]);

        Livewire::test(ReRegistration::class)
            ->set('applicationNumber', $application->application_number)
            ->set('accessCode', $accessCode)
            ->call('check')
            ->call('confirm')
            ->assertSet('confirmed', true);

        $application->refresh();
        $this->assertSame('confirmed', $application->reregistration_status);

        $application->update(['reregistration_status' => 'verified']);
        $admin = User::create([
            'name' => 'Admin Konversi',
            'email' => 'admin-konversi@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');

        Livewire::actingAs($admin)
            ->test(Applications::class)
            ->call('convertToStudent', $application->id)
            ->assertHasNoErrors();

        $student = Student::query()->where('school_id', $school->id)->where('nis', $application->application_number)->firstOrFail();
        $this->assertDatabaseHas('parent_student_relations', [
            'student_id' => $student->id,
        ]);
        $this->assertNotNull($student->user_id);
        $this->assertDatabaseHas('users', [
            'id' => $student->user_id,
            'school_id' => $school->id,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $student->user_id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'ibu-diterima@siakad.test',
            'school_id' => $school->id,
        ]);
        $this->assertDatabaseHas('ppdb_applications', [
            'id' => $application->id,
            'conversion_status' => PpdbApplication::CONVERSION_CONVERTED,
            'converted_student_id' => $student->id,
        ]);
    }

    public function test_converted_student_can_activate_account_from_public_status_link(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$application, $accessCode] = app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            [
                'name' => 'Calon Aktivasi Portal',
                'gender' => 'L',
                'birth_place' => 'Kendari',
                'birth_date' => '2012-01-10',
                'previous_school' => 'SD Aktivasi',
                'address' => 'Jalan Aktivasi',
            ],
            [
                'relationship' => 'ayah',
                'name' => 'Wali Aktivasi',
                'phone' => '08123456789',
            ],
            '',
            '08123456789',
        );
        $application->update([
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
            'reregistration_status' => 'verified',
        ]);
        $period->update([
            'status' => PpdbPeriod::STATUS_SELECTION,
            'selection_finalized_at' => now(),
        ]);

        $student = app(PpdbConversionService::class)->convert($application);

        $status = Livewire::test(Status::class)
            ->set('applicationNumber', $application->application_number)
            ->set('accessCode', $accessCode)
            ->call('check')
            ->assertHasNoErrors()
            ->assertSet('studentAccountActivated', false);
        $activationUrl = $status->get('studentActivationUrl');
        $this->assertIsString($activationUrl);
        $token = basename((string) parse_url($activationUrl, PHP_URL_PATH));

        $this->get('/ppdb/aktivasi-akun/'.$token)
            ->assertOk()
            ->assertSee('Buat password baru');

        Livewire::test(ActivateStudentAccount::class, ['token' => $token])
            ->set('password', 'Siswa8!a')
            ->set('password_confirmation', 'Siswa8!a')
            ->call('activate')
            ->assertRedirect(route('siswa.dashboard'));

        $student->refresh();
        $user = $student->user()->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Siswa8!a', $user->password));
        $this->assertDatabaseHas('ppdb_student_activations', [
            'ppdb_application_id' => $application->id,
            'user_id' => $user->id,
        ]);
        $this->assertNotNull(PpdbStudentActivation::query()->where('user_id', $user->id)->firstOrFail()->activated_at);

        Livewire::test(Status::class)
            ->set('applicationNumber', $application->application_number)
            ->set('accessCode', $accessCode)
            ->call('check')
            ->assertHasNoErrors()
            ->assertSet('studentAccountActivated', true)
            ->assertSet('studentActivationUrl', null);

        $this->expectExceptionMessage('tidak valid atau sudah kedaluwarsa');
        app(PpdbStudentActivationService::class)->activate($token, 'PasswordLain2026!');
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Sekolah PPDB Test',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
    }

    private function createAcademicYear(School $school): AcademicYear
    {
        return AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
    }

    private function createOpenPeriod(School $school): PpdbPeriod
    {
        $academicYear = $this->createAcademicYear($school);

        return app(PpdbPeriodService::class)->create($school->id, [
            'academic_year_id' => $academicYear->id,
            'name' => 'PPDB 2026',
            'code' => 'PPDB-2026',
            'level' => $school->level,
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDays(30),
            'status' => PpdbPeriod::STATUS_OPEN,
            'payment_required' => false,
            'default_registration_fee' => 0,
        ]);
    }
}
