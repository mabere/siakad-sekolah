<?php

namespace App\Services\AI;

use JsonException;
use RuntimeException;

final class LearningDraftGenerator
{
    public function __construct(
        private readonly GeminiClient $client,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generate(array $context): array
    {
        try {
            $contextJson = json_encode(
                $context,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new RuntimeException('Konteks pembelajaran tidak dapat diproses.');
        }

        $docType = (string) ($context['document_type'] ?? 'modul_ajar');

        $prompt = match ($docType) {
            'atp' => $this->buildAtpPrompt($contextJson),
            'prota_prosem' => $this->buildProtaProsemPrompt($contextJson),
            'bahan_ajar', 'materi_ajar' => $this->buildBahanAjarPrompt($contextJson),
            'lkpd_bertingkat', 'lkpd' => $this->buildLkpdPrompt($contextJson),
            'modul_p5' => $this->buildModulP5Prompt($contextJson),
            'asesmen_kktp', 'asesmen' => $this->buildAsesmenKktpPrompt($contextJson),
            default => $this->buildModulAjarPrompt($contextJson),
        };

        $schema = match ($docType) {
            'atp' => self::atpSchema(),
            'prota_prosem' => self::protaProsemSchema(),
            'bahan_ajar', 'materi_ajar' => self::bahanAjarSchema(),
            'lkpd_bertingkat', 'lkpd' => self::lkpdSchema(),
            'modul_p5' => self::modulP5Schema(),
            'asesmen_kktp', 'asesmen' => self::asesmenKktpSchema(),
            default => self::schema(),
        };

        $systemInstruction = $this->buildSystemInstruction($docType);

        $draft = $this->client->generateJson(
            systemInstruction: $systemInstruction,
            prompt: $prompt,
            schema: $schema,
        );

        $this->assertMinimumStructure($draft, $docType);

        // Pastikan document_type disematkan ke hasil draft
        $draft['document_type'] = $docType;

        return $draft;
    }

    private function buildSystemInstruction(string $docType): string
    {
        $basePedagogicalRole = match ($docType) {
            'atp' => 'Anda adalah Pakar Penyusun Alur Tujuan Pembelajaran (ATP) dan Kurikulum Nasional Kemendikdasmen RI yang mampu menurunkan Capaian Pembelajaran (CP) menjadi alur TP yang logis, berjenjang, dan aplikatif.',
            'prota_prosem' => 'Anda adalah Perancang Kalender Akademik dan Program Semester (Prota & Prosem) Kurikulum Merdeka yang akurat dalam mendistribusikan alokasi jam pelajaran (JP) dan pekan efektif kalender pendidikan.',
            'bahan_ajar', 'materi_ajar' => 'Anda adalah Penulis Buku Teks dan Bahan Ajar Konseptual Kurikulum Merdeka yang menyajikan materi esensial secara mendalam, faktual, analogis, dan kontekstual bagi peserta didik.',
            'lkpd_bertingkat', 'lkpd' => 'Anda adalah Spesialis Pembelajaran Berdiferensiasi yang mahir menyusun Lembar Kerja Peserta Didik (LKPD) berjenjang (Tiered Worksheet: Scaffolding, Reguler, dan Pengayaan HOTS).',
            'modul_p5' => 'Anda adalah Fasilitator Utama Projek Penguatan Profil Pelajar Pancasila (P5) Kemendikdasmen RI yang merancang modul projek karakter sesuai 8 tema resmi secara mendalam dan bermakna.',
            'asesmen_kktp', 'asesmen' => 'Anda adalah Pakar Evaluasi Pembelajaran & Asesmen Kurikulum Merdeka yang merancang instrumen asesmen diagnostik, formatif, dan sumatif AKM HOTS beserta rubrik Kriteria Ketercapaian Tujuan Pembelajaran (KKTP).',
            default => 'Anda adalah Pakar Perancang Kurikulum dan Asisten Perangkat Ajar Kurikulum Merdeka Indonesia yang cermat, inovatif, dan berorientasi pada kemudahan guru serta keberhasilan belajar murid.',
        };

        return <<<INSTRUCTION
{$basePedagogicalRole}

PEDOMAN REGULASI & STANDAR KURIKULUM RESMI:
1. Mengacu pada Keputusan Kepala BSKAP Kemendikbudristek/Kemendikdasmen No. 032/H/KR/2024, Permendikbudristek No. 12 Tahun 2024, dan Panduan Pembelajaran dan Asesmen (PPA 2024).
2. Gunakan fase perkembangan resmi: Fase Fondasi (PAUD), Fase A (Kelas 1-2), Fase B (Kelas 3-4), Fase C (Kelas 5-6), Fase D (Kelas 7-9 SMP), Fase E (Kelas 10 SMA/SMK), Fase F (Kelas 11-12 SMA/SMK).
3. Gunakan Kata Kerja Operasional (KKO) Taksonomi Bloom Terkini (C1-Mengingat, C2-Memahami, C3-Menerapkan, C4-Menganalisis, C5-Mengevaluasi, C6-Mencipta) yang terukur dan dapat diamati (observable & measurable).
4. Gunakan 6 Dimensi Profil Pelajar Pancasila resmi: (1) Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia; (2) Berkebinekaan Global; (3) Bergotong Royong; (4) Mandiri; (5) Bernalar Kritis; (6) Kreatif.

PANDUAN ANTI-HALUSINASI & INTEGRITAS DATA:
1. Jangan membuat istilah kurikulum fiktif atau mencampurkan konsep usang (dilarang menggunakan format KI/KD K13 pada Kurikulum Merdeka).
2. Jangan menggunakan jawaban placeholder generik seperti "lakukan aktivitas sesuai buku", melainkan susun skenario KBM, butir soal, wacana stimulus, dan rubrik yang konkret dan substantif.
3. Seluruh fakta ilmiah, rumus matematika, peristiwa sejarah, dan konsep keilmuan wajib akurat dan faktual.

PANDUAN FORMAT HUMAN-FRIENDLY:
1. Output teks HARUS bersih, profesional, mengalir, dan siap dibaca langsung oleh guru dan siswa.
2. DILARANG menyisipkan karakter mentah Markdown seperti "**" (bintang tebal), "###" (tanda pagar heading), atau backtick code block di dalam nilai string JSON.
3. Gunakan kalimat bahasa Indonesia yang rapi, kapitalisasi yang tepat, dan tanda baca standar.
4. Kembalikan respons dalam format JSON murni sesuai schema yang ditentukan tanpa teks pengantar di luar JSON.
INSTRUCTION;
    }

    private function buildModulAjarPrompt(string $contextJson): string
    {
        return <<<'PROMPT'
Susun draf lengkap Modul Ajar (RPP+ Berdiferensiasi) berstandar resmi Kurikulum Merdeka (Kepka BSKAP No. 032/H/KR/2024 & PPA 2024) berdasarkan konteks JSON berikut.

Struktur & Ketentuan Modul Ajar:
1. Informasi Umum & Identitas: Mata pelajaran, fase/kelas, alokasi waktu, dan model pembelajaran yang relevan (Problem-Based Learning, Project-Based Learning, Discovery Learning, atau Pedagogi Genre).
2. Dimensi Profil Pelajar Pancasila (P5): Pilih 2-3 dimensi yang paling relevan dengan aktivitas pembelajaran.
3. Pemahaman Bermakna: Rumuskan esensi manfaat konsep materi dalam kehidupan nyata peserta didik.
4. Pertanyaan Pemantik: Buat 2-3 pertanyaan terbuka yang merangsang rasa ingin tahu dan nalar kritis siswa sebelum memulai materi inti.
5. Tujuan Pembelajaran (TP): Rumuskan 2-4 butir tujuan pembelajaran terukur berbasis KKO Taksonomi Bloom.
6. Skenario KBM 3 Fase:
   - Pendahuluan: Orientasi, apersepsi kontekstual, motivasi, dan penyampaian tujuan.
   - Kegiatan Inti: Sintaks model pembelajaran dengan penerapan strategi diferensiasi (konten/proses/produk), peran aktif guru sebagai fasilitator, dan peran murid yang kolaboratif.
   - Penutup: Refleksi bersama, simpulan konsep kunci, dan tindak lanjut/penugasan bermakna.
7. Lembar Kerja Peserta Didik (LKPD): Sediakan judul, instruksi jelas, dan tugas-tugas konkret aplikatif.
8. Asesmen & Rubrik KKTP: Asesmen diagnostik awal, asesmen formatif proses, asesmen sumatif lingkup materi, dan tabel rubrik penilaian dengan kriteria, indikator, dan pedoman skor.
9. Format Teks: Gunakan bahasa Indonesia baku, profesional, dan bebas dari karakter markdown mentah (** atau ###) di dalam string JSON.

Konteks Pembelajaran:
PROMPT
            .$contextJson;
    }

    private function buildAtpPrompt(string $contextJson): string
    {
        return <<<'PROMPT'
Susun dokumen Alur Tujuan Pembelajaran (ATP / Silabus Tahunan) resmi Kurikulum Merdeka berdasarkan konteks JSON berikut.

Struktur & Ketentuan ATP:
1. Capaian Pembelajaran (CP) Umum: Uraikan CP resmi fase terkait secara utuh dan terstruktur.
2. CP per Elemen: Uraikan elemen mata pelajaran (misal: Menyimak, Membaca-Memirsa, Berbicara-Mempresentasikan, Menulis, dsb.) beserta rumusan capaian masing-masing.
3. Alur Tujuan Pembelajaran (ATP Flow): Susun tahapan TP secara berurutan dan logis dari konsep prasyarat (C1-C2) hingga penerapan dan kreasi (C3-C6).
4. Setiap butir alur memuat: Nomor urut, Bab materi, Pokok Topik, Rumusan TP, Indikator Ketercapaian, Alokasi JP, Dimensi P5 terkait, dan Teknik Asesmen yang disarankan.
5. Glosarium & Referensi: Glosarium istilah penting dan daftar buku teks/sumber belajar resmi Kemendikdasmen.
6. Format Teks: Bersih, profesional, bebas karakter markdown mentah (** atau ###) di dalam string JSON.

Konteks Pembelajaran:
PROMPT
            .$contextJson;
    }

    private function buildProtaProsemPrompt(string $contextJson): string
    {
        return <<<'PROMPT'
Susun dokumen Program Tahunan (Prota) dan Program Semester (Prosem) Kurikulum Merdeka berdasarkan konteks JSON berikut.

Struktur & Ketentuan Prota & Prosem:
1. Distribusi Prota: Hitung alokasi Jam Pelajaran (JP) per bab/tujuan pembelajaran untuk Semester Ganjil dan Semester Genap.
2. Perhitungan Jam Efektif: Sesuaikan dengan jumlah pekan efektif semester ganjil dan genap yang tercantum pada data konteks (misal: 18 pekan ganjil, 16 pekan genap).
3. Matriks Prosem Ganjil (Juli - Desember): Distribusikan bab materi, asesmen formatif, sumatif tengah semester, sumatif akhir semester, dan cadangan JP ke pekan 1 s.d. 4/5 per bulan.
4. Matriks Prosem Genap (Januari - Juni): Distribusikan bab materi lanjutan, asesmen sumatif, dan persiapan kenaikan kelas.
5. Format Teks: Rapi, matematis akurat, bebas karakter markdown mentah (** atau ###) di dalam string JSON.

Konteks Pembelajaran:
PROMPT
            .$contextJson;
    }

    private function buildBahanAjarPrompt(string $contextJson): string
    {
        return <<<'PROMPT'
Susun Bahan Ajar & Ringkasan Materi Konseptual Interaktif Kurikulum Merdeka berdasarkan konteks JSON berikut.

Struktur & Ketentuan Bahan Ajar:
1. Ringkasan Konsep Inti: Jelaskan peta konsep dan esensi materi secara komprehensif, padat, dan mudah dipahami.
2. Bagian Materi Utama (Key Sections): Bagi topik ke dalam 3-5 sub-topik terstruktur yang memuat penjelasan mendalam, contoh konkret di kehidupan nyata, dan kesimpulan kunci (key takeaway).
3. Analogi / Skema Konseptual: Sajikan analogi atau metafora cerdas yang mempermudah daya ingat siswa.
4. Studi Kasus Kontekstual: Sediakan 1 wacana studi kasus nyata beserta 2-3 pertanyaan pemantik diskusi kelompok.
5. Ringkasan Infografis & Glosarium: Poin-poin visual siap baca dan kamus istilah materi.
6. Format Teks: Bahasa Indonesia yang komunikatif, menarik, dan bebas dari karakter markdown mentah (** atau ###) di dalam string JSON.

Konteks Pembelajaran:
PROMPT
            .$contextJson;
    }

    private function buildLkpdPrompt(string $contextJson): string
    {
        return <<<'PROMPT'
Susun Lembar Kerja Peserta Didik (LKPD) Berdiferensiasi 3 Tingkat Kesiapan Belajar (Tiered Worksheet) Kurikulum Merdeka berdasarkan konteks JSON berikut.

Struktur & Ketentuan LKPD:
1. Petunjuk Umum: Panduan pengerjaan yang jelas bagi seluruh siswa.
2. Level 1 - Scaffolding (Perlu Bimbingan Dasar): Panduan langkah demi langkah, petunjuk bantu (hints/clues), dan soal penuntun pemahaman konsep dasar.
3. Level 2 - Reguler (Cakap / Mandiri): Soal kasus standar dan tugas eksplorasi mandiri sesuai capaian fase.
4. Level 3 - Pengayaan (Mahir / HOTS C4-C6): Studi kasus kompleks, analisis pemecahan masalah nyata, dan ide mini riset/projek kreatif.
5. Pertanyaan Refleksi Siswa: 2-3 butir refleksi kesadaran belajar diri.
6. Rubrik Penilaian & Kunci Jawaban: Rubrik penskoran per level dan panduan jawaban guru.
7. Format Teks: Bersih, human-friendly, bebas karakter markdown mentah (** atau ###) di dalam string JSON.

Konteks Pembelajaran:
PROMPT
            .$contextJson;
    }

    private function buildModulP5Prompt(string $contextJson): string
    {
        return <<<'PROMPT'
Susun Modul Projek Penguatan Profil Pelajar Pancasila (P5) resmi Kemendikdasmen RI berdasarkan konteks JSON berikut.

Struktur & Ketentuan Modul P5:
1. Tema & Topik Projek: Pilih 1 dari 8 tema resmi P5 (Kearifan Lokal, Gaya Hidup Berkelanjutan, Rekayasa & Teknologi, Kewirausahaan, Suara Demokrasi, Bhinneka Tunggal Ika, Bangunlah Jiwa dan Raganya, Kebekerjaan).
2. Latar Belakang & Urgensi: Jelaskan konteks masalah nyata di lingkungan sekolah/masyarakat yang melandasi projek ini.
3. Dimensi, Elemen, dan Subelemen: Tentukan target capaian di akhir fase untuk setiap dimensi P5 yang disasar.
4. Alur 4 Tahap Projek:
   - Tahap 1: Pengenalan (Membangun kesadaran dan eksplorasi konsep tema).
   - Tahap 2: Kontekstualisasi (Mengidentifikasi masalah spesifik di lingkungan sekitar).
   - Tahap 3: Aksi Nyata (Merancang, menguji, dan memamerkan solusi/karya projek).
   - Tahap 4: Refleksi & Tindak Lanjut (Mengevaluasi proses, dampak, dan keberlanjutan projek).
5. Rubrik Asesmen Projek 4 Kategori: Mulai Berkembang (MB), Sedang Berkembang (SB), Berkembang Sesuai Harapan (BSH), dan Sangat Berkembang (SAB).
6. Format Teks: Rapi, berbobot karakter, bebas karakter markdown mentah (** atau ###) di dalam string JSON.

Konteks Pembelajaran:
PROMPT
            .$contextJson;
    }

    private function buildAsesmenKktpPrompt(string $contextJson): string
    {
        return <<<'PROMPT'
Susun Dokumen Kisi-Kisi, Instrumen Asesmen Lengkap & Rubrik Kriteria Ketercapaian Tujuan Pembelajaran (KKTP) Kurikulum Merdeka berdasarkan konteks JSON berikut.

Struktur & Ketentuan Asesmen & KKTP:
1. Asesmen Diagnostik Awal: 3-4 butir pertanyaan kognitif (mengecek prasyarat materi) dan 2 butir non-kognitif (gaya belajar & kesiapan emosi).
2. Asesmen Formatif (Proses): Checklist observasi unjuk kerja dengan indikator perilaku teramati, serta 2-3 prompt tiket keluar (Exit Ticket).
3. Asesmen Sumatif (AKM / HOTS):
   - Kisi-kisi soal terstruktur (Indikator, Bentuk Soal, Level Kognitif C1-C6, Skor Maksimal).
   - 3-5 butir soal kontekstual (Pilihan Ganda & Uraian Analitis) lengkap dengan wacana stimulus, opsi, kunci jawaban, dan pembahasan.
4. Rubrik KKTP 4 Interval: Matriks deskripsi interval pencapaian (Perlu Bimbingan [0-65], Cukup [66-75], Baik [76-85], Sangat Baik [86-100]).
5. Panduan Remedial & Pengayaan: Rekomendasi tindak lanjut intervensi bagi peserta didik yang belum dan telah tuntas KKTP.
6. Format Teks: Bersih, profesional, bebas karakter markdown mentah (** atau ###) di dalam string JSON.

Konteks Pembelajaran:
PROMPT
            .$contextJson;
    }

    /**
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Judul lengkap Modul Ajar, contoh: Modul Ajar Biologi Fase E: Struktur Virus dan Peranannya',
                ],
                'summary' => [
                    'type' => 'string',
                    'description' => 'Ringkasan esensi modul ajar dan pendekatan pembelajaran yang digunakan dalam 2-3 kalimat bersih.',
                ],
                'p5_dimensions' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Daftar 2-3 Dimensi Profil Pelajar Pancasila yang dilatihkan (misal: Bernalar Kritis, Gotong Royong, Mandiri).',
                ],
                'learning_model' => [
                    'type' => 'string',
                    'description' => 'Model atau metode pembelajaran yang digunakan (misal: Problem-Based Learning, Project-Based Learning, Discovery Learning).',
                ],
                'meaningful_understanding' => [
                    'type' => 'string',
                    'description' => 'Uraian pemahaman bermakna mengenai esensi manfaat materi dalam kehidupan nyata peserta didik.',
                ],
                'inquiry_questions' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => '2-3 pertanyaan pemantik terbuka yang merangsang rasa ingin tahu dan berpikir kritis.',
                ],
                'learning_objectives' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => '2-4 butir tujuan pembelajaran terukur berbasis KKO Taksonomi Bloom (misal: Peserta didik mampu menganalisis...).',
                ],
                'activities' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'stage' => [
                                'type' => 'string',
                                'description' => 'Tahapan KBM (Kegiatan Pendahuluan, Kegiatan Inti - Diferensiasi, atau Kegiatan Penutup).',
                            ],
                            'duration_minutes' => [
                                'type' => 'integer',
                                'minimum' => 0,
                                'description' => 'Durasi waktu pelaksanaan dalam menit.',
                            ],
                            'activity' => [
                                'type' => 'string',
                                'description' => 'Deskripsi konkret aktivitas pembelajaran yang dilakukan di kelas.',
                            ],
                            'teacher_role' => [
                                'type' => 'string',
                                'description' => 'Peran atau tindakan guru sebagai fasilitator.',
                            ],
                            'student_role' => [
                                'type' => 'string',
                                'description' => 'Peran aktif murid dalam kegiatan (misal: observasi, diskusi, presentasi).',
                            ],
                        ],
                        'required' => [
                            'stage',
                            'duration_minutes',
                            'activity',
                            'teacher_role',
                            'student_role',
                        ],
                    ],
                ],
                'student_worksheet' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string', 'description' => 'Judul LKPD'],
                        'instructions' => ['type' => 'string', 'description' => 'Petunjuk pengerjaan LKPD bagi murid'],
                        'tasks' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar 3-5 butir instruksi tugas konkret pada lembar kerja.',
                        ],
                    ],
                    'required' => ['title', 'instructions', 'tasks'],
                ],
                'assessment' => [
                    'type' => 'object',
                    'properties' => [
                        'diagnostic' => ['type' => 'string', 'description' => 'Metode dan instrumen asesmen diagnostik awal'],
                        'formative' => ['type' => 'string', 'description' => 'Metode dan instrumen asesmen formatif proses'],
                        'summative' => ['type' => 'string', 'description' => 'Metode dan instrumen asesmen sumatif akhir'],
                    ],
                    'required' => ['diagnostic', 'formative', 'summative'],
                ],
                'assessment_rubric' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'criteria' => ['type' => 'string', 'description' => 'Kriteria aspek yang dinilai'],
                            'indicator' => ['type' => 'string', 'description' => 'Indikator perilaku atau kompetensi teramati'],
                            'scoring_guide' => ['type' => 'string', 'description' => 'Pedoman skor dan kriteria capaian'],
                        ],
                        'required' => ['criteria', 'indicator', 'scoring_guide'],
                    ],
                ],
                'differentiation' => [
                    'type' => 'string',
                    'description' => 'Strategi penerapan diferensiasi konten, proses, dan produk sesuai profil murid.',
                ],
                'resources' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Alat, bahan, dan media belajar yang dibutuhkan di kelas.',
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Catatan penting atau hal yang perlu dikonfirmasi guru pengampu.',
                ],
                'references' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Daftar buku teks resmi dan sumber rujukan Kemendikdasmen.',
                ],
            ],
            'required' => [
                'title',
                'summary',
                'p5_dimensions',
                'learning_model',
                'meaningful_understanding',
                'inquiry_questions',
                'learning_objectives',
                'activities',
                'student_worksheet',
                'assessment',
                'assessment_rubric',
                'differentiation',
                'resources',
                'warnings',
                'references',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function atpSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Judul Dokumen ATP'],
                'summary' => ['type' => 'string', 'description' => 'Rasional dan deskripsi umum alur capaian pembelajaran'],
                'cp_general' => ['type' => 'string', 'description' => 'Rumusan Capaian Pembelajaran (CP) Umum Fase'],
                'cp_elements' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'element_name' => ['type' => 'string', 'description' => 'Nama Elemen Mapel'],
                            'cp_statement' => ['type' => 'string', 'description' => 'Deskripsi Capaian per Elemen'],
                        ],
                        'required' => ['element_name', 'cp_statement'],
                    ],
                ],
                'atp_flow' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'sequence_number' => ['type' => 'string', 'description' => 'Nomor urut alur (misal: 10.1, 10.2)'],
                            'chapter' => ['type' => 'string', 'description' => 'Bab / Unit Pembelajaran'],
                            'topic' => ['type' => 'string', 'description' => 'Topik Materi'],
                            'learning_objectives' => ['type' => 'string', 'description' => 'Rumusan Tujuan Pembelajaran'],
                            'indicators' => ['type' => 'string', 'description' => 'Indikator Ketercapaian Kompetensi'],
                            'suggested_duration_jp' => ['type' => 'string', 'description' => 'Alokasi Jam Pelajaran (misal: 6 JP)'],
                            'p5_dimensions' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Dimensi P5 terkait',
                            ],
                            'assessment_technique' => ['type' => 'string', 'description' => 'Teknik Asesmen yang disarankan'],
                        ],
                        'required' => ['sequence_number', 'chapter', 'topic', 'learning_objectives', 'indicators', 'suggested_duration_jp'],
                    ],
                ],
                'total_duration_jp' => ['type' => 'string', 'description' => 'Total Jam Pelajaran dalam 1 Tahun Ajaran'],
                'glossary' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'term' => ['type' => 'string', 'description' => 'Istilah'],
                            'definition' => ['type' => 'string', 'description' => 'Definisi istilah'],
                        ],
                        'required' => ['term', 'definition'],
                    ],
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'references' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['title', 'summary', 'cp_general', 'cp_elements', 'atp_flow', 'total_duration_jp', 'glossary', 'warnings', 'references'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function protaProsemSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Judul Dokumen Prota & Prosem'],
                'summary' => ['type' => 'string', 'description' => 'Ringkasan rencana distribusi jam pelajaran'],
                'total_effective_weeks_odd' => ['type' => 'integer', 'description' => 'Pekan Efektif Semester Ganjil'],
                'total_effective_weeks_even' => ['type' => 'integer', 'description' => 'Pekan Efektif Semester Genap'],
                'total_jp_year' => ['type' => 'string', 'description' => 'Total JP 1 Tahun'],
                'prota_distribution' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'chapter_number' => ['type' => 'string', 'description' => 'Nomor Bab'],
                            'chapter_title' => ['type' => 'string', 'description' => 'Judul Bab'],
                            'learning_objectives' => ['type' => 'string', 'description' => 'Tujuan Pembelajaran'],
                            'semester' => ['type' => 'string', 'description' => 'Semester (Ganjil / Genap)'],
                            'allocated_jp' => ['type' => 'string', 'description' => 'Alokasi JP'],
                        ],
                        'required' => ['chapter_number', 'chapter_title', 'learning_objectives', 'semester', 'allocated_jp'],
                    ],
                ],
                'prosem_odd' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'month_name' => ['type' => 'string', 'description' => 'Bulan (Juli - Desember)'],
                            'week_number' => ['type' => 'integer', 'description' => 'Pekan ke- (1-5)'],
                            'chapter_title' => ['type' => 'string', 'description' => 'Judul Bab / Aktivitas'],
                            'topic' => ['type' => 'string', 'description' => 'Topik Pembelajaran'],
                            'allocated_jp' => ['type' => 'string', 'description' => 'Alokasi JP'],
                            'activity_type' => ['type' => 'string', 'description' => 'Jenis Kegiatan (KBM, Formatif, STS, SAS, Cadangan)'],
                        ],
                        'required' => ['month_name', 'week_number', 'chapter_title', 'topic', 'allocated_jp', 'activity_type'],
                    ],
                ],
                'prosem_even' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'month_name' => ['type' => 'string', 'description' => 'Bulan (Januari - Juni)'],
                            'week_number' => ['type' => 'integer', 'description' => 'Pekan ke- (1-5)'],
                            'chapter_title' => ['type' => 'string', 'description' => 'Judul Bab / Aktivitas'],
                            'topic' => ['type' => 'string', 'description' => 'Topik Pembelajaran'],
                            'allocated_jp' => ['type' => 'string', 'description' => 'Alokasi JP'],
                            'activity_type' => ['type' => 'string', 'description' => 'Jenis Kegiatan (KBM, Formatif, STS, SAT, Cadangan)'],
                        ],
                        'required' => ['month_name', 'week_number', 'chapter_title', 'topic', 'allocated_jp', 'activity_type'],
                    ],
                ],
                'reserve_jp' => ['type' => 'string', 'description' => 'Cadangan JP'],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'references' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['title', 'summary', 'prota_distribution', 'prosem_odd', 'prosem_even', 'warnings', 'references'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function bahanAjarSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Judul Bahan Ajar'],
                'summary' => ['type' => 'string', 'description' => 'Ringkasan singkat materi'],
                'concept_summary' => ['type' => 'string', 'description' => 'Peta konsep dan penjelasan materi inti secara komprehensif'],
                'key_sections' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'subtitle' => ['type' => 'string', 'description' => 'Subtopik Materi'],
                            'content' => ['type' => 'string', 'description' => 'Uraian materi mendalam dan mudah dipahami'],
                            'key_takeaway' => ['type' => 'string', 'description' => 'Poin kesimpulan kunci'],
                            'practical_example' => ['type' => 'string', 'description' => 'Contoh nyata kontekstual dalam kehidupan'],
                        ],
                        'required' => ['subtitle', 'content', 'key_takeaway'],
                    ],
                ],
                'conceptual_analogy' => ['type' => 'string', 'description' => 'Analogi atau skema konsep yang mempermudah ingatan murid'],
                'case_study' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string', 'description' => 'Judul Kasus Nyata'],
                        'scenario' => ['type' => 'string', 'description' => 'Wacana skenario studi kasus kontekstual'],
                        'discussion_questions' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Pertanyaan pemantik diskusi kelas',
                        ],
                    ],
                    'required' => ['title', 'scenario', 'discussion_questions'],
                ],
                'infographic_summary' => ['type' => 'string', 'description' => 'Ringkasan poin-poin infografis visual'],
                'glossary' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'term' => ['type' => 'string', 'description' => 'Istilah'],
                            'definition' => ['type' => 'string', 'description' => 'Penjelasan istilah'],
                        ],
                        'required' => ['term', 'definition'],
                    ],
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'references' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['title', 'summary', 'concept_summary', 'key_sections', 'conceptual_analogy', 'case_study', 'glossary', 'warnings', 'references'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function lkpdSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Judul LKPD Berdiferensiasi'],
                'summary' => ['type' => 'string', 'description' => 'Deskripsi tujuan dan panduan LKPD'],
                'general_instructions' => ['type' => 'string', 'description' => 'Petunjuk umum pengerjaan bagi peserta didik'],
                'learning_objectives' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Tujuan pembelajaran yang disasar pada lembar kerja',
                ],
                'level_1_scaffolding' => [
                    'type' => 'object',
                    'properties' => [
                        'target_group' => ['type' => 'string', 'description' => 'Sasaran: Murid Perlu Bimbingan Fondasi'],
                        'guidance_steps' => ['type' => 'string', 'description' => 'Panduan terstruktur langkah demi langkah'],
                        'tasks' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar soal latihan penuntun konsep dasar',
                        ],
                        'hints' => ['type' => 'string', 'description' => 'Petunjuk bantu dan kata kunci pendukung'],
                    ],
                    'required' => ['target_group', 'guidance_steps', 'tasks'],
                ],
                'level_2_regular' => [
                    'type' => 'object',
                    'properties' => [
                        'target_group' => ['type' => 'string', 'description' => 'Sasaran: Murid Reguler / Cakap'],
                        'instructions' => ['type' => 'string', 'description' => 'Instruksi tugas mandiri reguler'],
                        'core_tasks' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar soal dan tugas eksplorasi standar fase',
                        ],
                    ],
                    'required' => ['target_group', 'instructions', 'core_tasks'],
                ],
                'level_3_advanced' => [
                    'type' => 'object',
                    'properties' => [
                        'target_group' => ['type' => 'string', 'description' => 'Sasaran: Murid Pengayaan / Mahir HOTS'],
                        'challenge_case' => ['type' => 'string', 'description' => 'Kasus tantangan analisis masalah tingkat tinggi'],
                        'hots_tasks' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Tugas berpikir kritis, evaluasi, dan kreasi solusi',
                        ],
                        'mini_project_idea' => ['type' => 'string', 'description' => 'Gagasan mini projek atau investigasi mandiri'],
                    ],
                    'required' => ['target_group', 'challenge_case', 'hots_tasks'],
                ],
                'reflection_questions' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Pertanyaan refleksi kesadaran belajar murid',
                ],
                'scoring_rubric' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'aspect' => ['type' => 'string', 'description' => 'Aspek Penilaian'],
                            'level_1_desc' => ['type' => 'string', 'description' => 'Deskripsi capaian level 1'],
                            'level_2_desc' => ['type' => 'string', 'description' => 'Deskripsi capaian level 2'],
                            'level_3_desc' => ['type' => 'string', 'description' => 'Deskripsi capaian level 3'],
                            'max_score' => ['type' => 'integer', 'description' => 'Skor maksimal aspek'],
                        ],
                        'required' => ['aspect', 'level_1_desc', 'level_2_desc', 'level_3_desc', 'max_score'],
                    ],
                ],
                'teacher_answer_key' => ['type' => 'string', 'description' => 'Kunci jawaban dan panduan penskoran guru'],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'references' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['title', 'summary', 'general_instructions', 'learning_objectives', 'level_1_scaffolding', 'level_2_regular', 'level_3_advanced', 'reflection_questions', 'scoring_rubric', 'teacher_answer_key', 'warnings', 'references'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function modulP5Schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Judul Modul Projek P5'],
                'summary' => ['type' => 'string', 'description' => 'Ringkasan tujuan dan gambaran projek'],
                'p5_theme' => ['type' => 'string', 'description' => 'Tema Resmi P5 (misal: Kearifan Lokal, Gaya Hidup Berkelanjutan)'],
                'project_topic' => ['type' => 'string', 'description' => 'Topik Spesifik Projek'],
                'target_fase' => ['type' => 'string', 'description' => 'Fase Sasaran Projek'],
                'total_duration_jp' => ['type' => 'string', 'description' => 'Total Alokasi JP Projek'],
                'project_background' => ['type' => 'string', 'description' => 'Latar belakang kontekstual masalah di lingkungan'],
                'targeted_dimensions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'dimension' => ['type' => 'string', 'description' => 'Dimensi P5'],
                            'element' => ['type' => 'string', 'description' => 'Elemen Dimensi'],
                            'sub_element' => ['type' => 'string', 'description' => 'Subelemen Dimensi'],
                            'target_achievement' => ['type' => 'string', 'description' => 'Target Capaian di Akhir Fase'],
                        ],
                        'required' => ['dimension', 'element', 'sub_element', 'target_achievement'],
                    ],
                ],
                'project_stages' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'stage_name' => ['type' => 'string', 'description' => 'Nama Tahapan (Pengenalan, Kontekstualisasi, Aksi, Refleksi)'],
                            'duration_jp' => ['type' => 'string', 'description' => 'Alokasi JP Tahap'],
                            'activities' => ['type' => 'string', 'description' => 'Uraian aktivitas konkret pada tahap ini'],
                            'output_artifact' => ['type' => 'string', 'description' => 'Artefak atau hasil unjuk kerja yang dibuat'],
                        ],
                        'required' => ['stage_name', 'duration_jp', 'activities', 'output_artifact'],
                    ],
                ],
                'assessment_rubric' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'criteria' => ['type' => 'string', 'description' => 'Kriteria Penilaian Karakter'],
                            'mb_desc' => ['type' => 'string', 'description' => 'Indikator: Mulai Berkembang (MB)'],
                            'sb_desc' => ['type' => 'string', 'description' => 'Indikator: Sedang Berkembang (SB)'],
                            'bsh_desc' => ['type' => 'string', 'description' => 'Indikator: Berkembang Sesuai Harapan (BSH)'],
                            'sab_desc' => ['type' => 'string', 'description' => 'Indikator: Sangat Berkembang (SAB)'],
                        ],
                        'required' => ['criteria', 'mb_desc', 'sb_desc', 'bsh_desc', 'sab_desc'],
                    ],
                ],
                'glossary' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'term' => ['type' => 'string', 'description' => 'Istilah'],
                            'definition' => ['type' => 'string', 'description' => 'Penjelasan istilah'],
                        ],
                        'required' => ['term', 'definition'],
                    ],
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'references' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['title', 'summary', 'p5_theme', 'project_topic', 'project_background', 'targeted_dimensions', 'project_stages', 'assessment_rubric', 'warnings', 'references'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function asesmenKktpSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Judul Dokumen Asesmen & KKTP'],
                'summary' => ['type' => 'string', 'description' => 'Ringkasan instrumen dan kriteria ketercapaian'],
                'target_competency' => ['type' => 'string', 'description' => 'Kompetensi sasaran pembelajaran'],
                'diagnostic_assessment' => [
                    'type' => 'object',
                    'properties' => [
                        'cognitive_questions' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'question' => ['type' => 'string', 'description' => 'Pertanyaan diagnostik prasyarat materi'],
                                    'cognitive_level' => ['type' => 'string', 'description' => 'Level kognitif (C1-C3)'],
                                    'correct_answer' => ['type' => 'string', 'description' => 'Kunci jawaban atau kriteria benar'],
                                ],
                                'required' => ['question', 'cognitive_level', 'correct_answer'],
                            ],
                        ],
                        'non_cognitive_questions' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'question' => ['type' => 'string', 'description' => 'Pertanyaan non-kognitif gaya belajar & kesiapan'],
                                    'purpose' => ['type' => 'string', 'description' => 'Tujuan observasi'],
                                ],
                                'required' => ['question', 'purpose'],
                            ],
                        ],
                    ],
                    'required' => ['cognitive_questions', 'non_cognitive_questions'],
                ],
                'formative_assessment' => [
                    'type' => 'object',
                    'properties' => [
                        'observation_checklist' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'indicator' => ['type' => 'string', 'description' => 'Indikator unjuk kerja'],
                                    'observed_behavior' => ['type' => 'string', 'description' => 'Deskripsi perilaku teramati'],
                                ],
                                'required' => ['indicator', 'observed_behavior'],
                            ],
                        ],
                        'exit_ticket_prompts' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar prompt tiket keluar (refleksi singkat akhir KBM)',
                        ],
                        'peer_assessment_guide' => ['type' => 'string', 'description' => 'Panduan penilaian antarteman'],
                    ],
                    'required' => ['observation_checklist', 'exit_ticket_prompts'],
                ],
                'summative_assessment' => [
                    'type' => 'object',
                    'properties' => [
                        'assessment_grid' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'indicator' => ['type' => 'string', 'description' => 'Indikator Soal'],
                                    'question_type' => ['type' => 'string', 'description' => 'Bentuk Soal (Pilihan Ganda / Uraian)'],
                                    'cognitive_level' => ['type' => 'string', 'description' => 'Level Kognitif (C1-C6)'],
                                    'max_score' => ['type' => 'integer', 'description' => 'Skor Maksimal'],
                                ],
                                'required' => ['indicator', 'question_type', 'cognitive_level', 'max_score'],
                            ],
                        ],
                        'questions' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'number' => ['type' => 'integer', 'description' => 'Nomor Soal'],
                                    'question_type' => ['type' => 'string', 'description' => 'Tipe Soal'],
                                    'stimulus_text' => ['type' => 'string', 'description' => 'Wacana stimulus kontekstual / data kasus'],
                                    'question_text' => ['type' => 'string', 'description' => 'Teks butir pertanyaan'],
                                    'options' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                        'description' => 'Pilihan jawaban (A, B, C, D, E jika PG)',
                                    ],
                                    'correct_answer' => ['type' => 'string', 'description' => 'Kunci jawaban yang benar'],
                                    'explanation' => ['type' => 'string', 'description' => 'Pembahasan konsep'],
                                    'scoring_points' => ['type' => 'integer', 'description' => 'Bobot poin skor'],
                                ],
                                'required' => ['number', 'question_type', 'question_text', 'correct_answer', 'explanation', 'scoring_points'],
                            ],
                        ],
                    ],
                    'required' => ['assessment_grid', 'questions'],
                ],
                'kktp_rubric' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'aspect' => ['type' => 'string', 'description' => 'Aspek Capaian Pembelajaran'],
                            'perlu_bimbingan' => ['type' => 'string', 'description' => 'Deskripsi Kriteria: Perlu Bimbingan (0-65)'],
                            'cukup' => ['type' => 'string', 'description' => 'Deskripsi Kriteria: Cukup (66-75)'],
                            'baik' => ['type' => 'string', 'description' => 'Deskripsi Kriteria: Baik (76-85)'],
                            'sangat_baik' => ['type' => 'string', 'description' => 'Deskripsi Kriteria: Sangat Baik (86-100)'],
                        ],
                        'required' => ['aspect', 'perlu_bimbingan', 'cukup', 'baik', 'sangat_baik'],
                    ],
                ],
                'remedial_and_enrichment_guide' => ['type' => 'string', 'description' => 'Rekomendasi tindak lanjut program remedial dan pengayaan'],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'references' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['title', 'summary', 'diagnostic_assessment', 'formative_assessment', 'summative_assessment', 'kktp_rubric', 'warnings', 'references'],
        ];
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function assertMinimumStructure(array $draft, string $docType = 'modul_ajar'): void
    {
        $requiredKeys = match ($docType) {
            'atp' => ['title', 'summary', 'cp_general', 'cp_elements', 'atp_flow', 'warnings', 'references'],
            'prota_prosem' => ['title', 'summary', 'prota_distribution', 'prosem_odd', 'prosem_even', 'warnings', 'references'],
            'bahan_ajar', 'materi_ajar' => ['title', 'summary', 'concept_summary', 'key_sections', 'warnings', 'references'],
            'lkpd_bertingkat', 'lkpd' => ['title', 'summary', 'general_instructions', 'level_1_scaffolding', 'level_2_regular', 'level_3_advanced', 'warnings', 'references'],
            'modul_p5' => ['title', 'summary', 'p5_theme', 'project_topic', 'targeted_dimensions', 'project_stages', 'assessment_rubric', 'warnings', 'references'],
            'asesmen_kktp', 'asesmen' => ['title', 'summary', 'diagnostic_assessment', 'formative_assessment', 'summative_assessment', 'kktp_rubric', 'warnings', 'references'],
            default => [
                'title',
                'summary',
                'learning_objectives',
                'activities',
                'assessment',
                'differentiation',
                'resources',
                'warnings',
                'references',
            ],
        };

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $draft)) {
                throw new RuntimeException('Draf Gemini tidak lengkap: key ['.$key.'] tidak ditemukan.');
            }

            if (is_string($draft[$key]) && trim($draft[$key]) === '') {
                throw new RuntimeException('Draf Gemini tidak valid: nilai ['.$key.'] kosong.');
            }
        }
    }
}
