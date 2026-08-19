<?php

namespace Database\Seeders;

use App\Support\PpdbPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Default Roles
        $roles = [
            'Super Admin',
            'Admin Sekolah',
            'Kepala Sekolah',
            'Wakasek Kurikulum',
            'Wakasek Kesiswaan',
            'Wakasek Sarana',
            'Wakasek Humas',
            'Guru',
            'Wali Kelas',
            'Guru BK',
            'Pembina Ekstrakurikuler',
            'Staf Tata Usaha',
            'Panitia PPDB',
            'Siswa',
            'Orang Tua',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        foreach (PpdbPermissions::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (PpdbPermissions::roleMap() as $role => $permissions) {
            Role::findByName($role)->givePermissionTo($permissions);
        }
    }
}
