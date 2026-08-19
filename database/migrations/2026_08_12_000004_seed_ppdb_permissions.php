<?php

use App\Support\PpdbPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach (PpdbPermissions::all() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (PpdbPermissions::roleMap() as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        foreach (PpdbPermissions::all() as $permissionName) {
            Permission::query()->where('name', $permissionName)->where('guard_name', 'web')->delete();
        }
    }
};
