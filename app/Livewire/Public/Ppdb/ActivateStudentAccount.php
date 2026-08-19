<?php

namespace App\Livewire\Public\Ppdb;

use App\Models\PpdbStudentActivation;
use App\Services\PpdbStudentActivationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ActivateStudentAccount extends Component
{
    public string $token = '';

    public string $studentName = '';

    public string $username = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $isValid = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $activation = app(PpdbStudentActivationService::class)->findValid($token);

        if (! $activation instanceof PpdbStudentActivation) {
            return;
        }

        $this->studentName = (string) $activation->user?->name;
        $this->username = (string) $activation->user?->email;
        $this->isValid = $activation->user !== null;
    }

    public function activate(): mixed
    {
        if (! $this->isValid) {
            $this->addError('password', 'Tautan aktivasi tidak valid atau sudah kedaluwarsa. Silakan buat tautan baru dari halaman cek status PPDB.');

            return null;
        }

        $this->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            $user = app(PpdbStudentActivationService::class)->activate($this->token, $this->password);
        } catch (\DomainException $exception) {
            $this->isValid = false;
            $this->addError('password', $exception->getMessage());

            return null;
        }

        Auth::login($user);
        session()->regenerate();
        session(['active_role' => 'Siswa']);

        return redirect()->route('siswa.dashboard');
    }

    public function render(): View
    {
        return view('livewire.public.ppdb.activate-student-account');
    }
}
