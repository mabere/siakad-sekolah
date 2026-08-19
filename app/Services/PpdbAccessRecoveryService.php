<?php

namespace App\Services;

use App\Mail\PpdbAccessRecoveryOtp;
use App\Models\PpdbAccessRecovery;
use App\Models\PpdbApplication;
use App\Models\School;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class PpdbAccessRecoveryService
{
    private const OTP_EXPIRY_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function requestOtp(string $applicationNumber, string $contactEmail): void
    {
        $normalizedNumber = strtoupper(trim($applicationNumber));
        $normalizedEmail = strtolower(trim($contactEmail));
        $rateKey = 'ppdb-pin-recovery|'.request()->ip().'|'.$normalizedNumber;

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            return;
        }
        RateLimiter::hit($rateKey, 900);

        $school = School::query()->where('is_active', true)->orderBy('id')->first();
        if ($school === null || ! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $application = PpdbApplication::query()
            ->where('school_id', $school->id)
            ->where('application_number', $normalizedNumber)
            ->with('guardians')
            ->first();

        if (! $application || ! $this->emailBelongsToApplication($application, $normalizedEmail)) {
            return;
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(self::OTP_EXPIRY_MINUTES);

        DB::transaction(function () use ($application, $normalizedEmail, $code, $expiresAt): void {
            PpdbAccessRecovery::query()
                ->where('ppdb_application_id', $application->id)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            PpdbAccessRecovery::create([
                'school_id' => $application->school_id,
                'ppdb_application_id' => $application->id,
                'channel' => 'email',
                'destination_hash' => Hash::make($normalizedEmail),
                'code_hash' => Hash::make($code),
                'expires_at' => $expiresAt,
                'requested_ip' => request()->ip(),
            ]);
        });

        try {
            Mail::to($normalizedEmail)->send(new PpdbAccessRecoveryOtp($application, $code, self::OTP_EXPIRY_MINUTES));
        } catch (\Throwable $exception) {
            Log::error('PPDB access recovery email could not be sent.', [
                'application_id' => $application->id,
                'exception' => $exception::class,
            ]);
        }
    }

    public function verifyAndReset(string $applicationNumber, string $contactEmail, string $otp): ?string
    {
        $normalizedNumber = strtoupper(trim($applicationNumber));
        $normalizedEmail = strtolower(trim($contactEmail));
        $school = School::query()->where('is_active', true)->orderBy('id')->first();
        if ($school === null) {
            return null;
        }

        $recovery = PpdbAccessRecovery::query()
            ->where('school_id', $school->id)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->whereHas('application', fn ($query) => $query->where('application_number', $normalizedNumber))
            ->latest('id')
            ->first();

        if (! $recovery || $recovery->attempts >= self::MAX_ATTEMPTS || ! Hash::check($normalizedEmail, $recovery->destination_hash)) {
            return null;
        }

        $recovery->increment('attempts');
        if (! Hash::check(trim($otp), $recovery->code_hash)) {
            return null;
        }

        return DB::transaction(function () use ($recovery): string {
            $lockedRecovery = PpdbAccessRecovery::query()->lockForUpdate()->findOrFail($recovery->id);
            $expiresAt = Carbon::parse((string) $lockedRecovery->getRawOriginal('expires_at'));
            if ($lockedRecovery->consumed_at !== null || $expiresAt->isPast() || $lockedRecovery->attempts > self::MAX_ATTEMPTS) {
                return '';
            }

            $application = PpdbApplication::query()->lockForUpdate()->findOrFail($lockedRecovery->ppdb_application_id);
            $newAccessCode = (string) random_int(100000, 999999);
            $application->update(['access_code_hash' => Hash::make($newAccessCode)]);
            $lockedRecovery->update(['consumed_at' => now()]);
            app(PpdbApplicationService::class)->audit($application, 'access_code_recovered', null, null, ['channel' => 'email']);

            return $newAccessCode;
        }) ?: null;
    }

    private function emailBelongsToApplication(PpdbApplication $application, string $email): bool
    {
        if (strtolower(trim((string) $application->contact_email)) === $email) {
            return true;
        }

        return $application->guardians->contains(fn ($guardian): bool => strtolower(trim((string) $guardian->email)) === $email);
    }
}
