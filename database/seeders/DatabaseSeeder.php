<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            // MasterDataSeeder::class, // Dihapus/dikomentari agar data awal benar-benar bersih
        ]);

        $configuredSeedPassword = config('app.seed_password');
        $seedPassword = is_string($configuredSeedPassword) && $configuredSeedPassword !== ''
            ? $configuredSeedPassword
            : (app()->isProduction() ? null : 'password');

        if (! $seedPassword) {
            throw new RuntimeException('SIAKAD_SEED_PASSWORD wajib diisi sebelum menjalankan seeder di production.');
        }

        // Buat sekolah default dengan is_setup_completed = false
        $school = School::firstOrCreate(
            ['name' => 'Sekolah Siakad'],
            [
                'level' => 'SMP',
                'status' => 'NEGERI',
                'is_setup_completed' => false,
            ]
        );

        // Buat Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@siakad.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($seedPassword),
                'school_id' => $school->id,
            ]
        );

        if (! $superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole('Super Admin');
        }
    }
}
