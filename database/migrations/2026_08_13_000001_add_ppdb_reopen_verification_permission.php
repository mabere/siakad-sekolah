<?php

use App\Support\PpdbPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate(PpdbPermissions::REOPEN_VERIFICATION, 'web');

        foreach (['Super Admin', 'Admin Sekolah'] as $roleName) {
            Role::findOrCreate($roleName, 'web')->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        foreach (['Super Admin', 'Admin Sekolah'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            $role?->revokePermissionTo(PpdbPermissions::REOPEN_VERIFICATION);
        }

        Permission::query()
            ->where('name', PpdbPermissions::REOPEN_VERIFICATION)
            ->where('guard_name', 'web')
            ->delete();
    }
};
