<?php

namespace App\Http\Controllers\Public;

use App\Models\PpdbApplication;
use App\Models\School;
use App\Services\PpdbReceiptDownloadService;
use App\Services\PpdbReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PpdbReceiptDownloadController
{
    public function __invoke(Request $request, PpdbReceiptDownloadService $downloads, PpdbReceiptService $receipts): Response
    {
        $applicationId = (string) $request->route('application');
        $token = (string) $request->route('token');
        $payload = $downloads->resolve($token, $applicationId);
        abort_unless($payload !== null, 403);

        $schoolId = School::query()->where('is_active', true)->orderBy('id')->value('id');
        $application = PpdbApplication::query()
            ->where('school_id', $schoolId)
            ->whereKey($applicationId)
            ->firstOrFail();
        abort_unless(Hash::check($payload['access_code'], $application->access_code_hash), 403, 'Kode akses tidak valid.');

        return $receipts->download($application, $payload['access_code']);
    }
}
