<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\ViolationMaster;
use Illuminate\Database\Seeder;

class ViolationMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $violations = [
            ['code' => 'R01', 'name' => 'Terlambat Masuk Sekolah (< 15 menit)', 'category' => 'Ringan', 'default_points' => 5],
            ['code' => 'R02', 'name' => 'Atribut Seragam Tidak Lengkap', 'category' => 'Ringan', 'default_points' => 5],
            ['code' => 'R03', 'name' => 'Makan/Minum Saat Jam Pelajaran', 'category' => 'Ringan', 'default_points' => 5],
            ['code' => 'R04', 'name' => 'Rambut Panjang/Berdandan Berlebihan', 'category' => 'Ringan', 'default_points' => 5],
            ['code' => 'R05', 'name' => 'Membuang Sampah Sembarangan', 'category' => 'Ringan', 'default_points' => 5],

            ['code' => 'S01', 'name' => 'Terlambat Masuk Sekolah (> 15 menit / Berulang)', 'category' => 'Sedang', 'default_points' => 15],
            ['code' => 'S02', 'name' => 'Bolos / Keluar Lingkungan Sekolah Tanpa Izin', 'category' => 'Sedang', 'default_points' => 20],
            ['code' => 'S03', 'name' => 'Membawa / Menggunakan HP Saat KBM Tanpa Izin', 'category' => 'Sedang', 'default_points' => 15],
            ['code' => 'S04', 'name' => 'Merusak Fasilitas Sekolah (Kecil)', 'category' => 'Sedang', 'default_points' => 20],
            ['code' => 'S05', 'name' => 'Berkata Kasar / Tidak Sopan Pada Guru', 'category' => 'Sedang', 'default_points' => 25],

            ['code' => 'B01', 'name' => 'Berkelahi / Tawuran', 'category' => 'Berat', 'default_points' => 50],
            ['code' => 'B02', 'name' => 'Membawa/Merokok di Area Sekolah', 'category' => 'Berat', 'default_points' => 35],
            ['code' => 'B03', 'name' => 'Membawa Senjata Tajam / Miras / Narkoba', 'category' => 'Berat', 'default_points' => 100],
            ['code' => 'B04', 'name' => 'Mencuri / Tindak Kriminal', 'category' => 'Berat', 'default_points' => 100],
            ['code' => 'B05', 'name' => 'Bullying / Pelecehan', 'category' => 'Berat', 'default_points' => 50],
        ];

        foreach ($violations as $v) {
            ViolationMaster::firstOrCreate(
                ['school_id' => $school->id, 'code' => $v['code']],
                [
                    'name' => $v['name'],
                    'category' => $v['category'],
                    'default_points' => $v['default_points'],
                ]
            );
        }
    }
}
