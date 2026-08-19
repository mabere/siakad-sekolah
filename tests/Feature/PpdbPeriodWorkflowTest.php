<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\PpdbAuditLog;
use App\Models\PpdbPeriod;
use App\Models\School;
use App\Services\PpdbPeriodService;
use App\Services\PpdbPeriodWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbPeriodWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_rejects_skipping_verification_and_requires_deadlines(): void
    {
        $period = $this->createPeriod(PpdbPeriod::STATUS_DRAFT);
        $workflow = app(PpdbPeriodWorkflowService::class);

        $this->expectExceptionMessage('Status PPDB tidak dapat diubah');
        $workflow->transition($period, PpdbPeriod::STATUS_SELECTION);
    }

    public function test_workflow_requires_verification_deadline_before_opening_verification(): void
    {
        $period = $this->createPeriod(PpdbPeriod::STATUS_OPEN);
        $workflow = app(PpdbPeriodWorkflowService::class);

        $this->expectExceptionMessage('Batas verifikasi wajib diisi');
        $workflow->transition($period, PpdbPeriod::STATUS_VERIFICATION);
    }

    public function test_verification_is_available_during_open_and_verification_only(): void
    {
        $period = $this->createPeriod(PpdbPeriod::STATUS_OPEN);

        $this->assertTrue($period->allowsVerification());
        $this->assertSame('Pendaftaran aktif - verifikasi berjalan', $period->verificationStageLabel());

        $period->update(['status' => PpdbPeriod::STATUS_VERIFICATION]);
        $this->assertTrue($period->refresh()->allowsVerification());
        $this->assertSame('Pendaftaran ditutup - penyelesaian verifikasi', $period->verificationStageLabel());

        $period->update(['status' => PpdbPeriod::STATUS_SELECTION]);
        $this->assertFalse($period->refresh()->allowsVerification());
        $this->assertSame('Verifikasi ditutup pada tahap ini', $period->verificationStageLabel());
    }

    public function test_workflow_allows_valid_progression_to_reregistration(): void
    {
        $period = $this->createPeriod(PpdbPeriod::STATUS_DRAFT);
        $period->update([
            'verification_ends_at' => now()->addDays(10),
            'announcement_at' => now()->addDays(12),
            're_registration_ends_at' => now()->addDays(20),
        ]);
        $workflow = app(PpdbPeriodWorkflowService::class);

        $workflow->transition($period, PpdbPeriod::STATUS_PUBLISHED);
        $workflow->transition($period->refresh(), PpdbPeriod::STATUS_OPEN);
        $workflow->transition($period->refresh(), PpdbPeriod::STATUS_VERIFICATION);
        $workflow->transition($period->refresh(), PpdbPeriod::STATUS_SELECTION);
        $period->update(['selection_finalized_at' => now()]);
        $workflow->transition($period->refresh(), PpdbPeriod::STATUS_ANNOUNCED);
        $workflow->transition($period->refresh(), PpdbPeriod::STATUS_REREGISTRATION);

        $this->assertTrue($period->refresh()->isReregistrationOpen());
    }

    public function test_closed_period_can_be_reopened_for_verification_with_reason_and_audit_log(): void
    {
        $period = $this->createPeriod(PpdbPeriod::STATUS_CLOSED);
        $period->update(['verification_ends_at' => now()->addDays(3)]);

        $reopened = app(PpdbPeriodWorkflowService::class)->reopenVerification($period, 'Penyelesaian antrean verifikasi setelah gangguan teknis.');

        $this->assertSame(PpdbPeriod::STATUS_VERIFICATION, $reopened->status);
        $audit = PpdbAuditLog::query()->where('action', 'period_reopened_for_verification')->firstOrFail();
        $this->assertSame($period->id, data_get($audit->metadata, 'period_id'));
        $this->assertSame('Penyelesaian antrean verifikasi setelah gangguan teknis.', data_get($audit->metadata, 'reason'));
    }

    public function test_reopening_verification_is_blocked_after_selection_is_finalized(): void
    {
        $period = $this->createPeriod(PpdbPeriod::STATUS_CLOSED);
        $period->update([
            'verification_ends_at' => now()->addDays(3),
            'selection_finalized_at' => now(),
        ]);

        $this->expectExceptionMessage('setelah hasil seleksi difinalisasi');
        app(PpdbPeriodWorkflowService::class)->reopenVerification($period, 'Koreksi teknis.');
    }

    public function test_finalized_selection_cannot_return_to_verification_through_regular_transition(): void
    {
        $period = $this->createPeriod(PpdbPeriod::STATUS_SELECTION);
        $period->update([
            'verification_ends_at' => now()->addDays(10),
            'selection_finalized_at' => now(),
        ]);

        $this->expectExceptionMessage('Gunakan fitur batalkan finalisasi');
        app(PpdbPeriodWorkflowService::class)->transition($period->refresh(), PpdbPeriod::STATUS_VERIFICATION);
    }

    private function createPeriod(string $status): PpdbPeriod
    {
        $school = School::create([
            'name' => 'Sekolah Workflow Test',
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

        return app(PpdbPeriodService::class)->create($school->id, [
            'academic_year_id' => $academicYear->id,
            'name' => 'PPDB Workflow',
            'code' => 'PPDB-WORKFLOW',
            'level' => 'SMP',
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDays(5),
            'status' => $status,
            'payment_required' => false,
            'default_registration_fee' => 0,
        ]);
    }
}
