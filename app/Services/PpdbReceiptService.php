<?php

namespace App\Services;

use App\Models\PpdbApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PpdbReceiptService
{
    public function download(PpdbApplication $application, string $accessCode): Response
    {
        $application->loadMissing([
            'school',
            'period',
            'pathway',
            'candidate',
            'guardians' => fn ($query) => $query->where('is_primary', true),
        ]);

        return Pdf::loadView('public.ppdb.receipt', [
            'application' => $application,
            'accessCode' => $accessCode,
            'statusUrl' => route('public.ppdb.status'),
        ])
            ->setPaper('a4')
            ->download('bukti-pendaftaran-'.$application->application_number.'.pdf');
    }
}
