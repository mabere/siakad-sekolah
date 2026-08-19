<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): RedirectResponse|Redirector|null
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.");

            return null;
        }

        $credentials['is_active'] = true;

        if (Auth::attempt($credentials, $this->remember)) {
            RateLimiter::clear($throttleKey);
            session()->regenerate();
            $user = Auth::user();

            // Define role priority
            $priority = [
                'Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum',
                'Wakasek Kesiswaan', 'Wakasek Sarana', 'Guru BK', 'Pembina Ekstrakurikuler',
                'Staf Tata Usaha', 'Wali Kelas', 'Guru', 'Orang Tua', 'Siswa',
            ];

            $userRoles = $user->roles->pluck('name')->toArray();
            Log::info('User logging in: '.$user->email.' with roles: '.implode(', ', $userRoles));

            $activeRole = null;

            foreach ($priority as $role) {
                if (in_array($role, $userRoles)) {
                    $activeRole = $role;
                    break;
                }
            }

            if ($activeRole) {
                Log::info('Active role assigned: '.$activeRole);
                session(['active_role' => $activeRole]);

                if (in_array($activeRole, ['Super Admin', 'Admin Sekolah'])) {
                    return redirect()->route('admin.dashboard');
                } elseif ($activeRole === 'Kepala Sekolah') {
                    return redirect()->route('kepsek.dashboard');
                } elseif (str_starts_with((string) $activeRole, 'Wakasek')) {
                    return redirect()->route('wakasek.dashboard');
                } elseif ($activeRole === 'Staf Tata Usaha') {
                    return redirect()->route('tu.dashboard');
                } elseif ($activeRole === 'Orang Tua') {
                    return redirect()->route('parent.dashboard');
                } elseif (in_array($activeRole, ['Guru', 'Wali Kelas', 'Guru BK', 'Pembina Ekstrakurikuler'])) {
                    return redirect()->route('guru.dashboard');
                } elseif ($activeRole === 'Siswa') {
                    return redirect()->route('siswa.dashboard');
                }
            }

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            Log::warning('Login failed - no active role matched for: '.$user->email);
            $this->addError('email', 'Akun belum memiliki akses portal yang sesuai.');

            return null;
        }

        RateLimiter::hit($throttleKey, 60);
        $this->addError('email', 'Kredensial yang diberikan tidak cocok dengan catatan kami.');

        return null;
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
