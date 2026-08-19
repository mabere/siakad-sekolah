<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CurrentSchool
{
    private ?School $school = null;

    public function get(): School
    {
        if ($this->school instanceof School) {
            return $this->school;
        }

        $user = Auth::user();

        if (! $user instanceof Authenticatable) {
            throw new AccessDeniedHttpException('Sesi pengguna tidak valid.');
        }

        $schoolId = data_get($user, 'school_id');

        if ($schoolId) {
            return $this->school = School::query()
                ->whereKey($schoolId)
                ->where('is_active', true)
                ->firstOrFail();
        }

        if ($user->hasRole('Super Admin')) {
            return $this->school = School::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->firstOrFail();
        }

        throw new AccessDeniedHttpException('Pengguna belum terhubung ke sekolah aktif.');
    }

    public function id(): int
    {
        return (int) $this->get()->getKey();
    }
}
