<?php

namespace App\Services\AI;

use InvalidArgumentException;

final class ClassroomDifferentiationAdvisor
{
    public function __construct(
        private readonly GeminiClient $client = new GeminiClient()
    ) {
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function advise(array $context): array
    {
        $systemInstruction = $this->buildSystemInstruction();
        $prompt = $this->buildPrompt($context);
        $schema = $this->buildJsonSchema();

        $result = $this->client->generateJson($systemInstruction, $prompt, $schema);

        $this->assertMinimumStructure($result);

        return $result;
    }

    private function buildSystemInstruction(): string
    {
        return <<<'INSTRUCTION'
Anda adalah Konsultan Ahli Pedagogi & Pembelajaran Terdiferensiasi Kurikulum Merdeka (Kepka BSKAP No. 032/H/KR/2024 & Panduan Pembelajaran dan Asesmen / PPA 2024 Kemendikdasmen RI).

Tugas Anda adalah menganalisis data profil empiris kelas nyata (distribusi nilai tugas/UTS/UAS, capaian TP, tingkat kehadiran, sarana kelas, dan kebutuhan belajar khusus) dan menghasilkan rekomendasi diferensiasi pembelajaran komprehensif, strategis, dan aplikatif bagi guru pengampu.

Prinsip Diferensiasi Kurikulum Merdeka yang Wajib Diterapkan:
1. Diferensiasi Konten: Menyesuaikan materi belajar (konkret ke abstrak, penggunaan media multisensori, materi penunjang vs materi pengayaan).
2. Diferensiasi Proses: Menyesuaikan skenario KBM (scaffolding bertahap oleh guru, tutor sebaya, stasiun belajar mandiri, diskusi kelompok berjenjang).
3. Diferensiasi Produk: Menyesuaikan variasi unjuk kerja/artefak (poster visual, infografis, video/podcast, esai analitis, simulasi proyek).
4. Pengelompokan Kesiapan Belajar Fleksibel (Tiered Groups):
   - Kelompok 1: Perlu Bimbingan (Scaffolding / Fondasi Dasar)
   - Kelompok 2: Reguler (Cakap / Mandiri)
   - Kelompok 3: Pengayaan (Mahir / Tantangan HOTS)

PANDUAN ANTI-HALUSINASI & FORMAT HUMAN-FRIENDLY:
1. Rekomendasi wajib didasarkan secara empiris pada data kelas nyata yang diberikan (tidak mengarang statistik fiktif).
2. DILARANG menyisipkan karakter mentah Markdown seperti "**" (bintang tebal) atau "###" (heading) di dalam nilai string JSON.
3. Gunakan bahasa Indonesia profesional, solutif, dan mudah dieksekusi oleh guru di kelas.
4. Format output WAJIB mengikuti JSON Schema terstruktur yang ditentukan tanpa teks pengantar di luar JSON.
INSTRUCTION;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildPrompt(array $context): string
    {
        $json = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Analisis data kelas berikut dan susun rekomendasi diferensiasi pengajaran serta strategi pengelompokan kesiapan belajar:

=== DATA PROFIL EMPIRIS KELAS ===
{$json}
=== AKHIR DATA ===

Susun analisis profil kesiapan kelas, rekomendasi 3 dimensi diferensiasi (konten, proses, produk), pembagian 3 tingkat kelompok siswa beserta langkah intervensinya, dan rencana tindakan pedagogis guru yang terstruktur dan mudah dieksekusi di kelas. Pastikan teks bersih tanpa simbol markdown mentah di dalam JSON.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'classroom_summary' => [
                    'type' => 'string',
                    'description' => 'Ringkasan analisis kesiapan, dinamika, dan tantangan utama kelas ini.',
                ],
                'readiness_level_distribution' => [
                    'type' => 'object',
                    'properties' => [
                        'scaffolding_percentage' => ['type' => 'string', 'description' => 'Estimasi % siswa perlu bimbingan dasar'],
                        'regular_percentage' => ['type' => 'string', 'description' => 'Estimasi % siswa level reguler/cakap'],
                        'advanced_percentage' => ['type' => 'string', 'description' => 'Estimasi % siswa level mahir/pengayaan'],
                    ],
                    'required' => ['scaffolding_percentage', 'regular_percentage', 'advanced_percentage'],
                ],
                'recommended_learning_models' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Model atau metode pembelajaran yang paling cocok untuk dinamika kelas ini.',
                ],
                'differentiation_content' => [
                    'type' => 'object',
                    'properties' => [
                        'strategy' => ['type' => 'string', 'description' => 'Strategi penyesuaian materi dan sumber belajar'],
                        'for_scaffolding' => ['type' => 'string', 'description' => 'Materi fondasi / penyederhanaan / visual'],
                        'for_regular' => ['type' => 'string', 'description' => 'Materi standar kurikulum dan latihan mandiri'],
                        'for_advanced' => ['type' => 'string', 'description' => 'Materi pengayaan / studi kasus mendalam / riset'],
                    ],
                    'required' => ['strategy', 'for_scaffolding', 'for_regular', 'for_advanced'],
                ],
                'differentiation_process' => [
                    'type' => 'object',
                    'properties' => [
                        'strategy' => ['type' => 'string', 'description' => 'Strategi penataan interaksi dan alur KBM'],
                        'for_scaffolding' => ['type' => 'string', 'description' => 'Langkah bimbingan intensif / scaffolding'],
                        'for_regular' => ['type' => 'string', 'description' => 'Langkah kerja mandiri dan kolaborasi sebaya'],
                        'for_advanced' => ['type' => 'string', 'description' => 'Langkah investigasi terbuka / problem solving mandiri'],
                    ],
                    'required' => ['strategy', 'for_scaffolding', 'for_regular', 'for_advanced'],
                ],
                'differentiation_product' => [
                    'type' => 'object',
                    'properties' => [
                        'strategy' => ['type' => 'string', 'description' => 'Strategi variasi unjuk kerja / asesmen akhir'],
                        'options' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'target_group' => ['type' => 'string', 'description' => 'Kelompok sasaran'],
                                    'product_type' => ['type' => 'string', 'description' => 'Bentuk produk / artefak belajar'],
                                    'description' => ['type' => 'string', 'description' => 'Deskripsi unjuk kerja yang diharapkan'],
                                ],
                                'required' => ['target_group', 'product_type', 'description'],
                            ],
                        ],
                    ],
                    'required' => ['strategy', 'options'],
                ],
                'student_grouping' => [
                    'type' => 'object',
                    'properties' => [
                        'scaffolding_group' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => ['type' => 'string', 'description' => 'Nama / label kelompok'],
                                'characteristics' => ['type' => 'string', 'description' => 'Karakteristik & profil murid'],
                                'teacher_intervention' => ['type' => 'string', 'description' => 'Bentuk intervensi guru'],
                                'sample_tasks' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Contoh tugas kelompok',
                                ],
                            ],
                            'required' => ['title', 'characteristics', 'teacher_intervention', 'sample_tasks'],
                        ],
                        'regular_group' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => ['type' => 'string', 'description' => 'Nama / label kelompok'],
                                'characteristics' => ['type' => 'string', 'description' => 'Karakteristik & profil murid'],
                                'teacher_intervention' => ['type' => 'string', 'description' => 'Bentuk intervensi guru'],
                                'sample_tasks' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Contoh tugas kelompok',
                                ],
                            ],
                            'required' => ['title', 'characteristics', 'teacher_intervention', 'sample_tasks'],
                        ],
                        'advanced_group' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => ['type' => 'string', 'description' => 'Nama / label kelompok'],
                                'characteristics' => ['type' => 'string', 'description' => 'Karakteristik & profil murid'],
                                'teacher_intervention' => ['type' => 'string', 'description' => 'Bentuk intervensi guru'],
                                'sample_tasks' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Contoh tugas kelompok',
                                ],
                            ],
                            'required' => ['title', 'characteristics', 'teacher_intervention', 'sample_tasks'],
                        ],
                    ],
                    'required' => ['scaffolding_group', 'regular_group', 'advanced_group'],
                ],
                'pedagogical_action_plan' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'step_number' => ['type' => 'integer', 'description' => 'Nomor langkah'],
                            'action_title' => ['type' => 'string', 'description' => 'Nama tindakan taktis'],
                            'teacher_action' => ['type' => 'string', 'description' => 'Langkah konkret yang dilakukan guru'],
                            'expected_outcome' => ['type' => 'string', 'description' => 'Hasil atau dampak yang diharapkan'],
                        ],
                        'required' => ['step_number', 'action_title', 'teacher_action', 'expected_outcome'],
                    ],
                    'description' => 'Langkah tindakan taktis guru untuk perbaikan proses pembelajaran di kelas.',
                ],
                'assessment_tips' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Tips asesmen formatif berkelanjutan untuk memantau perkembangan diferensiasi.',
                ],
            ],
            'required' => [
                'classroom_summary',
                'differentiation_content',
                'differentiation_process',
                'differentiation_product',
                'student_grouping',
                'pedagogical_action_plan',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertMinimumStructure(array $data): void
    {
        $requiredKeys = [
            'classroom_summary',
            'differentiation_content',
            'differentiation_process',
            'differentiation_product',
            'student_grouping',
            'pedagogical_action_plan',
        ];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Respons Gemini tidak memuat kunci wajib: {$key}");
            }
        }
    }
}
