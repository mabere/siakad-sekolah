<?php

namespace App\Services;

use App\Models\PpdbAuditLog;
use App\Models\PpdbPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PpdbPeriodWorkflowService
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        PpdbPeriod::STATUS_DRAFT => [PpdbPeriod::STATUS_PUBLISHED, PpdbPeriod::STATUS_CLOSED],
        PpdbPeriod::STATUS_PUBLISHED => [PpdbPeriod::STATUS_DRAFT, PpdbPeriod::STATUS_OPEN, PpdbPeriod::STATUS_CLOSED],
        PpdbPeriod::STATUS_OPEN => [PpdbPeriod::STATUS_PUBLISHED, PpdbPeriod::STATUS_VERIFICATION, PpdbPeriod::STATUS_CLOSED],
        PpdbPeriod::STATUS_VERIFICATION => [PpdbPeriod::STATUS_OPEN, PpdbPeriod::STATUS_SELECTION, PpdbPeriod::STATUS_CLOSED],
        PpdbPeriod::STATUS_SELECTION => [PpdbPeriod::STATUS_VERIFICATION, PpdbPeriod::STATUS_ANNOUNCED, PpdbPeriod::STATUS_CLOSED],
        PpdbPeriod::STATUS_ANNOUNCED => [PpdbPeriod::STATUS_SELECTION, PpdbPeriod::STATUS_REREGISTRATION, PpdbPeriod::STATUS_CLOSED],
        PpdbPeriod::STATUS_REREGISTRATION => [PpdbPeriod::STATUS_ANNOUNCED, PpdbPeriod::STATUS_CLOSED],
        PpdbPeriod::STATUS_CLOSED => [],
    ];

    public function transition(PpdbPeriod $period, string $targetStatus): PpdbPeriod
    {
        if ($period->status === $targetStatus) {
            $this->assertSchedule($period, $targetStatus);

            return $period;
        }

        if (! in_array($targetStatus, self::TRANSITIONS[$period->status] ?? [], true)) {
            throw new \DomainException(sprintf(
                'Status PPDB tidak dapat diubah dari %s menjadi %s.',
                self::label($period->status),
                self::label($targetStatus),
            ));
        }

        $this->assertSchedule($period, $targetStatus);

        return DB::transaction(function () use ($period, $targetStatus): PpdbPeriod {
            $lockedPeriod = PpdbPeriod::query()->lockForUpdate()->findOrFail($period->id);
            if ($lockedPeriod->status !== $period->status) {
                throw new \DomainException('Periode PPDB berubah oleh pengguna lain. Muat ulang halaman lalu coba lagi.');
            }

            if ($targetStatus === PpdbPeriod::STATUS_VERIFICATION && $lockedPeriod->selection_finalized_at !== null) {
                throw new \DomainException('Verifikasi tidak dapat dibuka kembali setelah hasil seleksi difinalisasi. Gunakan fitur batalkan finalisasi dengan alasan yang jelas.');
            }

            $lockedPeriod->update(['status' => $targetStatus]);

            return $lockedPeriod->refresh();
        });
    }

    public function reopenVerification(PpdbPeriod $period, string $reason): PpdbPeriod
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('Alasan membuka kembali verifikasi wajib diisi.');
        }

        return DB::transaction(function () use ($period, $reason): PpdbPeriod {
            $lockedPeriod = PpdbPeriod::query()->lockForUpdate()->findOrFail($period->id);
            if ($lockedPeriod->status !== PpdbPeriod::STATUS_CLOSED) {
                throw new \DomainException('Fitur ini hanya dapat digunakan pada periode yang berstatus closed.');
            }

            if ($lockedPeriod->selection_finalized_at !== null) {
                throw new \DomainException('Verifikasi tidak dapat dibuka kembali setelah hasil seleksi difinalisasi.');
            }

            if ($this->dateAttribute($lockedPeriod, 'verification_ends_at') === null) {
                throw new \DomainException('Batas verifikasi wajib diisi sebelum periode dibuka kembali.');
            }

            $lockedPeriod->update(['status' => PpdbPeriod::STATUS_VERIFICATION]);
            PpdbAuditLog::create([
                'school_id' => $lockedPeriod->school_id,
                'actor_id' => auth()->id(),
                'action' => 'period_reopened_for_verification',
                'from_status' => PpdbPeriod::STATUS_CLOSED,
                'to_status' => PpdbPeriod::STATUS_VERIFICATION,
                'metadata' => [
                    'period_id' => $lockedPeriod->id,
                    'reason' => $reason,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 1000),
            ]);

            return $lockedPeriod->refresh();
        });
    }

    public function assertSchedule(PpdbPeriod $period, string $targetStatus): void
    {
        $registrationStartsAt = $this->dateAttribute($period, 'registration_starts_at');
        $registrationEndsAt = $this->dateAttribute($period, 'registration_ends_at');
        $verificationEndsAt = $this->dateAttribute($period, 'verification_ends_at');
        $announcementAt = $this->dateAttribute($period, 'announcement_at');
        $reregistrationEndsAt = $this->dateAttribute($period, 're_registration_ends_at');

        if ($registrationStartsAt === null || $registrationEndsAt === null) {
            throw new \DomainException('Jadwal pendaftaran wajib diisi.');
        }

        if ($registrationEndsAt->lessThanOrEqualTo($registrationStartsAt)) {
            throw new \DomainException('Akhir pendaftaran harus setelah awal pendaftaran.');
        }

        if ($verificationEndsAt !== null && $verificationEndsAt->lessThan($registrationEndsAt)) {
            throw new \DomainException('Batas verifikasi harus setelah akhir pendaftaran.');
        }

        if ($announcementAt !== null) {
            $verificationEnd = $verificationEndsAt ?? $registrationEndsAt;
            if ($announcementAt->lessThan($verificationEnd)) {
                throw new \DomainException('Waktu pengumuman harus setelah batas verifikasi atau akhir pendaftaran.');
            }
        }

        if ($reregistrationEndsAt !== null) {
            $reregistrationStartsAt = $announcementAt ?? $registrationEndsAt;
            if ($reregistrationEndsAt->lessThanOrEqualTo($reregistrationStartsAt)) {
                throw new \DomainException('Batas daftar ulang harus setelah waktu pengumuman.');
            }
        }

        if ($targetStatus === PpdbPeriod::STATUS_VERIFICATION && $verificationEndsAt === null) {
            throw new \DomainException('Batas verifikasi wajib diisi sebelum status verifikasi dibuka.');
        }

        if ($targetStatus === PpdbPeriod::STATUS_ANNOUNCED && $announcementAt === null) {
            throw new \DomainException('Waktu pengumuman wajib diisi sebelum hasil diumumkan.');
        }

        if ($targetStatus === PpdbPeriod::STATUS_ANNOUNCED && $period->selection_finalized_at === null) {
            throw new \DomainException('Hasil seleksi harus difinalisasi sebelum diumumkan.');
        }

        if ($targetStatus === PpdbPeriod::STATUS_REREGISTRATION) {
            if ($announcementAt === null) {
                throw new \DomainException('Waktu pengumuman wajib diisi sebelum daftar ulang dibuka.');
            }

            if ($reregistrationEndsAt === null || $reregistrationEndsAt->isPast()) {
                throw new \DomainException('Batas daftar ulang wajib diisi dan belum boleh lewat.');
            }
        }
    }

    /** @return array<string, list<string>> */
    public function transitions(): array
    {
        return self::TRANSITIONS;
    }

    private static function label(string $status): string
    {
        return match ($status) {
            PpdbPeriod::STATUS_DRAFT => 'draft',
            PpdbPeriod::STATUS_PUBLISHED => 'dipublikasikan',
            PpdbPeriod::STATUS_OPEN => 'pendaftaran dibuka',
            PpdbPeriod::STATUS_VERIFICATION => 'verifikasi',
            PpdbPeriod::STATUS_SELECTION => 'seleksi',
            PpdbPeriod::STATUS_ANNOUNCED => 'pengumuman',
            PpdbPeriod::STATUS_REREGISTRATION => 'daftar ulang',
            PpdbPeriod::STATUS_CLOSED => 'ditutup',
            default => $status,
        };
    }

    private function dateAttribute(PpdbPeriod $period, string $attribute): ?Carbon
    {
        $value = $period->getAttribute($attribute);

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        return is_string($value) && $value !== '' ? Carbon::parse($value) : null;
    }
}
