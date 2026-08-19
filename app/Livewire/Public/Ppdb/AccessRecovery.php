<?php

namespace App\Livewire\Public\Ppdb;

use App\Services\PpdbAccessRecoveryService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class AccessRecovery extends Component
{
    public string $applicationNumber = '';

    public string $contactEmail = '';

    public string $otp = '';

    public bool $otpSent = false;

    public ?string $newAccessCode = null;

    public string $message = '';

    public function requestOtp(): void
    {
        $this->validate([
            'applicationNumber' => ['required', 'string', 'max:50'],
            'contactEmail' => ['required', 'email', 'max:255'],
        ]);

        app(PpdbAccessRecoveryService::class)->requestOtp($this->applicationNumber, $this->contactEmail);
        $this->otpSent = true;
        $this->message = 'Jika nomor pendaftaran dan email cocok, kode OTP telah dikirim. Periksa inbox atau folder spam.';
    }

    public function resetAccessCode(): void
    {
        $this->validate([
            'applicationNumber' => ['required', 'string', 'max:50'],
            'contactEmail' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $newAccessCode = app(PpdbAccessRecoveryService::class)->verifyAndReset(
            $this->applicationNumber,
            $this->contactEmail,
            $this->otp,
        );

        if ($newAccessCode === null || $newAccessCode === '') {
            $this->addError('otp', 'OTP tidak valid, sudah kedaluwarsa, atau sudah digunakan.');

            return;
        }

        $this->newAccessCode = $newAccessCode;
        $this->otpSent = false;
        $this->otp = '';
        $this->message = 'PIN baru berhasil dibuat. Simpan PIN ini sebelum menutup halaman.';
    }

    public function resetForm(): void
    {
        $this->reset(['applicationNumber', 'contactEmail', 'otp', 'otpSent', 'newAccessCode', 'message']);
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.public.ppdb.access-recovery');
    }
}
