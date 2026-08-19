<?php

namespace Tests\Feature;

use App\Mail\PpdbAccessRecoveryOtp;
use App\Models\AcademicYear;
use App\Models\PpdbApplication;
use App\Models\PpdbPathway;
use App\Models\PpdbPeriod;
use App\Models\School;
use App\Services\PpdbAccessRecoveryService;
use App\Services\PpdbApplicationService;
use App\Services\PpdbPeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PpdbAccessRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_email_can_recover_pin_once(): void
    {
        Mail::fake();
        [$application] = $this->createPpdbApplication();
        $service = app(PpdbAccessRecoveryService::class);

        $service->requestOtp($application->application_number, 'wali-otp@siakad.test');
        $otp = null;
        Mail::assertSent(PpdbAccessRecoveryOtp::class, function (PpdbAccessRecoveryOtp $mail) use (&$otp): bool {
            $otp = $mail->code;

            return $mail->hasTo('wali-otp@siakad.test');
        });

        $newPin = $service->verifyAndReset($application->application_number, 'wali-otp@siakad.test', (string) $otp);
        $this->assertNotNull($newPin);
        $this->assertTrue(Hash::check($newPin, $application->refresh()->access_code_hash));
        $this->assertNull($service->verifyAndReset($application->application_number, 'wali-otp@siakad.test', (string) $otp));
    }

    public function test_unknown_email_does_not_send_otp(): void
    {
        Mail::fake();
        [$application] = $this->createPpdbApplication();

        app(PpdbAccessRecoveryService::class)->requestOtp($application->application_number, 'unknown@siakad.test');

        Mail::assertNothingSent();
        $this->assertDatabaseCount('ppdb_access_recoveries', 0);
    }

    /** @return array{PpdbApplication, string} */
    private function createPpdbApplication(): array
    {
        $school = School::create([
            'name' => 'Sekolah OTP Test',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $period = app(PpdbPeriodService::class)->create($school->id, [
            'academic_year_id' => $academicYear->id,
            'name' => 'PPDB OTP',
            'code' => 'PPDB-OTP',
            'level' => 'SMP',
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDays(30),
            'status' => PpdbPeriod::STATUS_OPEN,
            'payment_required' => false,
            'default_registration_fee' => 0,
        ]);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();

        return app(PpdbApplicationService::class)->submitOnline(
            $period,
            $pathway,
            ['name' => 'Calon OTP', 'gender' => 'L', 'birth_place' => 'Kendari', 'birth_date' => '2012-01-10', 'previous_school' => 'SD Asal', 'address' => 'Jalan OTP'],
            ['relationship' => 'ayah', 'name' => 'Wali OTP', 'email' => 'wali-otp@siakad.test', 'phone' => '08123456789'],
            'wali-otp@siakad.test',
            '08123456789',
        );
    }
}
