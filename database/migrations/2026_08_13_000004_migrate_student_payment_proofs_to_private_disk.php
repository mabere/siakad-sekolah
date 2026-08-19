<?php

use App\Models\StudentPayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');

        StudentPayment::query()
            ->whereNotNull('proof_file')
            ->orderBy('id')
            ->chunkById(100, function ($payments) use ($publicDisk, $privateDisk): void {
                foreach ($payments as $payment) {
                    $oldPath = $payment->proof_file;
                    if (! is_string($oldPath) || ! str_starts_with($oldPath, 'payment_proofs/') || ! $publicDisk->exists($oldPath)) {
                        continue;
                    }

                    $newPath = 'payment_proofs/'.$payment->school_id.'/'.$payment->id.'/'.basename($oldPath);
                    if ($privateDisk->exists($newPath)) {
                        $payment->update(['proof_file' => $newPath]);
                        $publicDisk->delete($oldPath);

                        continue;
                    }

                    $stream = $publicDisk->readStream($oldPath);
                    if (! is_resource($stream)) {
                        continue;
                    }

                    $stored = $privateDisk->put($newPath, $stream);
                    fclose($stream);

                    if ($stored) {
                        $payment->update(['proof_file' => $newPath]);
                        $publicDisk->delete($oldPath);
                    }
                }
            });
    }

    public function down(): void
    {
        // Private payment proofs are intentionally not copied back to a public disk.
    }
};
