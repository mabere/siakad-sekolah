<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $setupRequired = School::query()
            ->where('is_active', true)
            ->where('is_setup_completed', false)
            ->exists();

        if (! $setupRequired) {
            return $next($request);
        }

        // Login and the setup page must remain reachable while the first setup
        // is incomplete. The setup page itself is protected by auth and the
        // Super Admin active-role middleware in routes/web.php.
        if ($request->routeIs('setup.*', 'login', 'logout')) {
            return $next($request);
        }

        // Livewire update requests do not carry the original page middleware
        // stack. Only an authenticated Super Admin may continue a setup
        // component update while setup is incomplete.
        if ($request->is('livewire/update')) {
            $user = Auth::user();

            if (! $user instanceof User || ! $user->is_active || ! $user->hasRole('Super Admin')) {
                return redirect()->route('login');
            }

            return $next($request);
        }

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('setup.wizard');
    }
}
