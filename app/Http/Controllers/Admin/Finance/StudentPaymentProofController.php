<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\StudentPayment;
use App\Support\CurrentSchool;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentPaymentProofController extends Controller
{
    public function __invoke(int|string $payment): StreamedResponse
    {
        $record = StudentPayment::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($payment)
            ->firstOrFail();

        $path = $record->proof_file;
        abort_unless(is_string($path) && str_starts_with($path, 'payment_proofs/'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path));
    }
}
