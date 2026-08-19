<?php

namespace App\Services\AI;

use InvalidArgumentException;

final class RemedialEnrichmentGenerator
{
    public function __construct(
        private readonly GeminiClient $client = new GeminiClient()
    ) {
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generate(array $context): array
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
Anda adalah Pakar Asesmen Diagnostik, Pembelajaran Remedial & Program Pengayaan Kurikulum Merdeka Kemendikdasmen RI (berdasarkan Panduan Pembelajaran dan Asesmen / PPA 2024 & Kepka BSKAP No. 032/H/KR/2024).

Tugas Anda adalah menganalisis data asesmen murid/CBT dan menghasilkan:
1. Analisis Diagnostik & Akar Masalah (Root Cause Analysis & Misconception Identification).
2. Paket Lembar Kerja Remedial Terarah (Scaffolding & Re-teaching): Menjelaskan kembali konsep kunci secara sederhana, memberikan worked-example langkah demi langkah, dan 5 butir soal latihan perbaikan dengan hints dan pembahasan.
3. Paket Lembar Kerja Pengayaan (HOTS & Real-World Problem Solving): Wacana stimulus kontekstual dunia nyata, 3 butir soal analisis/evaluasi tingkat tinggi (HOTS C4-C6), ide mini-projek mandiri, dan rubrik penilaian kualitatif.

PRINSIP ANTI-HALUSINASI & KUALITAS PEDAGOGIS:
1. Semua konsep keilmuan, kunci jawaban, dan pembahasan soal wajib faktual, tepat, dan dapat dipertanggungjawabkan secara akademis.
2. Gunakan kata kerja operasional (KKO) Taksonomi Bloom Terkini yang terukur.
3. DILARANG menyisipkan karakter mentah Markdown seperti "**" (bintang tebal) atau "###" (heading) di dalam nilai string JSON.
4. Gunakan bahasa Indonesia yang santun, memotivasi, dan mudah dipahami oleh murid dan guru.
5. Format output WAJIB mengikuti JSON Schema terstruktur yang ditentukan tanpa teks pengantar di luar JSON.
INSTRUCTION;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildPrompt(array $context): string
    {
        $json = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Berdasarkan data asesmen, topik kurikulum, dan capaian siswa berikut, susun Paket Lengkap Lembar Kerja Remedial Terarah & Pengayaan HOTS:

{$json}

Pastikan paket remedial memberikan bimbingan konsep yang ramah murid dan soal perbaikan yang terstruktur dengan petunjuk bantu, serta paket pengayaan memberikan stimulus kasus nyata yang menantang nalar kritis. Gunakan bahasa Indonesia bersih tanpa simbol markdown mentah di dalam JSON.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => [
                'analysis_summary',
                'remedial_package',
                'enrichment_package',
            ],
            'properties' => [
                'analysis_summary' => [
                    'type' => 'object',
                    'required' => ['root_cause_analysis', 'misconceptions_identified', 'intervention_strategy'],
                    'properties' => [
                        'root_cause_analysis' => [
                            'type' => 'string',
                            'description' => 'Uraian analisis penyebab utama kendala belajar siswa pada materi ini',
                        ],
                        'misconceptions_identified' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar 2-3 miskonsepsi konsep yang sering dialami siswa',
                        ],
                        'intervention_strategy' => [
                            'type' => 'string',
                            'description' => 'Strategi intervensi pedagogis yang direkomendasikan untuk guru',
                        ],
                    ],
                ],
                'remedial_package' => [
                    'type' => 'object',
                    'required' => [
                        'title',
                        'target_competency',
                        'concept_recap',
                        'worked_example',
                        'practice_items',
                        'teacher_scaffolding_guide',
                    ],
                    'properties' => [
                        'title' => ['type' => 'string', 'description' => 'Judul Paket Remedial'],
                        'target_competency' => ['type' => 'string', 'description' => 'Kompetensi / Tujuan Pembelajaran yang diperbaiki'],
                        'concept_recap' => ['type' => 'string', 'description' => 'Rangkuman konsep kunci dengan bahasa sederhana'],
                        'worked_example' => [
                            'type' => 'object',
                            'required' => ['problem_statement', 'step_by_step_solution', 'key_takeaway'],
                            'properties' => [
                                'problem_statement' => ['type' => 'string', 'description' => 'Contoh soal model terurai'],
                                'step_by_step_solution' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                    'description' => 'Langkah-langkah penyelesaian logis nomor per nomor',
                                ],
                                'key_takeaway' => ['type' => 'string', 'description' => 'Poin kesimpulan yang wajib diingat siswa'],
                            ],
                        ],
                        'practice_items' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => ['item_number', 'question_text', 'type', 'hint', 'answer_key', 'explanation'],
                                'properties' => [
                                    'item_number' => ['type' => 'integer', 'description' => 'Nomor soal latihan'],
                                    'question_text' => ['type' => 'string', 'description' => 'Teks soal latihan perbaikan'],
                                    'type' => ['type' => 'string', 'enum' => ['pg', 'essay'], 'description' => 'Tipe soal (pg atau essay)'],
                                    'options' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                        'description' => 'Pilihan jawaban A-D/E jika soal PG',
                                    ],
                                    'hint' => ['type' => 'string', 'description' => 'Petunjuk bantu penuntun'],
                                    'answer_key' => ['type' => 'string', 'description' => 'Kunci jawaban yang benar'],
                                    'explanation' => ['type' => 'string', 'description' => 'Pembahasan konsep'],
                                ],
                            ],
                        ],
                        'teacher_scaffolding_guide' => ['type' => 'string', 'description' => 'Panduan pendampingan guru selama sesi remedial'],
                    ],
                ],
                'enrichment_package' => [
                    'type' => 'object',
                    'required' => [
                        'title',
                        'target_competency',
                        'real_world_case',
                        'hots_items',
                        'mini_project_prompt',
                        'scoring_rubric',
                    ],
                    'properties' => [
                        'title' => ['type' => 'string', 'description' => 'Judul Paket Pengayaan'],
                        'target_competency' => ['type' => 'string', 'description' => 'Kompetensi pengayaan tingkat tinggi'],
                        'real_world_case' => ['type' => 'string', 'description' => 'Wacana kasus kontekstual di dunia nyata'],
                        'hots_items' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => ['item_number', 'question_text', 'cognitive_level', 'expected_response_guide'],
                                'properties' => [
                                    'item_number' => ['type' => 'integer', 'description' => 'Nomor soal HOTS'],
                                    'question_text' => ['type' => 'string', 'description' => 'Pertanyaan analisis/evaluasi tingkat tinggi'],
                                    'cognitive_level' => ['type' => 'string', 'description' => 'Level kognitif (C4, C5, atau C6)'],
                                    'expected_response_guide' => ['type' => 'string', 'description' => 'Panduan respon jawaban yang diharapkan'],
                                ],
                            ],
                        ],
                        'mini_project_prompt' => [
                            'type' => 'object',
                            'required' => ['project_title', 'instructions', 'deliverable_product'],
                            'properties' => [
                                'project_title' => ['type' => 'string', 'description' => 'Judul mini projek mandiri'],
                                'instructions' => ['type' => 'string', 'description' => 'Instruksi pelaksanaan projek'],
                                'estimated_duration' => ['type' => 'string', 'description' => 'Estimasi durasi penyelesaian'],
                                'deliverable_product' => ['type' => 'string', 'description' => 'Produk akhir yang dikumpulkan siswa'],
                            ],
                        ],
                        'scoring_rubric' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => ['criteria', 'indicator', 'score_range'],
                                'properties' => [
                                    'criteria' => ['type' => 'string', 'description' => 'Kriteria penilaian pengayaan'],
                                    'indicator' => ['type' => 'string', 'description' => 'Indikator capaian'],
                                    'score_range' => ['type' => 'string', 'description' => 'Rentang skor'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertMinimumStructure(array $data): void
    {
        if (
            empty($data['analysis_summary'])
            || empty($data['remedial_package'])
            || empty($data['enrichment_package'])
        ) {
            throw new InvalidArgumentException('Respons AI tidak memenuhi struktur paket remedial dan pengayaan minimum.');
        }

        if (
            empty($data['remedial_package']['practice_items'])
            || empty($data['enrichment_package']['hots_items'])
        ) {
            throw new InvalidArgumentException('Daftar latihan remedial atau pengayaan tidak boleh kosong.');
        }
    }
}
