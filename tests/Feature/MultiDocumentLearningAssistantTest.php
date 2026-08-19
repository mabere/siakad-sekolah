<?php

namespace Tests\Feature;

use App\Livewire\Teacher\LearningAssistant;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LearningDraft;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class MultiDocumentLearningAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        config([
            'services.gemini.enabled' => true,
            'services.gemini.api_key' => 'test-key',
            'services.gemini.model' => 'gemini-2.5-flash',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ]);
    }

    public function test_teacher_can_generate_atp_document_and_sync_to_journal(): void
    {
        $fixture = $this->createFixture();

        $atpPayload = [
            'title' => 'Alur Tujuan Pembelajaran Bahasa Indonesia Fase E',
            'summary' => 'ATP untuk memetakan capaian pembelajaran bahasa Indonesia kelas 10.',
            'cp_general' => 'Peserta didik memiliki kemampuan berbahasa untuk berkomunikasi dan bernalar.',
            'cp_elements' => [
                ['element_name' => 'Membaca dan Memirsa', 'cp_statement' => 'Mengevaluasi informasi faktual.'],
                ['element_name' => 'Menulis', 'cp_statement' => 'Menulis gagasan dalam bentuk teks eksposisi.'],
            ],
            'atp_flow' => [
                [
                    'sequence_number' => '10.1',
                    'chapter' => 'Bab 1 LHO',
                    'topic' => 'Teks Laporan Hasil Observasi',
                    'learning_objectives' => 'Mengevaluasi informasi akurat dan fakta dalam teks LHO.',
                    'indicators' => 'Mampu menemukan 3 data fakta dalam wacana.',
                    'suggested_duration_jp' => '6 JP',
                    'p5_dimensions' => ['Bernalar Kritis', 'Mandiri'],
                    'assessment_technique' => 'Tes Tertulis & Portofolio',
                ],
            ],
            'total_duration_jp' => '72 JP',
            'glossary' => [
                ['term' => 'LHO', 'definition' => 'Laporan Hasil Observasi'],
            ],
            'warnings' => [],
            'references' => ['Buku Guru Bahasa Indonesia Kemendikdasmen 2024'],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($atpPayload, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('documentType', 'atp')
            ->set('topic', 'Penyusunan Alur Tujuan Pembelajaran Bahasa Indonesia')
            ->set('learningObjectives', 'Menurunkan CP menjadi TP berurutan.')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('draft.title', 'Alur Tujuan Pembelajaran Bahasa Indonesia Fase E')
            ->call('saveDraft')
            ->assertSet('savedDraftStatus', 'draft')
            ->call('syncToTeachingJournal')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teaching_journals', [
            'school_id' => $fixture['school']->id,
            'schedule_id' => $fixture['schedule']->id,
            'learning_method' => 'Kurikulum Merdeka',
        ]);
    }

    public function test_teacher_can_generate_modul_p5_document(): void
    {
        $fixture = $this->createFixture();

        $p5Payload = [
            'title' => 'Modul P5: Jejak Karbon dan Sampah Plastik di Sekolah',
            'summary' => 'Modul projek untuk membangun kesadaran gaya hidup berkelanjutan.',
            'p5_theme' => 'Gaya Hidup Berkelanjutan',
            'project_topic' => 'Pengolahan Sampah Plastik Menjadi Ecobrick',
            'target_fase' => 'Fase E (Kelas 10)',
            'total_duration_jp' => '48 JP',
            'project_background' => 'Penumpukan sampah plastik kantin sekolah yang belum terkelola.',
            'targeted_dimensions' => [
                [
                    'dimension' => 'Gotong Royong',
                    'element' => 'Kolaborasi',
                    'sub_element' => 'Kerja sama tim',
                    'target_achievement' => 'Membangun sinergi kelompok dalam menyelesaikan masalah.',
                ],
            ],
            'project_stages' => [
                [
                    'stage_name' => 'Tahap 1: Pengenalan',
                    'duration_jp' => '12 JP',
                    'activities' => 'Eksplorasi isu sampah global dan lokal.',
                    'output_artifact' => 'Infografis audit sampah',
                ],
                [
                    'stage_name' => 'Tahap 2: Aksi Nyata',
                    'duration_jp' => '24 JP',
                    'activities' => 'Pembuatan ecobrick dan perabotan sekolah.',
                    'output_artifact' => 'Meja & kursi dari ecobrick',
                ],
            ],
            'assessment_rubric' => [
                [
                    'criteria' => 'Inisiatif Pengelolaan Sampah',
                    'mb_desc' => 'Mulai memahami isu',
                    'sb_desc' => 'Aktif mengumpulkan sampah',
                    'bsh_desc' => 'Mampu memilah dan mendaur ulang',
                    'sab_desc' => 'Menggerakkan komunitas sekolah',
                ],
            ],
            'glossary' => [
                ['term' => 'Ecobrick', 'definition' => 'Botol plastik yang diisi padat dengan sampah non-biodegradable'],
            ],
            'warnings' => [],
            'references' => ['Panduan Pengembangan Projek Penguatan Profil Pelajar Pancasila BSKAP 2024'],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($p5Payload, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('documentType', 'modul_p5')
            ->set('selectedP5Theme', 'Gaya Hidup Berkelanjutan')
            ->set('topic', 'Pengurangan Sampah Plastik')
            ->set('learningObjectives', 'Mengembangkan kesadaran ekologis dan gotong royong.')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('draft.p5_theme', 'Gaya Hidup Berkelanjutan')
            ->call('saveDraft')
            ->assertSet('savedDraftStatus', 'draft')
            ->call('syncToTeachingJournal')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teaching_journals', [
            'school_id' => $fixture['school']->id,
            'schedule_id' => $fixture['schedule']->id,
            'topic_summary' => 'Projek P5: Pengolahan Sampah Plastik Menjadi Ecobrick',
        ]);
    }

    public function test_teacher_can_generate_asesmen_kktp_and_export_to_cbt_bank(): void
    {
        $fixture = $this->createFixture();

        $asesmenPayload = [
            'title' => 'Instrumen Asesmen Formatif & Sumatif AKM Teks LHO',
            'summary' => 'Paket asesmen komprehensif lengkap dengan rubrik KKTP.',
            'target_competency' => 'Menganalisis dan menyusun teks laporan hasil observasi objektif.',
            'diagnostic_assessment' => [
                'cognitive_questions' => [
                    [
                        'question' => 'Apa perbedaan utama antara kalimat fakta dan opini?',
                        'cognitive_level' => 'L1 Pemahaman',
                        'correct_answer' => 'Fakta dapat dibuktikan kebenarannya, opini adalah pandangan subjektif.',
                    ],
                ],
                'non_cognitive_questions' => [
                    [
                        'question' => 'Bagaimana Anda lebih mudah memahami materi observasi (membaca teks / mengamati video / praktik langsung)?',
                        'purpose' => 'Memetakan preferensi gaya belajar siswa.',
                    ],
                ],
            ],
            'formative_assessment' => [
                'observation_checklist' => [
                    [
                        'indicator' => 'Keaktifan bertanya dalam diskusi',
                        'observed_behavior' => 'Mengajukan minimal 1 pertanyaan kritis mengenai struktur teks.',
                    ],
                ],
                'exit_ticket_prompts' => [
                    'Tuliskan 1 konsep paling berharga yang Anda pelajari hari ini!',
                ],
                'peer_assessment_guide' => 'Gunakan rubrik teman sebaya untuk mengoreksi draf teks LHO.',
            ],
            'summative_assessment' => [
                'assessment_grid' => [
                    [
                        'indicator' => 'Mengidentifikasi informasi fakta',
                        'question_type' => 'Pilihan Ganda',
                        'cognitive_level' => 'L2 Aplikasi',
                        'max_score' => 10,
                    ],
                ],
                'questions' => [
                    [
                        'number' => 1,
                        'question_type' => 'Pilihan Ganda',
                        'stimulus_text' => 'Taman Nasional Baluran memiliki keanekaragaman hayati yang tinggi dengan luas 25.000 hektar.',
                        'question_text' => 'Berdasarkan wacana di atas, manakah yang merupakan data fakta numerik?',
                        'options' => ['A. Memiliki keanekaragaman tinggi', 'B. Luas 25.000 hektar', 'C. Taman terindah', 'D. Sangat luas'],
                        'correct_answer' => 'B. Luas 25.000 hektar',
                        'explanation' => 'Luas 25.000 hektar adalah data kuantitatif yang teruji.',
                        'scoring_points' => 10,
                    ],
                ],
            ],
            'kktp_rubric' => [
                [
                    'aspect' => 'Ketepatan Struktur Teks',
                    'perlu_bimbingan' => 'Belum memuat 3 struktur',
                    'cukup' => 'Memuat 2 struktur',
                    'baik' => 'Memuat 3 struktur lengkap',
                    'sangat_baik' => 'Memuat 3 struktur dengan elaborasi mendalam',
                ],
            ],
            'remedial_and_enrichment_guide' => 'Remedial bimbingan konsep struktur, pengayaan riset observasi mandiri.',
            'warnings' => [],
            'references' => ['Panduan Pembelajaran dan Asesmen PPA Kemendikdasmen 2024'],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($asesmenPayload, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('documentType', 'asesmen_kktp')
            ->set('topic', 'Asesmen AKM Teks LHO')
            ->set('learningObjectives', 'Mengukur pemahaman struktur dan kaidah kebahasaan teks LHO.')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('draft.title', 'Instrumen Asesmen Formatif & Sumatif AKM Teks LHO')
            ->call('saveDraft')
            ->assertSet('savedDraftStatus', 'draft')
            ->call('syncToQuestionBank')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('question_banks', [
            'school_id' => $fixture['school']->id,
            'subject_id' => $fixture['schedule']->subject_id,
            'title' => 'Bank Soal: Instrumen Asesmen Formatif & Sumatif AKM Teks LHO',
        ]);

        $this->assertDatabaseHas('questions', [
            'correct_answer' => 'B. Luas 25.000 hektar',
        ]);
    }

    public function test_teacher_can_print_and_export_different_document_types(): void
    {
        $fixture = $this->createFixture();

        $draft = LearningDraft::create([
            'school_id' => $fixture['school']->id,
            'academic_year_id' => $fixture['academic_year']->id,
            'teacher_id' => $fixture['teacher']->id,
            'schedule_id' => $fixture['schedule']->id,
            'user_id' => $fixture['user']->id,
            'document_type' => 'lkpd_bertingkat',
            'status' => LearningDraft::STATUS_APPROVED,
            'source' => 'user',
            'version' => 1,
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'input_context' => [],
            'output' => [
                'title' => 'LKPD Berdiferensiasi Teks LHO',
                'summary' => 'LKPD 3 jenjang kesiapan belajar.',
                'general_instructions' => 'Kerjakan sesuai kelompok kesiapan Anda.',
                'level_1_scaffolding' => ['tasks' => ['Sebutkan ciri teks LHO']],
                'level_2_regular' => ['core_tasks' => ['Temukan verba material']],
                'level_3_advanced' => ['hots_tasks' => ['Susun teks LHO observasi']],
                'references' => ['Kemendikdasmen 2024'],
            ],
        ]);

        // 1. Test HTML Print
        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.learning-assistant.print', $draft->id))
            ->assertOk()
            ->assertSee('LEMBAR KERJA PESERTA DIDIK (LKPD) BERDIFERENSIASI')
            ->assertSee('LEVEL 1: PERLU BIMBINGAN (SCAFFOLDING)')
            ->assertSee('LEVEL 2: REGULER (CAKAP)')
            ->assertSee('LEVEL 3: PENGAYAAN / HOTS (MAHIR)');

        // 2. Test Word Export
        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.learning-assistant.export-word', $draft->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/msword; charset=utf-8');
    }

    /**
     * @return array{
     *   school: School,
     *   user: User,
     *   teacher: Teacher,
     *   academic_year: AcademicYear,
     *   classroom: Classroom,
     *   subject: Subject,
     *   schedule: Schedule
     * }
     */
    private function createFixture(): array
    {
        $school = School::create([
            'name' => 'SMAS 1 Alam',
            'level' => 'SMA',
            'status' => 'SWASTA',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        $user = User::create([
            'name' => 'Budi Guru',
            'email' => 'budi.'.uniqid().'@test.com',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole('Guru');

        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'nip' => '198701012010011001',
            'name' => 'Budi Guru, M.Pd.',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'curriculum_type' => 'MERDEKA',
        ]);

        $classroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'X-A',
            'grade_level' => '10',
        ]);

        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Bahasa Indonesia',
            'code' => 'BIND-'.uniqid(),
            'type' => 'Wajib',
        ]);

        $schedule = Schedule::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
        ]);

        return [
            'school' => $school,
            'user' => $user,
            'teacher' => $teacher,
            'academic_year' => $academicYear,
            'classroom' => $classroom,
            'subject' => $subject,
            'schedule' => $schedule,
        ];
    }
}
