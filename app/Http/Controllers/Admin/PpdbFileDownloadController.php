<?php

namespace App\Http\Controllers\Admin;

use App\Models\PpdbDocument;
use App\Models\PpdbPayment;
use App\Support\CurrentSchool;
use App\Support\PpdbPermissions;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PpdbFileDownloadController
{
    public function document(int|string $id): StreamedResponse
    {
        PpdbPermissions::authorize(PpdbPermissions::VIEW_APPLICATIONS);
        $document = PpdbDocument::query()
            ->whereHas('application', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()))
            ->whereKey($id)
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function paymentProof(int|string $id): StreamedResponse
    {
        PpdbPermissions::authorize(PpdbPermissions::VIEW_APPLICATIONS);
        $payment = PpdbPayment::query()
            ->whereHas('application', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()))
            ->whereKey($id)
            ->firstOrFail();

        abort_unless($payment->proof_file && Storage::disk('local')->exists($payment->proof_file), 404);

        return Storage::disk('local')->download($payment->proof_file);
    }
}
