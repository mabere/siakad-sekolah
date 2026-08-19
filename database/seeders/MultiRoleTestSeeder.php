<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class MultiRoleTestSeeder extends Seeder
{
    public function run(): void
    {
        $guru1 = User::where('email', 'guru1@siakad.test')->first();
        if ($guru1) {
            $guru1->assignRole('Wakasek Kurikulum');
            $guru1->assignRole('Wali Kelas');
            $this->command->info('Role Wakasek Kurikulum dan Wali Kelas berhasil ditambahkan ke '.$guru1->email);
        }

        $guru2 = User::where('email', 'guru2@siakad.test')->first();
        if ($guru2) {
            $guru2->assignRole('Wali Kelas');
            $this->command->info('Role Wali Kelas berhasil ditambahkan ke '.$guru2->email);
        }
    }
}
