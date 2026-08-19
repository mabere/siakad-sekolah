<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, mixed ...$roles): Response
    {
        $activeRole = session('active_role');

        // If roles string contains pipe (like Spatie sometimes uses), we parse it, but ...$roles usually gives arguments
        // However, Laravel middleware parameters are passed as variadic arguments or comma separated.
        $allowedRoles = is_array($roles[0] ?? null) ? $roles[0] : (is_string($roles[0] ?? null) && str_contains($roles[0], '|') ? explode('|', $roles[0]) : $roles);

        $user = Auth::user();
        $roleIsStillAssigned = $user instanceof User
            && $activeRole
            && $user->is_active
            && $user->hasRole($activeRole);

        if (! $roleIsStillAssigned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors(['email' => 'Sesi role tidak lagi valid. Silakan login kembali.']);
        }

        if (! in_array($activeRole, $allowedRoles, true)) {
            // Redirect to appropriate dashboard based on their active role
            if (in_array($activeRole, ['Super Admin', 'Admin Sekolah'])) {
                return redirect()->route('admin.dashboard');
            } elseif ($activeRole === 'Kepala Sekolah') {
                return redirect()->route('kepsek.dashboard');
            } elseif (str_starts_with((string) $activeRole, 'Wakasek')) {
                return redirect()->route('wakasek.dashboard');
            } elseif (in_array($activeRole, ['Staf Tata Usaha', 'Panitia PPDB'], true)) {
                return redirect()->route('tu.dashboard');
            } elseif ($activeRole === 'Orang Tua') {
                return redirect()->route('parent.dashboard');
            } elseif (in_array($activeRole, ['Guru', 'Wali Kelas', 'Guru BK', 'Pembina Ekstrakurikuler'])) {
                return redirect()->route('guru.dashboard');
            } elseif ($activeRole === 'Siswa') {
                return redirect()->route('siswa.dashboard');
            }

            // If fallback or active role is invalid
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors(['email' => 'Akses ditolak atau sesi role tidak valid. Silakan login kembali.']);
        }

        return $next($request);
    }
}
