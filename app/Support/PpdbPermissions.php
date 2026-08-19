<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Role;

final class PpdbPermissions
{
    public const VIEW_APPLICATIONS = 'ppdb.view_applications';

    public const MANAGE_PERIODS = 'ppdb.manage_periods';

    public const REOPEN_VERIFICATION = 'ppdb.reopen_verification';

    public const CANCEL_FINALIZATION = 'ppdb.cancel_finalization';

    public const REGISTER_OFFLINE = 'ppdb.register_offline';

    public const VERIFY_DOCUMENTS = 'ppdb.verify_documents';

    public const VERIFY_PAYMENTS = 'ppdb.verify_payments';

    public const MANAGE_SELECTION = 'ppdb.manage_selection';

    public const FINALIZE_SELECTION = 'ppdb.finalize_selection';

    public const MANAGE_REREGISTRATION = 'ppdb.manage_reregistration';

    public const CONVERT_STUDENT = 'ppdb.convert_student';

    public const RESET_ACCESS_CODE = 'ppdb.reset_access_code';

    public const EXPORT_APPLICATIONS = 'ppdb.export_applications';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::VIEW_APPLICATIONS,
            self::MANAGE_PERIODS,
            self::REOPEN_VERIFICATION,
            self::CANCEL_FINALIZATION,
            self::REGISTER_OFFLINE,
            self::VERIFY_DOCUMENTS,
            self::VERIFY_PAYMENTS,
            self::MANAGE_SELECTION,
            self::FINALIZE_SELECTION,
            self::MANAGE_REREGISTRATION,
            self::CONVERT_STUDENT,
            self::RESET_ACCESS_CODE,
            self::EXPORT_APPLICATIONS,
        ];
    }

    /** @return array<string, list<string>> */
    public static function roleMap(): array
    {
        return [
            'Super Admin' => self::all(),
            'Admin Sekolah' => self::all(),
            'Kepala Sekolah' => [self::VIEW_APPLICATIONS, self::MANAGE_SELECTION, self::FINALIZE_SELECTION, self::EXPORT_APPLICATIONS],
            'Staf Tata Usaha' => [self::VIEW_APPLICATIONS, self::REGISTER_OFFLINE, self::VERIFY_DOCUMENTS, self::VERIFY_PAYMENTS, self::MANAGE_REREGISTRATION, self::CONVERT_STUDENT, self::RESET_ACCESS_CODE, self::EXPORT_APPLICATIONS],
            'Panitia PPDB' => [self::VIEW_APPLICATIONS, self::REGISTER_OFFLINE, self::VERIFY_DOCUMENTS, self::VERIFY_PAYMENTS, self::MANAGE_SELECTION, self::MANAGE_REREGISTRATION, self::RESET_ACCESS_CODE, self::EXPORT_APPLICATIONS],
        ];
    }

    public static function allows(string $permission): bool
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }

        $activeRole = session('active_role');
        if (! is_string($activeRole) || $activeRole === '') {
            return $user->can($permission);
        }

        $roleId = $user->roles()->where('name', $activeRole)->value('roles.id');
        if ($roleId === null) {
            return false;
        }

        $role = Role::query()->whereKey($roleId)->first();

        return $role !== null && $role->hasPermissionTo($permission);
    }

    public static function authorize(string $permission): void
    {
        abort_unless(self::allows($permission), 403);
    }
}
