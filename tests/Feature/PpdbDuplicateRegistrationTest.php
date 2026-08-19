<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\PpdbPathway;
use App\Models\PpdbPeriod;
use App\Models\School;
use App\Services\PpdbApplicationService;
use App\Services\PpdbPeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbDuplicateRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_nik_cannot_register_twice_in_one_period(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $candidate = [
            'name' => 'Calon Pertama',
            'nik' => '3201-0101-1234-0001',
            'gender' => 'L',
            'birth_place' => 'Kendari',
            'birth_date' => '2012-01-10',
            'previous_school' => 'SD Asal',
            'address' => 'Jalan Pendidikan',
        ];
        $guardian = ['relationship' => 'ayah', 'name' => 'Wali', 'phone' => '08123456789'];

        app(PpdbApplicationService::class)->submitOnline($period, $pathway, $candidate, $guardian, '', '08123456789');

        $this->expectExceptionMessage('NIK tersebut sudah memiliki pendaftaran');
        app(PpdbApplicationService::class)->submitOnline($period, $pathway, [
            ...$candidate,
            'name' => 'Calon Kedua',
            'nik' => '3201010112340001',
        ], $guardian, '', '08123456789');
    }

    public function test_same_identity_can_register_in_a_different_period(): void
    {
        $school = $this->createSchool();
        $period = $this->createOpenPeriod($school);
        $nextPeriod = $this->createOpenPeriod($school);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $nextPathway = $nextPeriod->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        $candidate = [
            'name' => 'Calon Lintas Periode',
            'nisn' => '0012345678',
            'gender' => 'P',
            'birth_place' => 'Kendari',
            'birth_date' => '2012-01-10',
            'previous_school' => 'SD Asal',
            'address' => 'Jalan Pendidikan',
        ];
        $guardian = ['relationship' => 'ibu', 'name' => 'Wali', 'phone' => '08123456789'];

        app(PpdbApplicationService::class)->submitOnline($period, $pathway, $candidate, $guardian, '', '08123456789');
        app(PpdbApplicationService::class)->submitOnline($nextPeriod, $nextPathway, $candidate, $guardian, '', '08123456789');

        $this->assertDatabaseCount('ppdb_applications', 2);
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Sekolah PPDB Duplicate Test',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
    }

    private function createOpenPeriod(School $school): PpdbPeriod
    {
        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027 '.(PpdbPeriod::query()->where('school_id', $school->id)->count() + 1),
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $sequence = PpdbPeriod::query()->where('school_id', $school->id)->count() + 1;

        return app(PpdbPeriodService::class)->create($school->id, [
            'academic_year_id' => $academicYear->id,
            'name' => 'PPDB 2026 '.$sequence,
            'code' => 'PPDB-2026-'.$sequence,
            'level' => $school->level,
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDays(30),
            'status' => PpdbPeriod::STATUS_OPEN,
            'payment_required' => false,
            'default_registration_fee' => 0,
        ]);
    }
}
