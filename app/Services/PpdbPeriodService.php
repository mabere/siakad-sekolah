<?php

namespace App\Services;

use App\Models\PpdbPathway;
use App\Models\PpdbPeriod;
use Illuminate\Support\Facades\DB;

class PpdbPeriodService
{
    /** @var array<string, array{name: string, description: string, sort_order: int, requirements: list<array{code: string, name: string}>}> */
    private const DEFAULT_PATHWAYS = [
        PpdbPathway::UMUM => [
            'name' => 'Jalur Umum',
            'description' => 'Penerimaan umum sesuai persyaratan sekolah.',
            'sort_order' => 10,
            'requirements' => [
                ['code' => 'kartu_keluarga', 'name' => 'Kartu Keluarga'],
                ['code' => 'akta_kelahiran', 'name' => 'Akta Kelahiran'],
                ['code' => 'ijazah_skhun', 'name' => 'Ijazah atau SKHUN'],
                ['code' => 'pas_foto', 'name' => 'Pas Foto'],
            ],
        ],
        PpdbPathway::PRESTASI => [
            'name' => 'Jalur Prestasi',
            'description' => 'Penerimaan berdasarkan prestasi akademik atau nonakademik.',
            'sort_order' => 20,
            'requirements' => [
                ['code' => 'kartu_keluarga', 'name' => 'Kartu Keluarga'],
                ['code' => 'akta_kelahiran', 'name' => 'Akta Kelahiran'],
                ['code' => 'ijazah_skhun', 'name' => 'Ijazah atau SKHUN'],
                ['code' => 'bukti_prestasi', 'name' => 'Bukti Prestasi'],
                ['code' => 'pas_foto', 'name' => 'Pas Foto'],
            ],
        ],
        PpdbPathway::PINDAHAN => [
            'name' => 'Jalur Pindahan',
            'description' => 'Penerimaan peserta didik pindahan sesuai kuota sekolah.',
            'sort_order' => 30,
            'requirements' => [
                ['code' => 'kartu_keluarga', 'name' => 'Kartu Keluarga'],
                ['code' => 'akta_kelahiran', 'name' => 'Akta Kelahiran'],
                ['code' => 'surat_pindah', 'name' => 'Surat Pindah atau Rekomendasi'],
                ['code' => 'rapor', 'name' => 'Rapor Terakhir'],
                ['code' => 'pas_foto', 'name' => 'Pas Foto'],
            ],
        ],
    ];

    /** @var array<string, array{name: string, description: string, sort_order: int, requirements: list<array{code: string, name: string}>}> */
    private const OPTIONAL_PATHWAYS = [
        PpdbPathway::ZONASI => [
            'name' => 'Jalur Zonasi',
            'description' => 'Penerimaan berdasarkan domisili calon peserta didik.',
            'sort_order' => 40,
            'requirements' => [
                ['code' => 'kartu_keluarga', 'name' => 'Kartu Keluarga'],
                ['code' => 'bukti_domisili', 'name' => 'Bukti Domisili'],
                ['code' => 'akta_kelahiran', 'name' => 'Akta Kelahiran'],
            ],
        ],
        PpdbPathway::AFIRMASI => [
            'name' => 'Jalur Afirmasi',
            'description' => 'Penerimaan bagi calon peserta didik sesuai kriteria afirmasi sekolah.',
            'sort_order' => 50,
            'requirements' => [
                ['code' => 'kartu_keluarga', 'name' => 'Kartu Keluarga'],
                ['code' => 'bukti_afirmasi', 'name' => 'Bukti Afirmasi'],
                ['code' => 'akta_kelahiran', 'name' => 'Akta Kelahiran'],
            ],
        ],
    ];

    /** @param array<string, mixed> $attributes */
    public function create(int $schoolId, array $attributes): PpdbPeriod
    {
        return DB::transaction(function () use ($schoolId, $attributes): PpdbPeriod {
            $period = PpdbPeriod::create([
                ...$attributes,
                'school_id' => $schoolId,
            ]);

            foreach (self::DEFAULT_PATHWAYS as $code => $pathway) {
                $record = $period->pathways()->create([
                    'code' => $code,
                    'name' => $pathway['name'],
                    'description' => $pathway['description'],
                    'sort_order' => $pathway['sort_order'],
                    'registration_fee' => $period->default_registration_fee,
                    'is_active' => true,
                ]);

                foreach ($pathway['requirements'] as $index => $requirement) {
                    $record->requirements()->create([
                        'code' => $requirement['code'],
                        'name' => $requirement['name'],
                        'sort_order' => $index + 1,
                        'accepted_mimes' => 'pdf,jpg,jpeg,png',
                    ]);
                }
            }

            return $period->load('pathways.requirements');
        });
    }

    public function activatePathway(PpdbPathway $pathway, bool $active): void
    {
        $pathway->update(['is_active' => $active]);
    }

    public function addOptionalPathway(PpdbPeriod $period, string $code): PpdbPathway
    {
        if (! isset(self::OPTIONAL_PATHWAYS[$code])) {
            throw new \InvalidArgumentException('Jalur PPDB opsional tidak dikenal.');
        }

        return DB::transaction(function () use ($period, $code): PpdbPathway {
            $pathway = $period->pathways()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => self::OPTIONAL_PATHWAYS[$code]['name'],
                    'description' => self::OPTIONAL_PATHWAYS[$code]['description'],
                    'sort_order' => self::OPTIONAL_PATHWAYS[$code]['sort_order'],
                    'registration_fee' => $period->default_registration_fee,
                    'is_active' => true,
                ],
            );

            foreach (self::OPTIONAL_PATHWAYS[$code]['requirements'] as $index => $requirement) {
                $pathway->requirements()->firstOrCreate(
                    ['code' => $requirement['code']],
                    [
                        'name' => $requirement['name'],
                        'sort_order' => $index + 1,
                        'accepted_mimes' => 'pdf,jpg,jpeg,png',
                    ],
                );
            }

            return $pathway->load('requirements');
        });
    }

    /** @return array<string, array{name: string, description: string, sort_order: int, requirements: list<array{code: string, name: string}>}> */
    public function defaultPathways(): array
    {
        return self::DEFAULT_PATHWAYS;
    }
}
