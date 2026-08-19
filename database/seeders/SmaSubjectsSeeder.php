<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SmaSubjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Kurikulum Merdeka - Jenjang SMA (Kelas 10 Fase E, Kelas 11 & 12 Fase F)
     * Berdasarkan Permendikbudristek No. 12 Tahun 2024 & Kepka BSKAP No. 032/H/KR/2024
     */
    public function run(): void
    {
        $schools = School::all();

        if ($schools->isEmpty()) {
            $this->command?->info('Tidak ada sekolah ditemukan. Melewati seeding mata pelajaran SMA.');
            return;
        }

        $smaSubjects = [
            // --- 1. KELOMPOK MATA PELAJARAN UMUM (WAJIB KELAS 10, 11, 12) ---
            [
                'code' => 'PAI',
                'name' => 'Pendidikan Agama Islam dan Budi Pekerti',
                'type' => 'Wajib',
                'aliases' => ['PAI', 'PABP', 'Pendidikan Agama Islam', 'Pendidikan Agama'],
            ],
            [
                'code' => 'PAK',
                'name' => 'Pendidikan Agama Kristen dan Budi Pekerti',
                'type' => 'Wajib',
                'aliases' => ['PAK', 'Pendidikan Agama Kristen'],
            ],
            [
                'code' => 'PAKT',
                'name' => 'Pendidikan Agama Katolik dan Budi Pekerti',
                'type' => 'Wajib',
                'aliases' => ['PAKT', 'Pendidikan Agama Katolik'],
            ],
            [
                'code' => 'PAH',
                'name' => 'Pendidikan Agama Hindu dan Budi Pekerti',
                'type' => 'Wajib',
                'aliases' => ['PAH', 'Pendidikan Agama Hindu'],
            ],
            [
                'code' => 'PAB',
                'name' => 'Pendidikan Agama Buddha dan Budi Pekerti',
                'type' => 'Wajib',
                'aliases' => ['PAB', 'Pendidikan Agama Buddha'],
            ],
            [
                'code' => 'PAKH',
                'name' => 'Pendidikan Agama Khonghucu dan Budi Pekerti',
                'type' => 'Wajib',
                'aliases' => ['PAKH', 'Pendidikan Agama Khonghucu'],
            ],
            [
                'code' => 'PCSL',
                'name' => 'Pendidikan Pancasila',
                'type' => 'Wajib',
                'aliases' => ['PCSL', 'PPKN', 'PKN', 'Pendidikan Pancasila dan Kewarganegaraan'],
            ],
            [
                'code' => 'BIND',
                'name' => 'Bahasa Indonesia',
                'type' => 'Wajib',
                'aliases' => ['BIND', 'BIN', 'B. Indonesia', 'B Indo'],
            ],
            [
                'code' => 'MTK',
                'name' => 'Matematika',
                'type' => 'Wajib',
                'aliases' => ['MTK', 'MATH', 'MAT', 'Matematika Wajib', 'Matematika Umum'],
            ],
            [
                'code' => 'BING',
                'name' => 'Bahasa Inggris',
                'type' => 'Wajib',
                'aliases' => ['BING', 'BIG', 'ENG', 'B. Inggris', 'B Inggris', 'English'],
            ],
            [
                'code' => 'PJOK',
                'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'type' => 'Wajib',
                'aliases' => ['PJOK', 'Penjas', 'Penjaskes', 'Olahraga'],
            ],
            [
                'code' => 'SEJ',
                'name' => 'Sejarah',
                'type' => 'Wajib',
                'aliases' => ['SEJ', 'SEJ-W', 'Sejarah Indonesia', 'Sejarah Wajib', 'Sejarah Umum'],
            ],
            [
                'code' => 'SNRP',
                'name' => 'Seni Rupa',
                'type' => 'Wajib',
                'aliases' => ['SNRP', 'Seni Budaya - Seni Rupa', 'Seni Budaya (Seni Rupa)'],
            ],
            [
                'code' => 'SNMS',
                'name' => 'Seni Musik',
                'type' => 'Wajib',
                'aliases' => ['SNMS', 'Seni Budaya - Seni Musik', 'Seni Budaya (Seni Musik)'],
            ],
            [
                'code' => 'SNTR',
                'name' => 'Seni Tari',
                'type' => 'Wajib',
                'aliases' => ['SNTR', 'Seni Budaya - Seni Tari', 'Seni Budaya (Seni Tari)'],
            ],
            [
                'code' => 'SNTT',
                'name' => 'Seni Teater',
                'type' => 'Wajib',
                'aliases' => ['SNTT', 'Seni Budaya - Seni Teater', 'Seni Budaya (Seni Teater)'],
            ],
            [
                'code' => 'PKWU',
                'name' => 'Prakarya dan Kewirausahaan',
                'type' => 'Wajib',
                'aliases' => ['PKWU', 'Prakarya', 'Kewirausahaan'],
            ],

            // --- 2. KELOMPOK MIPA / SAINS (FONDASI KELAS 10 & PILIHAN KELAS 11-12) ---
            [
                'code' => 'BIO',
                'name' => 'Biologi',
                'type' => 'Peminatan',
                'aliases' => ['BIO', 'BIOL', 'Biologi (Peminatan)'],
            ],
            [
                'code' => 'FIS',
                'name' => 'Fisika',
                'type' => 'Peminatan',
                'aliases' => ['FIS', 'PHYS', 'Fisika (Peminatan)'],
            ],
            [
                'code' => 'KIM',
                'name' => 'Kimia',
                'type' => 'Peminatan',
                'aliases' => ['KIM', 'CHEM', 'Kimia (Peminatan)'],
            ],
            [
                'code' => 'ITK',
                'name' => 'Informatika',
                'type' => 'Wajib',
                'aliases' => ['ITK', 'INF', 'TIK', 'Ilmu Komputer'],
            ],
            [
                'code' => 'MTKL',
                'name' => 'Matematika Tingkat Lanjut',
                'type' => 'Peminatan',
                'aliases' => ['MTKL', 'MTK-L', 'Matematika Peminatan', 'Matematika Lanjut'],
            ],

            // --- 3. KELOMPOK IPS / HUMANIORA (FONDASI KELAS 10 & PILIHAN KELAS 11-12) ---
            [
                'code' => 'SOS',
                'name' => 'Sosiologi',
                'type' => 'Peminatan',
                'aliases' => ['SOS', 'SOC', 'Sosiologi (Peminatan)'],
            ],
            [
                'code' => 'EKO',
                'name' => 'Ekonomi',
                'type' => 'Peminatan',
                'aliases' => ['EKO', 'ECON', 'Ekonomi (Peminatan)'],
            ],
            [
                'code' => 'GEO',
                'name' => 'Geografi',
                'type' => 'Peminatan',
                'aliases' => ['GEO', 'GEOG', 'Geografi (Peminatan)'],
            ],
            [
                'code' => 'SEJL',
                'name' => 'Sejarah Tingkat Lanjut',
                'type' => 'Peminatan',
                'aliases' => ['SEJL', 'SEJ-L', 'Sejarah Peminatan', 'Sejarah Lanjut'],
            ],
            [
                'code' => 'ANT',
                'name' => 'Antropologi',
                'type' => 'Peminatan',
                'aliases' => ['ANT', 'ANTH', 'Antropologi (Peminatan)'],
            ],

            // --- 4. KELOMPOK BAHASA & BUDAYA (PILIHAN KELAS 11-12) ---
            [
                'code' => 'BINL',
                'name' => 'Bahasa Indonesia Tingkat Lanjut',
                'type' => 'Peminatan',
                'aliases' => ['BINL', 'BIND-L', 'Bahasa dan Sastra Indonesia'],
            ],
            [
                'code' => 'BINGL',
                'name' => 'Bahasa Inggris Tingkat Lanjut',
                'type' => 'Peminatan',
                'aliases' => ['BINGL', 'BING-L', 'Bahasa dan Sastra Inggris'],
            ],
            [
                'code' => 'BARB',
                'name' => 'Bahasa Arab',
                'type' => 'Peminatan',
                'aliases' => ['BARB', 'ARB', 'Bahasa & Sastra Arab'],
            ],
            [
                'code' => 'BMND',
                'name' => 'Bahasa Mandarin',
                'type' => 'Peminatan',
                'aliases' => ['BMND', 'MND', 'Bahasa & Sastra Mandarin'],
            ],
            [
                'code' => 'BJP',
                'name' => 'Bahasa Jepang',
                'type' => 'Peminatan',
                'aliases' => ['BJP', 'JPN', 'Bahasa & Sastra Jepang'],
            ],
            [
                'code' => 'BJRM',
                'name' => 'Bahasa Jerman',
                'type' => 'Peminatan',
                'aliases' => ['BJRM', 'GER', 'Bahasa & Sastra Jerman'],
            ],
            [
                'code' => 'BPRC',
                'name' => 'Bahasa Perancis',
                'type' => 'Peminatan',
                'aliases' => ['BPRC', 'FRA', 'Bahasa & Sastra Perancis'],
            ],
            [
                'code' => 'BKOR',
                'name' => 'Bahasa Korea',
                'type' => 'Peminatan',
                'aliases' => ['BKOR', 'KOR', 'Bahasa & Sastra Korea'],
            ],

            // --- 5. KELOMPOK MUATAN LOKAL (MULOK) ---
            [
                'code' => 'BDER',
                'name' => 'Bahasa Daerah (Muatan Lokal)',
                'type' => 'Muatan Lokal',
                'aliases' => ['BDER', 'B. Daerah', 'Bahasa Sunda', 'Bahasa Jawa', 'Mulok Bahasa Daerah'],
            ],
            [
                'code' => 'PLH',
                'name' => 'Pendidikan Lingkungan Hidup',
                'type' => 'Muatan Lokal',
                'aliases' => ['PLH', 'Lingkungan Hidup', 'Mulok PLH'],
            ],
        ];

        foreach ($schools as $school) {
            foreach ($smaSubjects as $subjData) {
                $code = $subjData['code'];
                $name = $subjData['name'];
                $type = $subjData['type'];
                $aliases = $subjData['aliases'] ?? [];

                // Cari existing subject berdasarkan kode ATAU nama ATAU alias
                $existing = Subject::query()
                    ->where('school_id', $school->id)
                    ->where(function ($query) use ($code, $name, $aliases) {
                        $query->where('code', $code)
                            ->orWhere('name', $name);

                        foreach ($aliases as $alias) {
                            $query->orWhere('code', $alias)
                                ->orWhere('name', $alias);
                        }
                    })
                    ->first();

                if ($existing) {
                    // Update dengan data kanonikal standar nasional tanpa membuat duplikasi
                    $existing->update([
                        'name' => $name,
                        'code' => $code,
                        'type' => $type,
                    ]);
                } else {
                    // Buat baru jika belum ada
                    Subject::create([
                        'school_id' => $school->id,
                        'name' => $name,
                        'code' => $code,
                        'type' => $type,
                    ]);
                }
            }
        }
    }
}
