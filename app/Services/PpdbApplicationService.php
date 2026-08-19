<?php

namespace App\Services;

use App\Models\PpdbApplication;
use App\Models\PpdbAuditLog;
use App\Models\PpdbCandidate;
use App\Models\PpdbPathway;
use App\Models\PpdbPayment;
use App\Models\PpdbPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PpdbApplicationService
{
    /**
     * @param  array<string, mixed>  $candidateData
     * @param  array<string, mixed>  $guardianData
     * @return array{0: PpdbApplication, 1: string}
     */
    public function submitOnline(
        PpdbPeriod $period,
        PpdbPathway $pathway,
        array $candidateData,
        array $guardianData,
        string $contactEmail,
        string $contactPhone,
    ): array {
        return $this->submit(
            $period,
            $pathway,
            $candidateData,
            $guardianData,
            $contactEmail,
            $contactPhone,
            PpdbApplication::SOURCE_ONLINE,
            null,
        );
    }

    /**
     * @param  array<string, mixed>  $candidateData
     * @param  array<string, mixed>  $guardianData
     * @return array{0: PpdbApplication, 1: string}
     */
    public function submitOffline(
        PpdbPeriod $period,
        PpdbPathway $pathway,
        array $candidateData,
        array $guardianData,
        string $contactEmail,
        string $contactPhone,
        int $createdBy,
    ): array {
        return $this->submit(
            $period,
            $pathway,
            $candidateData,
            $guardianData,
            $contactEmail,
            $contactPhone,
            PpdbApplication::SOURCE_OFFLINE,
            $createdBy,
        );
    }

    /**
     * @param  array<string, mixed>  $candidateData
     * @param  array<string, mixed>  $guardianData
     * @return array{0: PpdbApplication, 1: string}
     */
    private function submit(
        PpdbPeriod $period,
        PpdbPathway $pathway,
        array $candidateData,
        array $guardianData,
        string $contactEmail,
        string $contactPhone,
        string $source,
        ?int $createdBy,
    ): array {
        if ($pathway->ppdb_period_id !== $period->id || ! $pathway->is_active) {
            throw new \DomainException('Jalur PPDB tidak sesuai dengan periode aktif.');
        }

        return DB::transaction(function () use ($period, $pathway, $candidateData, $guardianData, $contactEmail, $contactPhone, $source, $createdBy): array {
            $lockedPeriod = PpdbPeriod::query()->lockForUpdate()->findOrFail($period->id);
            if ($lockedPeriod->status !== PpdbPeriod::STATUS_OPEN || ! $lockedPeriod->is_registration_open) {
                throw new \DomainException('Pendaftaran baru hanya dapat dilakukan saat periode berstatus open dan berada dalam jadwal pendaftaran.');
            }

            $normalizedNik = self::normalizeIdentity($candidateData['nik'] ?? null);
            $normalizedNisn = self::normalizeIdentity($candidateData['nisn'] ?? null);

            if ($normalizedNik !== null && PpdbCandidate::query()
                ->where('nik_normalized', $normalizedNik)
                ->whereHas('application', fn ($query) => $query->where('ppdb_period_id', $lockedPeriod->id))
                ->exists()) {
                throw new \DomainException('Calon siswa dengan NIK tersebut sudah memiliki pendaftaran pada periode ini.');
            }

            if ($normalizedNisn !== null && PpdbCandidate::query()
                ->where('nisn_normalized', $normalizedNisn)
                ->whereHas('application', fn ($query) => $query->where('ppdb_period_id', $lockedPeriod->id))
                ->exists()) {
                throw new \DomainException('Calon siswa dengan NISN tersebut sudah memiliki pendaftaran pada periode ini.');
            }

            $candidateData['nik'] = self::nullableTrim($candidateData['nik'] ?? null);
            $candidateData['nisn'] = self::nullableTrim($candidateData['nisn'] ?? null);
            $candidateData['nik_normalized'] = $normalizedNik;
            $candidateData['nisn_normalized'] = $normalizedNisn;
            $sequence = $lockedPeriod->applications()->count() + 1;
            $applicationNumber = sprintf('%s-%04d', strtoupper($lockedPeriod->code), $sequence);
            $accessCode = (string) random_int(100000, 999999);
            $fee = (float) $pathway->registration_fee;
            $paymentRequired = $lockedPeriod->payment_required && $fee > 0;

            $application = PpdbApplication::create([
                'school_id' => $lockedPeriod->school_id,
                'ppdb_period_id' => $lockedPeriod->id,
                'ppdb_pathway_id' => $pathway->id,
                'created_by' => $createdBy,
                'application_number' => $applicationNumber,
                'source' => $source,
                'contact_email' => trim($contactEmail),
                'contact_phone' => trim($contactPhone),
                'access_code_hash' => Hash::make($accessCode),
                'verification_status' => PpdbApplication::VERIFICATION_SUBMITTED,
                'payment_status' => $paymentRequired ? PpdbPayment::STATUS_PENDING : 'not_required',
                'submitted_at' => now(),
            ]);

            $application->candidate()->create($candidateData);
            $application->guardians()->create([
                ...$guardianData,
                'is_primary' => true,
            ]);

            $application->payments()->create([
                'invoice_number' => 'INV-'.$applicationNumber,
                'type' => PpdbPayment::TYPE_REGISTRATION,
                'amount' => $fee,
                'status' => $paymentRequired ? PpdbPayment::STATUS_PENDING : PpdbPayment::STATUS_VERIFIED,
                'paid_amount' => 0,
            ]);

            $this->audit($application, 'application_submitted', null, PpdbApplication::VERIFICATION_SUBMITTED);

            return [$application, $accessCode];
        });
    }

    /** @param array<string, mixed> $metadata */
    public function audit(PpdbApplication $application, string $action, ?string $fromStatus, ?string $toStatus, array $metadata = []): void
    {
        PpdbAuditLog::create([
            'school_id' => $application->school_id,
            'ppdb_application_id' => $application->id,
            'actor_id' => auth()->id(),
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 1000),
        ]);
    }

    private static function normalizeIdentity(mixed $value): ?string
    {
        $normalized = preg_replace('/[^0-9a-z]/i', '', trim((string) $value));

        return $normalized === '' ? null : strtolower((string) $normalized);
    }

    private static function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
