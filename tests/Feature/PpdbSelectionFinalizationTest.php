<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\PpdbApplication;
use App\Models\PpdbPathway;
use App\Models\PpdbPeriod;
use App\Models\School;
use App\Services\PpdbApplicationService;
use App\Services\PpdbPeriodService;
use App\Services\PpdbSelectionFinalizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbSelectionFinalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_selection_is_snapshotted_and_locked(): void
    {
        [$period, $application] = $this->createSelection();
        $application->update([
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
        ]);
        $application->selectionScores()->create(['criterion' => 'Rapor', 'score' => 90]);

        $finalized = app(PpdbSelectionFinalizationService::class)->finalize($period);

        $this->assertNotNull($finalized->selection_finalized_at);
        $this->assertDatabaseHas('ppdb_selection_results', [
            'ppdb_application_id' => $application->id,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
            'rank' => 1,
            'total_score' => 90,
        ]);
        $this->expectExceptionMessage('sudah difinalisasi');
        app(PpdbSelectionFinalizationService::class)->finalize($period->refresh());
    }

    public function test_finalization_can_be_cancelled_before_announcement_and_snapshot_is_preserved(): void
    {
        [$period, $application] = $this->createSelection();
        $application->update([
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
        ]);
        $application->selectionScores()->create(['criterion' => 'Rapor', 'score' => 90]);

        $finalization = app(PpdbSelectionFinalizationService::class);
        $this->assertSame(PpdbPeriod::STATUS_SELECTION, $period->status);
        $finalization->finalize($period);
        $oldSnapshot = $period->selectionResults()->firstOrFail();

        $cancelled = $finalization->cancelFinalization($period->refresh(), 'Koreksi teknis pada proses pemeringkatan.');

        $this->assertSame(PpdbPeriod::STATUS_VERIFICATION, $cancelled->status);
        $this->assertNull($cancelled->selection_finalized_at);
        $this->assertNotNull($oldSnapshot->refresh()->invalidated_at);
        $this->assertSame('Koreksi teknis pada proses pemeringkatan.', $oldSnapshot->invalidation_reason);

        $period->refresh()->update(['status' => PpdbPeriod::STATUS_SELECTION]);
        $finalization->finalize($period->refresh());

        $this->assertDatabaseCount('ppdb_selection_results', 2);
        $this->assertSame(1, $period->selectionResults()->whereNull('invalidated_at')->count());
    }

    public function test_finalization_cannot_be_cancelled_after_announcement(): void
    {
        [$period, $application] = $this->createSelection();
        $application->update([
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
        ]);
        app(PpdbSelectionFinalizationService::class)->finalize($period);
        $period->update([
            'status' => PpdbPeriod::STATUS_CLOSED,
            'announcement_at' => now()->subMinute(),
        ]);

        $this->expectExceptionMessage('setelah hasil seleksi diumumkan');
        app(PpdbSelectionFinalizationService::class)->cancelFinalization($period->refresh(), 'Koreksi terlambat.');
    }

    public function test_finalization_cannot_be_cancelled_after_reregistration_started(): void
    {
        [$period, $application] = $this->createSelection();
        $application->update([
            'verification_status' => PpdbApplication::VERIFICATION_VERIFIED,
            'selection_status' => PpdbApplication::SELECTION_ACCEPTED,
            'reregistration_status' => 'pending',
        ]);
        app(PpdbSelectionFinalizationService::class)->finalize($period);

        $this->expectExceptionMessage('proses daftar ulang dimulai');
        app(PpdbSelectionFinalizationService::class)->cancelFinalization($period->refresh(), 'Koreksi sebelum daftar ulang.');
    }

    private function createSelection(): array
    {
        $school = School::create(['name' => 'Sekolah Finalisasi Test', 'level' => 'SMP', 'status' => 'NEGERI', 'is_active' => true, 'is_setup_completed' => true]);
        $year = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $period = app(PpdbPeriodService::class)->create($school->id, [
            'academic_year_id' => $year->id,
            'name' => 'PPDB Finalisasi',
            'code' => 'PPDB-FINAL',
            'level' => 'SMP',
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDays(3),
            'status' => PpdbPeriod::STATUS_OPEN,
            'payment_required' => false,
            'default_registration_fee' => 0,
        ]);
        $pathway = $period->pathways()->where('code', PpdbPathway::UMUM)->firstOrFail();
        [$application] = app(PpdbApplicationService::class)->submitOnline($period, $pathway, ['name' => 'Calon Final', 'gender' => 'L', 'birth_place' => 'Kendari', 'birth_date' => '2012-01-10', 'previous_school' => 'SD Asal', 'address' => 'Jalan Final'], ['relationship' => 'ayah', 'name' => 'Wali Final', 'phone' => '08123456789'], '', '08123456789');
        $period->update(['status' => PpdbPeriod::STATUS_SELECTION]);

        return [$period, $application];
    }
}
