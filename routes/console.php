<?php

use App\Services\PpdbFileCleanupService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ppdb:cleanup-files {--force : Hapus berkas PPDB yatim yang melewati masa aman}', function (PpdbFileCleanupService $cleanup): void {
    $summary = $cleanup->removeOrphans((bool) $this->option('force'));
    $this->info(sprintf(
        'Scanned: %d, candidates: %d, deleted: %d.',
        $summary['scanned'],
        $summary['candidates'],
        $summary['deleted'],
    ));
})->purpose('Bersihkan berkas PPDB yatim secara aman');

Schedule::command('ppdb:cleanup-files --force')->dailyAt('02:30')->withoutOverlapping();
