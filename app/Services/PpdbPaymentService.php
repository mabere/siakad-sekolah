<?php

namespace App\Services;

use App\Models\PpdbApplication;
use App\Models\PpdbPayment;
use App\Models\PpdbPaymentHistory;
use App\Models\PpdbPeriod;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PpdbPaymentService
{
    public function submitProof(PpdbApplication $application, UploadedFile $file, string $notes, string $method = 'bank_transfer'): PpdbPayment
    {
        $application->loadMissing('period');
        $this->assertVerificationWindow($application->period);

        $fileService = app(PpdbFileService::class);
        $stored = $fileService->store($file, 'ppdb/payments/'.$application->id);
        $oldPath = null;

        try {
            $payment = DB::transaction(function () use ($application, $stored, $notes, $method, &$oldPath): PpdbPayment {
                $payment = $application->payments()
                    ->where('type', PpdbPayment::TYPE_REGISTRATION)
                    ->lockForUpdate()
                    ->first();
                if (! $payment instanceof PpdbPayment) {
                    throw (new ModelNotFoundException)->setModel(PpdbPayment::class);
                }

                if (! in_array($payment->status, [PpdbPayment::STATUS_PENDING, PpdbPayment::STATUS_REJECTED], true)) {
                    throw new \DomainException('Bukti pembayaran tidak dapat diperbarui dari status saat ini.');
                }

                $fromStatus = $payment->status;
                $oldPath = $payment->proof_file;
                $payment->update([
                    'status' => PpdbPayment::STATUS_SUBMITTED,
                    'payment_method' => $method,
                    'proof_file' => $stored['path'],
                    'proof_original_name' => $stored['original_name'],
                    'proof_mime_type' => $stored['mime_type'],
                    'proof_file_size' => $stored['file_size'],
                    'paid_amount' => $payment->amount,
                    'notes' => trim($notes) ?: null,
                ]);
                $application->update(['payment_status' => PpdbPayment::STATUS_SUBMITTED]);
                $this->history($application, $payment, $fromStatus, PpdbPayment::STATUS_SUBMITTED, trim($notes) ?: null);
                app(PpdbApplicationService::class)->audit($application, 'payment_proof_submitted', $fromStatus, PpdbPayment::STATUS_SUBMITTED);

                return $payment->fresh();
            });
        } catch (\Throwable $exception) {
            $fileService->delete($stored['path']);
            throw $exception;
        }

        $fileService->delete($oldPath);

        return $payment;
    }

    public function verify(PpdbPayment $payment): PpdbPayment
    {
        return DB::transaction(function () use ($payment): PpdbPayment {
            $lockedPayment = PpdbPayment::query()->lockForUpdate()->findOrFail($payment->id);
            if (! in_array($lockedPayment->status, [PpdbPayment::STATUS_PENDING, PpdbPayment::STATUS_SUBMITTED], true)) {
                throw new \DomainException('Pembayaran hanya dapat diverifikasi dari status menunggu pemeriksaan.');
            }

            $application = $lockedPayment->application()->firstOrFail();
            $application->loadMissing('period');
            $this->assertVerificationWindow($application->period);
            $fromStatus = $lockedPayment->status;
            $lockedPayment->update([
                'status' => PpdbPayment::STATUS_VERIFIED,
                'verified_by' => auth()->id(),
                'paid_at' => $lockedPayment->paid_at ?? now(),
            ]);
            $application->update(['payment_status' => PpdbPayment::STATUS_VERIFIED]);
            $this->history($application, $lockedPayment, $fromStatus, PpdbPayment::STATUS_VERIFIED, $lockedPayment->notes);
            app(PpdbApplicationService::class)->audit($application, 'payment_status_changed', $fromStatus, PpdbPayment::STATUS_VERIFIED);

            return $lockedPayment->fresh();
        });
    }

    public function reject(PpdbPayment $payment, string $note): PpdbPayment
    {
        $note = trim($note);
        if ($note === '') {
            throw new \InvalidArgumentException('Alasan penolakan pembayaran wajib diisi.');
        }

        return DB::transaction(function () use ($payment, $note): PpdbPayment {
            $lockedPayment = PpdbPayment::query()->lockForUpdate()->findOrFail($payment->id);
            if (! in_array($lockedPayment->status, [PpdbPayment::STATUS_PENDING, PpdbPayment::STATUS_SUBMITTED], true)) {
                throw new \DomainException('Pembayaran dengan status ini tidak dapat ditolak ulang.');
            }

            $application = $lockedPayment->application()->firstOrFail();
            $application->loadMissing('period');
            $this->assertVerificationWindow($application->period);
            $fromStatus = $lockedPayment->status;
            $lockedPayment->update([
                'status' => PpdbPayment::STATUS_REJECTED,
                'verified_by' => auth()->id(),
                'notes' => $note,
            ]);
            $application->update(['payment_status' => PpdbPayment::STATUS_REJECTED]);
            $this->history($application, $lockedPayment, $fromStatus, PpdbPayment::STATUS_REJECTED, $note);
            app(PpdbApplicationService::class)->audit($application, 'payment_status_changed', $fromStatus, PpdbPayment::STATUS_REJECTED, ['note' => $note]);

            return $lockedPayment->fresh();
        });
    }

    private function history(PpdbApplication $application, PpdbPayment $payment, ?string $fromStatus, string $toStatus, ?string $notes = null): void
    {
        PpdbPaymentHistory::create([
            'school_id' => $application->school_id,
            'ppdb_application_id' => $application->id,
            'ppdb_payment_id' => $payment->id,
            'actor_id' => auth()->id(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'amount' => $payment->paid_amount,
            'proof_file' => $payment->proof_file,
            'notes' => $notes,
        ]);
    }

    private function assertVerificationWindow(?PpdbPeriod $period): void
    {
        if ($period === null || ! $period->allowsVerification()) {
            throw new \DomainException('Verifikasi pendaftaran hanya dapat dilakukan saat periode berstatus open atau verification.');
        }
    }
}
