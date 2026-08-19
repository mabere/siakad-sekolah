<?php

namespace App\Services;

use App\Models\PpdbApplication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PpdbReceiptDownloadService
{
    private const TTL_MINUTES = 5;

    public function createUrl(PpdbApplication $application, string $accessCode): string
    {
        $token = Str::random(64);
        $expiresAt = now()->addMinutes(self::TTL_MINUTES);

        Cache::put($this->cacheKey($token), [
            'application_id' => $application->id,
            'access_code' => Crypt::encryptString($accessCode),
        ], $expiresAt);

        return URL::temporarySignedRoute('public.ppdb.receipt.download', $expiresAt, [
            'application' => $application->id,
            'token' => $token,
        ]);
    }

    /** @return array{application_id: int, access_code: string}|null */
    public function resolve(string $token, int|string $applicationId): ?array
    {
        $payload = Cache::get($this->cacheKey($token));
        if (! is_array($payload) || (int) ($payload['application_id'] ?? 0) !== (int) $applicationId) {
            return null;
        }

        try {
            $accessCode = Crypt::decryptString((string) ($payload['access_code'] ?? ''));
        } catch (\Throwable) {
            return null;
        }

        return [
            'application_id' => (int) $payload['application_id'],
            'access_code' => $accessCode,
        ];
    }

    private function cacheKey(string $token): string
    {
        return 'ppdb-receipt-download:'.$token;
    }
}
