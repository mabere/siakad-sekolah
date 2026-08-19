<?php

namespace Database\Seeders;

use App\Models\LearningDraft;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class LearningDraftDemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $school) {
            return;
        }

        $academicYear = $school->academicYears()
            ->where('is_active', true)
            ->first();
        $teacher = Teacher::query()
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->first();

        if (! $academicYear || ! $teacher) {
            return;
        }

        $schedule = Schedule::query()
            ->where('school_id', $school->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('teacher_id', $teacher->id)
            ->with(['classroom', 'subject'])
            ->orderBy('id')
            ->first();

        if (! $schedule) {
            return;
        }

        LearningDraft::updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'teacher_id' => $teacher->id,
                'schedule_id' => $schedule->id,
                'document_type' => 'modul_ajar',
                'source' => 'demo',
                'version' => 1,
            ],
            [
                'user_id' => $teacher->user_id,
                'status' => LearningDraft::STATUS_DRAFT,
                'provider' => 'gemini',
                'model' => 'demo-gemini',
                'input_context' => [
                    'jenis_dokumen' => 'Modul Ajar / RPP',
                    'topik' => 'Ekosistem di lingkungan sekitar',
                    'tujuan_pembelajaran' => 'Siswa dapat menjelaskan hubungan antarkomponen ekosistem.',
                    'demo_seed_key' => 'learning-draft-demo-v1',
                ],
                'output' => [
                    'title' => 'Modul Ajar: Ekosistem di Lingkungan Sekitar',
                    'summary' => 'Draf modul ajar Kurikulum Merdeka untuk menguji telaah komponen, asesmen bertingkat, LKPD, dan rubrik penilaian.',
                    'p5_dimensions' => ['Bernalar Kritis', 'Gotong Royong', 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia'],
                    'learning_model' => 'Problem-Based Learning (PBL)',
                    'meaningful_understanding' => 'Keseimbangan ekosistem sangat dipengaruhi oleh setiap tindakan manusia terhadap lingkungan sekitarnya.',
                    'inquiry_questions' => [
                        'Apa yang akan terjadi jika salah satu rantai makanan di sawah atau kebun sekolah terputus?',
                        'Bagaimana peran kita menjaga kebersihan ekosistem sekolah?',
                    ],
                    'learning_objectives' => [
                        'Siswa dapat mengidentifikasi komponen biotik dan abiotik di lingkungan sekolah.',
                        'Siswa dapat menganalisis interaksi antarkomponen dalam jaring-jaring makanan.',
                    ],
                    'activities' => [
                        [
                            'stage' => 'Pendahuluan',
                            'duration_minutes' => 10,
                            'activity' => 'Mengamati gambar lingkungan sekitar dan menjawab pertanyaan pemantik.',
                            'teacher_role' => 'Mengarahkan observasi dan mengajukan pertanyaan pemantik.',
                            'student_role' => 'Mengamati dan menyampaikan gagasan awal.',
                        ],
                        [
                            'stage' => 'Kegiatan Inti',
                            'duration_minutes' => 35,
                            'activity' => 'Eksplorasi taman sekolah dalam kelompok dan menyusun peta jaring-jaring makanan.',
                            'teacher_role' => 'Memfasilitasi diskusi kelompok dan memantau kerja lapangan.',
                            'student_role' => 'Mencatat temuan biotik/abiotik dan berdiskusi.',
                        ],
                        [
                            'stage' => 'Penutup & Refleksi',
                            'duration_minutes' => 15,
                            'activity' => 'Menyusun kesimpulan dan mengisi lembar refleksi diri.',
                            'teacher_role' => 'Memberikan umpan balik dan penguatan konsep.',
                            'student_role' => 'Menyimpulkan dan merefleksikan pembelajaran.',
                        ],
                    ],
                    'student_worksheet' => [
                        'title' => 'LKPD 1: Eksplorasi Ekosistem Taman Sekolah',
                        'instructions' => 'Amati area taman sekolah selama 15 menit, catat komponen yang ditemukan pada tabel di bawah ini.',
                        'tasks' => [
                            'Catat minimal 5 komponen biotik dan 5 komponen abiotik.',
                            'Gambarkan 1 rantai makanan yang terjadi antara komponen tersebut.',
                            'Tuliskan simpulan hubungan ketergantungan antarkomponen.',
                        ],
                    ],
                    'assessment' => [
                        'diagnostic' => 'Tanya jawab pemantik tentang makhluk hidup dan benda tak hidup.',
                        'formative' => 'Observasi keaktifan diskusi dan review pengerjaan LKPD.',
                        'summative' => 'Tes tertulis analisis dampak kerusakan salah satu komponen rantai makanan.',
                    ],
                    'assessment_rubric' => [
                        [
                            'criteria' => 'Identifikasi Komponen',
                            'indicator' => 'Mampu membedakan komponen biotik dan abiotik dengan tepat.',
                            'scoring_guide' => 'Skor 4: Tepat semua, Skor 3: Tepat sebagian besar, Skor 2: Cukup, Skor 1: Belum tepat',
                        ],
                        [
                            'criteria' => 'Analisis Rantai Makanan',
                            'indicator' => 'Mampu menyusun urutan produsen, konsumen, dan pengurai secara logis.',
                            'scoring_guide' => 'Skor 4: Sangat Logis, Skor 3: Logis, Skor 2: Cukup, Skor 1: Kurang logis',
                        ],
                    ],
                    'differentiation' => 'Sediakan kartu bergambar untuk peserta didik visual, dan tantangan studi kasus lokal untuk kelompok yang lebih cepat paham.',
                    'resources' => ['Taman sekolah', 'LKPD Eksplorasi', 'Buku Siswa IPA'],
                    'warnings' => ['Pastikan keselamatan peserta didik saat melakukan observasi di luar kelas.'],
                    'references' => ['Kemendikbudristek BSKAP No. 032/H/KR/2024'],
                ],
            ],
        );
    }
}
