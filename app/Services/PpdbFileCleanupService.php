<?php

namespace App\Services;

use App\Models\PpdbDocument;
use App\Models\PpdbPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PpdbFileCleanupService
{
    /** @return array{scanned: int, candidates: int, deleted: int} */
    public function removeOrphans(bool $delete = false): array
    {
        $disk = Storage::disk('local');
        $cutoff = Carbon::now()->subDays(max(1, (int) config('ppdb.orphan_retention_days', 7)));
        $summary = ['scanned' => 0, 'candidates' => 0, 'deleted' => 0];
        $batch = [];

        foreach ($disk->getDriver()->listContents('ppdb', true) as $attributes) {
            if (! $attributes->isFile()) {
                continue;
            }

            $batch[$attributes->path()] = $attributes->lastModified();
            if (count($batch) >= 500) {
                $this->processBatch($batch, $cutoff, $delete, $summary);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->processBatch($batch, $cutoff, $delete, $summary);
        }

        return $summary;
    }

    /**
     * @param  array<string, int|null>  $batch
     * @param  array{scanned: int, candidates: int, deleted: int}  $summary
     */
    private function processBatch(array $batch, Carbon $cutoff, bool $delete, array &$summary): void
    {
        $paths = array_keys($batch);
        $referenced = PpdbDocument::query()
            ->whereIn('file_path', $paths)
            ->pluck('file_path')
            ->merge(PpdbPayment::query()->whereIn('proof_file', $paths)->pluck('proof_file'))
            ->filter()
            ->flip();

        foreach ($batch as $path => $lastModified) {
            $summary['scanned']++;
            if ($referenced->has($path) || ($lastModified !== null && Carbon::createFromTimestamp($lastModified)->greaterThanOrEqualTo($cutoff))) {
                continue;
            }

            $summary['candidates']++;
            if ($delete && Storage::disk('local')->delete($path)) {
                $summary['deleted']++;
            }
        }
    }
}
