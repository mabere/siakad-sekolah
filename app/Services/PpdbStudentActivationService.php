<?php

namespace App\Services;

use App\Models\PpdbApplication;
use App\Models\PpdbStudentActivation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class PpdbStudentActivationService
{
    private const EXPIRY_MINUTES = 30;

    /**
     * @return array{activation_url: ?string, activated: bool, username: ?string, expires_at: ?string}
     */
    public function statusFor(PpdbApplication $application): array
    {
        $application->loadMissing(['convertedStudent.user']);
        $student = $application->convertedStudent;
        $user = $student?->user;

        if ($application->conversion_status !== PpdbApplication::CONVERSION_CONVERTED || $student === null || $user === null || ! $user->is_active) {
            return [
                'activation_url' => null,
                'activated' => false,
                'username' => null,
                'expires_at' => null,
            ];
        }

        $activated = PpdbStudentActivation::query()
            ->where('ppdb_application_id', $application->id)
            ->where('user_id', $user->id)
            ->whereNotNull('activated_at')
            ->exists();

        if ($activated) {
            return [
                'activation_url' => null,
                'activated' => true,
                'username' => $user->email,
                'expires_at' => null,
            ];
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addMinutes(self::EXPIRY_MINUTES);

        DB::transaction(function () use ($application, $user, $token, $expiresAt): void {
            PpdbStudentActivation::query()
                ->where('ppdb_application_id', $application->id)
                ->whereNull('activated_at')
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            PpdbStudentActivation::create([
                'school_id' => $application->school_id,
                'ppdb_application_id' => $application->id,
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $token),
                'expires_at' => $expiresAt,
                'requested_ip' => request()->ip(),
            ]);
        });

        return [
            'activation_url' => URL::route(
                'public.ppdb.student-activation',
                ['token' => $token],
            ),
            'activated' => false,
            'username' => $user->email,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    public function findValid(string $token): ?PpdbStudentActivation
    {
        return PpdbStudentActivation::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('activated_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->with('user')
            ->first();
    }

    public function activate(string $token, string $password): User
    {
        return DB::transaction(function () use ($token, $password): User {
            $activation = PpdbStudentActivation::query()
                ->lockForUpdate()
                ->where('token_hash', hash('sha256', $token))
                ->whereNull('activated_at')
                ->whereNull('revoked_at')
                ->first();

            $expiresAt = $activation?->getAttribute('expires_at');
            if ($activation === null || ! $expiresAt instanceof \DateTimeInterface || $expiresAt->getTimestamp() <= now()->getTimestamp()) {
                throw new \DomainException('Tautan aktivasi tidak valid atau sudah kedaluwarsa. Silakan buat tautan aktivasi baru dari halaman cek status PPDB.');
            }

            $application = PpdbApplication::query()
                ->where('school_id', $activation->school_id)
                ->whereKey($activation->ppdb_application_id)
                ->where('conversion_status', PpdbApplication::CONVERSION_CONVERTED)
                ->with('convertedStudent.user')
                ->lockForUpdate()
                ->first();
            $user = $application?->convertedStudent?->user;

            if ($application === null || $user === null || ! $user->is_active || (int) $user->id !== (int) $activation->user_id) {
                throw new \DomainException('Akun siswa tidak ditemukan atau tidak lagi tersedia.');
            }

            $user->update(['password' => Hash::make($password)]);
            $activation->update([
                'activated_at' => now(),
                'activated_ip' => request()->ip(),
            ]);
            PpdbStudentActivation::query()
                ->where('user_id', $user->id)
                ->whereNull('activated_at')
                ->whereNull('revoked_at')
                ->where('id', '<>', $activation->id)
                ->update(['revoked_at' => now()]);
            app(PpdbApplicationService::class)->audit($application, 'student_account_activated', null, null, [
                'student_user_id' => $user->id,
            ]);

            return $user;
        });
    }
}
