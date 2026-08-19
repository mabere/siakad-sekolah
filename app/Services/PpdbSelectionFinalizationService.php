<?php

namespace App\Services;

use App\Models\PpdbApplication;
use App\Models\PpdbAuditLog;
use App\Models\PpdbPeriod;
use App\Models\PpdbSelectionResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PpdbSelectionFinalizationService
{
    public function finalize(PpdbPeriod $period): PpdbPeriod
    {
        if ($period->status !== PpdbPeriod::STATUS_SELECTION) {
            throw new \DomainException('Hasil hanya dapat difinalisasi saat periode berada pada tahap seleksi.');
        }

        if ($period->selection_finalized_at !== null) {
            throw new \DomainException('Hasil seleksi periode ini sudah difinalisasi dan dikunci.');
        }

        return DB::transaction(function () use ($period): PpdbPeriod {
            $lockedPeriod = PpdbPeriod::query()->lockForUpdate()->findOrFail($period->id);
            if ($lockedPeriod->status !== PpdbPeriod::STATUS_SELECTION) {
                throw new \DomainException('Hasil hanya dapat difinalisasi saat periode berada pada tahap seleksi.');
            }

            if ($lockedPeriod->selection_finalized_at !== null) {
                throw new \DomainException('Hasil seleksi periode ini sudah difinalisasi dan dikunci.');
            }

            $applications = $lockedPeriod->applications()
                ->where('school_id', $lockedPeriod->school_id)
                ->where('verification_status', PpdbApplication::VERIFICATION_VERIFIED)
                ->with(['selectionScores', 'pathway'])
                ->get();

            if ($applications->isEmpty()) {
                throw new \DomainException('Belum ada pendaftar terverifikasi untuk difinalisasi.');
            }

            if ($applications->contains(fn (PpdbApplication $application): bool => in_array($application->selection_status, [PpdbApplication::SELECTION_PENDING, PpdbApplication::SELECTION_ELIGIBLE], true))) {
                throw new \DomainException('Semua pendaftar terverifikasi harus memiliki hasil diterima, cadangan, atau tidak diterima.');
            }

            foreach ($applications->groupBy('ppdb_pathway_id') as $pathwayApplications) {
                $quota = (int) $pathwayApplications->first()->pathway->quota;
                if ($quota > 0 && $pathwayApplications->where('selection_status', PpdbApplication::SELECTION_ACCEPTED)->count() > $quota) {
                    throw new \DomainException('Jumlah pendaftar diterima melebihi kuota jalur '.$pathwayApplications->first()->pathway->name.'.');
                }
            }

            $snapshotAt = now();
            foreach ($applications->groupBy('ppdb_pathway_id') as $pathwayApplications) {
                $ranked = $pathwayApplications->sort(function (PpdbApplication $left, PpdbApplication $right): int {
                    $leftTotal = (float) $left->selectionScores->sum(fn ($score): float => (float) $score->score);
                    $rightTotal = (float) $right->selectionScores->sum(fn ($score): float => (float) $score->score);
                    $leftAverage = $left->selectionScores->isNotEmpty() ? $leftTotal / $left->selectionScores->count() : 0;
                    $rightAverage = $right->selectionScores->isNotEmpty() ? $rightTotal / $right->selectionScores->count() : 0;

                    if ($leftTotal !== $rightTotal) {
                        return $leftTotal < $rightTotal ? 1 : -1;
                    }
                    if ($leftAverage !== $rightAverage) {
                        return $leftAverage < $rightAverage ? 1 : -1;
                    }

                    return [self::submittedTimestamp($left), $left->id]
                        <=> [self::submittedTimestamp($right), $right->id];
                })->values();

                foreach ($ranked as $index => $application) {
                    $totalScore = (float) $application->selectionScores->sum(fn ($score): float => (float) $score->score);
                    $averageScore = $application->selectionScores->isNotEmpty() ? $totalScore / $application->selectionScores->count() : 0;
                    PpdbSelectionResult::create([
                        'school_id' => $lockedPeriod->school_id,
                        'ppdb_period_id' => $lockedPeriod->id,
                        'ppdb_application_id' => $application->id,
                        'ppdb_pathway_id' => $application->ppdb_pathway_id,
                        'rank' => $index + 1,
                        'selection_status' => $application->selection_status,
                        'total_score' => $totalScore,
                        'average_score' => $averageScore,
                        'snapshot_at' => $snapshotAt,
                        'finalized_by' => auth()->id(),
                    ]);
                }
            }

            $lockedPeriod->update([
                'selection_finalized_at' => $snapshotAt,
                'selection_finalized_by' => auth()->id(),
            ]);

            return $lockedPeriod->refresh();
        });
    }

    public function cancelFinalization(PpdbPeriod $period, string $reason): PpdbPeriod
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('Alasan pembatalan finalisasi wajib diisi.');
        }

        return DB::transaction(function () use ($period, $reason): PpdbPeriod {
            $lockedPeriod = PpdbPeriod::query()->lockForUpdate()->findOrFail($period->id);
            if (! in_array($lockedPeriod->status, [PpdbPeriod::STATUS_SELECTION, PpdbPeriod::STATUS_CLOSED], true)) {
                throw new \DomainException('Finalisasi hanya dapat dibatalkan saat periode berada pada tahap seleksi atau closed.');
            }

            if ($lockedPeriod->selection_finalized_at === null) {
                throw new \DomainException('Hasil seleksi periode ini belum difinalisasi.');
            }

            if ($lockedPeriod->isAnnouncementPublished()) {
                throw new \DomainException('Finalisasi tidak dapat dibatalkan setelah hasil seleksi diumumkan.');
            }

            if ($lockedPeriod->applications()
                ->whereNotNull('reregistration_status')
                ->where('reregistration_status', '<>', 'not_open')
                ->exists()) {
                throw new \DomainException('Finalisasi tidak dapat dibatalkan setelah proses daftar ulang dimulai.');
            }

            if ($lockedPeriod->applications()->whereNotNull('converted_student_id')->exists()) {
                throw new \DomainException('Finalisasi tidak dapat dibatalkan setelah ada calon yang dikonversi menjadi siswa.');
            }

            $invalidatedAt = now();
            $invalidatedCount = PpdbSelectionResult::query()
                ->where('school_id', $lockedPeriod->school_id)
                ->where('ppdb_period_id', $lockedPeriod->id)
                ->whereNull('invalidated_at')
                ->update([
                    'invalidated_at' => $invalidatedAt,
                    'invalidated_by' => auth()->id(),
                    'invalidation_reason' => $reason,
                ]);

            $previousFinalizedAt = $lockedPeriod->getRawOriginal('selection_finalized_at');
            $lockedPeriod->update([
                'status' => PpdbPeriod::STATUS_VERIFICATION,
                'selection_finalized_at' => null,
                'selection_finalized_by' => null,
            ]);

            PpdbAuditLog::create([
                'school_id' => $lockedPeriod->school_id,
                'actor_id' => auth()->id(),
                'action' => 'selection_finalization_cancelled',
                'from_status' => 'finalized',
                'to_status' => PpdbPeriod::STATUS_VERIFICATION,
                'metadata' => [
                    'period_id' => $lockedPeriod->id,
                    'previous_status' => $period->status,
                    'previous_finalized_at' => $previousFinalizedAt !== null
                        ? Carbon::parse($previousFinalizedAt)->toIso8601String()
                        : null,
                    'invalidated_snapshot_rows' => $invalidatedCount,
                    'reason' => $reason,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 1000),
            ]);

            return $lockedPeriod->refresh();
        });
    }

    private static function submittedTimestamp(PpdbApplication $application): int
    {
        $value = $application->getRawOriginal('submitted_at');

        if ($value === null || $value === '') {
            return PHP_INT_MAX;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? PHP_INT_MAX : $timestamp;
    }
}
