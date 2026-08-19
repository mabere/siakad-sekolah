<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class RoleSwitcher extends Component
{
    public ?string $activeRole = null;

    /** @var array<int, string> */
    public array $availableRoles = [];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->activeRole = session('active_role');
            // get all roles except active
            $this->availableRoles = $user->roles->pluck('name')->reject(function ($name) {
                return $name === $this->activeRole;
            })->values()->toArray();
        }
    }

    public function switchRole(string $roleName): RedirectResponse|Redirector|null
    {
        $user = Auth::user();
        if ($user && $user->hasRole($roleName)) {
            session(['active_role' => $roleName]);

            // Redirect based on the new active role
            if (in_array($roleName, ['Super Admin', 'Admin Sekolah'])) {
                return redirect()->route('admin.dashboard');
            } elseif ($roleName === 'Kepala Sekolah') {
                return redirect()->route('kepsek.dashboard');
            } elseif (str_starts_with((string) $roleName, 'Wakasek')) {
                return redirect()->route('wakasek.dashboard');
            } elseif (in_array($roleName, ['Staf Tata Usaha', 'Panitia PPDB'])) {
                return redirect()->route('tu.dashboard');
            } elseif ($roleName === 'Orang Tua') {
                return redirect()->route('parent.dashboard');
            } elseif (in_array($roleName, ['Guru', 'Wali Kelas', 'Guru BK', 'Pembina Ekstrakurikuler'])) {
                return redirect()->route('guru.dashboard');
            } elseif ($roleName === 'Siswa') {
                return redirect()->route('siswa.dashboard');
            }
        }

        return null;
    }

    public function render(): View
    {
        return view('livewire.role-switcher');
    }
}
