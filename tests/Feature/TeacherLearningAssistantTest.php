<?php

namespace Tests\Feature;

use App\Livewire\Teacher\LearningAssistant;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LearningDraft;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\LearningDraftDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherLearningAssistantTest extends TestCase
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

    public function test_teacher_can_open_the_isolated_learning_assistant_route(): void
    {
        $fixture = $this->createFixture();

        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.learning-assistant'))
            ->assertOk()
            ->assertSee('Perangkat Pembelajaran AI');
    }

    public function test_teacher_can_generate_a_structured_draft_from_owned_schedule(): void
    {
        $fixture = $this->createFixture();
        $payload = $this->draftPayload();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($payload, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear satu variabel')
            ->set('learningObjectives', 'Siswa dapat menyelesaikan persamaan linear satu variabel.')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('draft.title', 'Modul Ajar Persamaan Linear');

        Http::assertSent(function ($request): bool {
            $body = json_decode($request->body(), true);

            return $request->hasHeader('x-goog-api-key')
                && str_contains($request->body(), 'Persamaan linear satu variabel')
                && str_contains($request->url(), 'gemini-2.5-flash:generateContent')
                && data_get($body, 'systemInstruction.parts.0.text') !== null
                && data_get($body, 'generationConfig.responseMimeType') === 'application/json'
                && data_get($body, 'generationConfig.responseJsonSchema.type') === 'object';
        });
    }

    public function test_school_vision_and_mission_prefill_the_learning_context(): void
    {
        $fixture = $this->createFixture();
        $fixture['school']->update([
            'vision' => 'Terwujudnya peserta didik yang berkarakter.',
            'mission' => "Menyelenggarakan pembelajaran bermutu.\nMenguatkan budaya literasi.",
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->assertSet('schoolVisionMission', "Visi:\nTerwujudnya peserta didik yang berkarakter.\n\nMisi:\nMenyelenggarakan pembelajaran bermutu.\nMenguatkan budaya literasi.");
    }

    public function test_active_year_and_class_profile_are_sent_to_the_learning_provider(): void
    {
        $fixture = $this->createFixture();
        $fixture['academicYear']->update([
            'curriculum_type' => 'K13',
            'local_content' => 'Bahasa daerah',
        ]);
        $fixture['schedule']->classroom->update([
            'student_needs' => 'Kemampuan awal beragam.',
            'available_facilities' => 'Papan tulis dan perpustakaan.',
            'learning_environment' => 'Ruang kelas.',
        ]);
        $this->fakeGeminiResponse();

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear satu variabel')
            ->set('learningObjectives', 'Siswa dapat menyelesaikan persamaan linear satu variabel.')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('curriculumType', 'K13')
            ->assertSet('localContent', 'Bahasa daerah')
            ->assertSet('studentNeeds', 'Kemampuan awal beragam.')
            ->assertSet('availableFacilities', 'Papan tulis dan perpustakaan.');

        Http::assertSent(function ($request): bool {
            return str_contains($request->body(), 'Kemampuan awal beragam.')
                && str_contains($request->body(), 'Papan tulis dan perpustakaan.')
                && str_contains($request->body(), 'Lingkungan belajar: Ruang kelas.')
                && str_contains($request->body(), 'Bahasa daerah');
        });
    }

    public function test_teacher_can_generate_when_gemini_wraps_json_in_a_code_fence(): void
    {
        $fixture = $this->createFixture();
        $payload = $this->draftPayload();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "```json\n".json_encode($payload, JSON_UNESCAPED_UNICODE)."\n```"],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear satu variabel')
            ->set('learningObjectives', 'Siswa dapat menyelesaikan persamaan linear satu variabel.')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('draft.title', 'Modul Ajar Persamaan Linear');
    }

    public function test_disabled_feature_does_not_call_gemini_or_change_existing_data(): void
    {
        config(['services.gemini.enabled' => false]);
        Http::fake();
        $fixture = $this->createFixture();

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear satu variabel')
            ->set('learningObjectives', 'Siswa dapat menyelesaikan persamaan linear satu variabel.')
            ->call('generate')
            ->assertHasErrors('generation')
            ->assertSet('draft', null);

        Http::assertNothingSent();
        $this->assertDatabaseCount('teaching_journals', 0);
    }

    public function test_generation_rechecks_provider_configuration_after_component_mount(): void
    {
        config(['services.gemini.enabled' => false]);
        $fixture = $this->createFixture();
        $this->fakeGeminiResponse();

        $component = Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->assertSet('isConfigured', false);

        config(['services.gemini.enabled' => true]);

        $component
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear satu variabel')
            ->set('learningObjectives', 'Siswa dapat menyelesaikan persamaan linear satu variabel.')
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('isConfigured', true)
            ->assertSet('draft.title', 'Modul Ajar Persamaan Linear');
    }

    public function test_teacher_can_save_and_approve_a_generated_draft(): void
    {
        $fixture = $this->createFixture();
        $this->fakeGeminiResponse();

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear satu variabel')
            ->set('learningObjectives', 'Siswa dapat menyelesaikan persamaan linear satu variabel.')
            ->call('generate')
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertSet('savedDraftStatus', LearningDraft::STATUS_DRAFT);

        $this->assertDatabaseHas('learning_drafts', [
            'school_id' => $fixture['school']->id,
            'teacher_id' => $fixture['teacher']->id,
            'schedule_id' => $fixture['schedule']->id,
            'status' => LearningDraft::STATUS_DRAFT,
            'version' => 1,
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear satu variabel')
            ->set('learningObjectives', 'Siswa dapat menyelesaikan persamaan linear satu variabel.')
            ->call('generate')
            ->call('saveDraft')
            ->call('approveDraft')
            ->assertHasNoErrors()
            ->assertSet('savedDraftStatus', LearningDraft::STATUS_APPROVED);

        $this->assertDatabaseHas('learning_drafts', [
            'teacher_id' => $fixture['teacher']->id,
            'schedule_id' => $fixture['schedule']->id,
            'status' => LearningDraft::STATUS_APPROVED,
            'version' => 2,
            'approved_by' => $fixture['user']->id,
        ]);
    }

    public function test_demo_seeder_is_idempotent_and_uses_existing_teacher_context(): void
    {
        $fixture = $this->createFixture();

        $this->seed(LearningDraftDemoSeeder::class);
        $this->seed(LearningDraftDemoSeeder::class);

        $this->assertDatabaseCount('learning_drafts', 1);
        $this->assertDatabaseHas('learning_drafts', [
            'school_id' => $fixture['school']->id,
            'teacher_id' => $fixture['teacher']->id,
            'schedule_id' => $fixture['schedule']->id,
            'source' => 'demo',
            'status' => LearningDraft::STATUS_DRAFT,
        ]);
    }

    public function test_teacher_can_load_the_seeded_draft_inside_their_scope(): void
    {
        $fixture = $this->createFixture();
        $this->seed(LearningDraftDemoSeeder::class);
        $demoDraft = LearningDraft::query()->where('source', 'demo')->firstOrFail();

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->call('loadSavedDraft', $demoDraft->id)
            ->assertHasNoErrors()
            ->assertSet('draft.title', 'Modul Ajar: Ekosistem di Lingkungan Sekitar')
            ->assertSet('savedDraftStatus', LearningDraft::STATUS_DRAFT);
    }

    public function test_teacher_cannot_generate_from_another_teachers_schedule(): void
    {
        $fixture = $this->createFixture();
        $foreignUser = User::create([
            'name' => 'Guru Lain',
            'email' => 'teacher-foreign-ai@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $fixture['school']->id,
            'is_active' => true,
        ]);
        $foreignUser->assignRole('Guru');

        $foreignTeacher = Teacher::create([
            'school_id' => $fixture['school']->id,
            'user_id' => $foreignUser->id,
            'name' => 'Guru Lain',
            'gender' => 'P',
            'is_active' => true,
        ]);
        $foreignClassroom = Classroom::create([
            'school_id' => $fixture['school']->id,
            'academic_year_id' => $fixture['academicYear']->id,
            'teacher_id' => $foreignTeacher->id,
            'name' => 'VII B',
            'grade_level' => '7',
        ]);
        $foreignSchedule = Schedule::create([
            'school_id' => $fixture['school']->id,
            'academic_year_id' => $fixture['academicYear']->id,
            'classroom_id' => $foreignClassroom->id,
            'subject_id' => $fixture['subject']->id,
            'teacher_id' => $foreignTeacher->id,
            'day_of_week' => 'Selasa',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        Http::fake();

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $foreignSchedule->id)
            ->set('topic', 'Percobaan lintas jadwal')
            ->set('learningObjectives', 'Percobaan akses tidak sah.')
            ->call('generate')
            ->assertHasErrors('selectedScheduleId')
            ->assertSet('draft', null);

        Http::assertNothingSent();
    }

    /**
     * @return array{
     *     school: School,
     *     academicYear: AcademicYear,
     *     user: User,
     *     teacher: Teacher,
     *     subject: Subject,
     *     schedule: Schedule
     * }
     */
    private function createFixture(): array
    {
        $school = School::create([
            'name' => 'Sekolah AI Test',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $user = User::create([
            'name' => 'Guru AI Test',
            'email' => 'teacher-ai@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole('Guru');
        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'name' => 'Guru AI Test',
            'gender' => 'L',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Matematika',
            'code' => 'MAT-AI',
            'type' => 'Wajib',
        ]);
        $classroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'name' => 'VII A',
            'grade_level' => '7',
        ]);
        $schedule = Schedule::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:00',
            'end_time' => '08:00',
        ]);

        return compact('school', 'academicYear', 'user', 'teacher', 'subject', 'classroom', 'schedule');
    }

    private function fakeGeminiResponse(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($this->draftPayload(), JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    public function test_teacher_can_generate_and_print_bahasa_indonesia_fase_e_kurikulum_merdeka_modul(): void
    {
        $fixture = $this->createFixture();
        // Update classroom to Grade 10 (Fase E) and Subject to Bahasa Indonesia
        $fixture['classroom']->update(['grade_level' => '10', 'name' => 'X-1']);
        $fixture['subject']->update(['name' => 'Bahasa Indonesia', 'code' => 'BINDO-10']);

        $payload = [
            'title' => 'Mengungkap Fakta Alam Secara Objektif (Teks LHO)',
            'summary' => 'Modul ajar Bahasa Indonesia Fase E Kelas 10 berfokus pada analisis struktur dan kaidah kebahasaan teks Laporan Hasil Observasi.',
            'p5_dimensions' => ['Bernalar Kritis', 'Gotong Royong', 'Kreatif'],
            'learning_model' => 'Problem-Based Learning (PBL) & Pedagogi Genre',
            'meaningful_understanding' => 'Observasi objektif dan penulisan berbasis fakta melatih kejujuran intelektual dan daya kritis dalam memandang fenomena alam/sosial.',
            'inquiry_questions' => [
                'Bagaimana cara membedakan fakta ilmiah objektif dengan opini atau kabar burung?',
                'Mengapa struktur klasifikasi dan deskripsi bagian penting dalam sebuah laporan?',
            ],
            'learning_objectives' => [
                'Peserta didik mampu mengevaluasi informasi akurat dan fakta dalam teks LHO.',
                'Peserta didik mampu menyusun teks LHO berdasarkan observasi lingkungan nyata dengan kaidah kebahasaan yang tepat.',
            ],
            'activities' => [
                [
                    'stage' => 'Pendahuluan (BKoF)',
                    'duration_minutes' => 15,
                    'activity' => 'Membangun konteks lewat tayangan video observasi alam dan tanya jawab pemantik.',
                    'teacher_role' => 'Memfasilitasi diskusi dan mengajukan pertanyaan pemantik.',
                    'student_role' => 'Menyimak dan mengemukakan pendapat awal.',
                ],
                [
                    'stage' => 'Kegiatan Inti (MoT & JCoT)',
                    'duration_minutes' => 55,
                    'activity' => 'Bedah struktur teks model dan kerja kelompok menyusun kerangka LHO.',
                    'teacher_role' => 'Membimbing kelompok dan memberikan umpan balik formatif.',
                    'student_role' => 'Berdiskusi dan mengidentifikasi verba relasional serta kalimat definisi.',
                ],
                [
                    'stage' => 'Penutup & Refleksi (ICoT)',
                    'duration_minutes' => 20,
                    'activity' => 'Penyimpulan pembelajaran dan penyusunan draf mandiri.',
                    'teacher_role' => 'Mengarahkan refleksi dan tugas observasi mandiri.',
                    'student_role' => 'Mengisi lembar refleksi belajar.',
                ],
            ],
            'student_worksheet' => [
                'title' => 'LKPD 1: Analisis Struktur dan Kebahasaan Teks LHO',
                'instructions' => 'Bacalah teks LHO "Kunang-Kunang" berikut secara teliti, lalu kerjakan tugas kelompok di bawah ini.',
                'tasks' => [
                    'Identifikasilah bagian pernyataan umum, deskripsi bagian, dan deskripsi manfaat.',
                    'Temukan 3 kalimat definisi dan 3 kalimat deskripsi dalam teks.',
                    'Susunlah ringkasan isi laporan dalam 1 paragraf padu.',
                ],
            ],
            'assessment' => [
                'diagnostic' => 'Kuis lisan pemantik pengetahuan awal mengenai observasi lingkungan.',
                'formative' => 'Observasi keaktifan diskusi kelompok dan telaah lembar kerja siswa (LKPD).',
                'summative' => 'Penilaian produk draf teks Laporan Hasil Observasi (LHO) utuh.',
            ],
            'assessment_rubric' => [
                [
                    'criteria' => 'Kelengkapan Struktur Teks',
                    'indicator' => 'Memuat klasifikasi umum, deskripsi bagian, dan manfaat secara lengkap dan runtut.',
                    'scoring_guide' => 'Skor 4: Sangat Lengkap, Skor 3: Lengkap, Skor 2: Cukup, Skor 1: Kurang',
                ],
                [
                    'criteria' => 'Kaidah Kebahasaan',
                    'indicator' => 'Penggunaan kalimat definisi, kalimat deskripsi, dan istilah ilmiah tepat sesuai EYD V.',
                    'scoring_guide' => 'Skor 4: Tepat & Akurat, Skor 3: Sebagian besar tepat, Skor 2: Cukup, Skor 1: Banyak kesalahan',
                ],
            ],
            'differentiation' => 'Peserta didik yang membutuhkan bimbingan diberikan teks rintisan dengan panduan kalimat pembuka. Peserta didik mahir meneliti topik biodiversitas lokal yang lebih kompleks.',
            'resources' => ['Buku Siswa Cerdas Cergas Kelas X', 'Proyektor', 'Taman Sekolah / Lingkungan Sekitar', 'LKPD'],
            'warnings' => ['Verifikasi contoh teks LHO dengan kekayaan flora/fauna lokal sekolah.'],
            'references' => ['Kemendikbudristek BSKAP No. 032/H/KR/2024', 'Buku Guru Cerdas Cergas Bahasa Indonesia SMA/SMK Kelas X'],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($payload, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $component = Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->assertSet('detectedFase', 'Fase E (Kelas 10 SMA/SMK)')
            ->set('documentType', 'modul_ajar')
            ->set('topic', 'Mengungkap Fakta Alam Secara Objektif (Teks LHO)')
            ->set('learningObjectives', 'Mengevaluasi informasi akurat dan menulis teks LHO berdasarkan observasi nyata.')
            ->set('selectedLearningModel', 'Problem-Based Learning (PBL)')
            ->set('selectedP5Dimensions', ['Bernalar Kritis', 'Kreatif', 'Gotong Royong'])
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('draft.title', 'Mengungkap Fakta Alam Secara Objektif (Teks LHO)')
            ->call('saveDraft');

        $draftId = $component->get('savedDraftId');
        $this->assertNotNull($draftId);

        // Verify print view renders Kurikulum Merdeka Fase E, P5, LKPD, and Rubrik
        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.learning-assistant.print', $draftId))
            ->assertOk()
            ->assertSee('Fase E (Kelas 10)')
            ->assertSee('Bahasa Indonesia')
            ->assertSee('Bernalar Kritis')
            ->assertSee('Pemahaman Bermakna')
            ->assertSee('LKPD 1: Analisis Struktur')
            ->assertSee('Kelengkapan Struktur Teks')
            ->assertSee('Kepala Sekolah')
            ->assertSee('Guru Mata Pelajaran');
    }

    public function test_teacher_can_sync_draft_to_teaching_journal(): void
    {
        $fixture = $this->createFixture();
        $this->fakeGeminiResponse();

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear')
            ->set('learningObjectives', 'Tujuan pembelajaran')
            ->call('generate')
            ->call('syncToTeachingJournal')
            ->assertHasNoErrors()
            ->assertSee('Jurnal mengajar pertemuan ke-1 berhasil dibuat');

        $this->assertDatabaseHas('teaching_journals', [
            'school_id' => $fixture['school']->id,
            'schedule_id' => $fixture['schedule']->id,
            'teacher_id' => $fixture['teacher']->id,
            'meeting_number' => 1,
            'topic_summary' => 'Modul Ajar Persamaan Linear',
        ]);
    }

    public function test_teacher_can_sync_draft_to_cbt_question_bank(): void
    {
        $fixture = $this->createFixture();
        $this->fakeGeminiResponse();

        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Persamaan linear')
            ->set('learningObjectives', 'Tujuan pembelajaran')
            ->call('generate')
            ->call('syncToQuestionBank')
            ->assertHasNoErrors()
            ->assertSee('berhasil dibuat dengan');

        $this->assertDatabaseHas('question_banks', [
            'school_id' => $fixture['school']->id,
            'subject_id' => $fixture['subject']->id,
            'teacher_id' => $fixture['teacher']->id,
            'title' => 'Bank Soal: Modul Ajar Persamaan Linear',
        ]);

        $this->assertDatabaseHas('questions', [
            'question_text' => 'Selesaikan persamaan 2x + 5 = 15.',
            'type' => 'essay',
        ]);
    }

    public function test_teacher_can_select_and_prefill_from_kemendikdasmen_cp_atp_bank(): void
    {
        $fixture = $this->createFixture();
        $this->actingAs($fixture['user']);

        // Create subject Bahasa Indonesia and class Grade 10
        $subject = Subject::create([
            'school_id' => $fixture['school']->id,
            'name' => 'Bahasa Indonesia',
            'code' => 'BIN-10',
            'order' => 1,
        ]);

        $classroom = Classroom::create([
            'school_id' => $fixture['school']->id,
            'academic_year_id' => $fixture['academicYear']->id,
            'name' => 'X-A',
            'grade_level' => '10',
            'order' => 1,
        ]);

        $schedule = Schedule::create([
            'school_id' => $fixture['school']->id,
            'academic_year_id' => $fixture['academicYear']->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $fixture['teacher']->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
            'order' => 1,
        ]);

        Livewire::test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $schedule->id)
            ->assertSet('detectedFase', 'Fase E (Kelas 10 SMA/SMK)')
            ->assertCount('availableBankTopics', 6)
            ->call('selectBankTopic', 'bindo-e-bab1')
            ->assertSet('topic', 'Teks Laporan Hasil Observasi (LHO)')
            ->assertSet('selectedLearningModel', 'Problem-Based Learning (PBL) & Pedagogi Genre')
            ->assertSet('selectedP5Dimensions', ['Bernalar Kritis', 'Gotong Royong', 'Mandiri'])
            ->assertSee('Teks Laporan Hasil Observasi');
    }

    public function test_teacher_can_duplicate_learning_draft_to_parallel_classes(): void
    {
        $fixture = $this->createFixture();
        $this->actingAs($fixture['user']);

        // Create parallel classrooms X-B and X-C for the same subject
        $classroomB = Classroom::create([
            'school_id' => $fixture['school']->id,
            'academic_year_id' => $fixture['academicYear']->id,
            'name' => 'X-B',
            'grade_level' => '10',
            'order' => 2,
        ]);

        $scheduleB = Schedule::create([
            'school_id' => $fixture['school']->id,
            'academic_year_id' => $fixture['academicYear']->id,
            'classroom_id' => $classroomB->id,
            'subject_id' => $fixture['subject']->id,
            'teacher_id' => $fixture['teacher']->id,
            'day_of_week' => 'Selasa',
            'start_time' => '09:30:00',
            'end_time' => '11:00:00',
            'order' => 2,
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($this->draftPayload())],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('documentType', 'modul_ajar')
            ->set('topic', 'Persamaan linear')
            ->set('learningObjectives', 'Tujuan pembelajaran')
            ->call('generate')
            ->call('saveDraft')
            ->call('openDuplicateModal')
            ->assertSet('showDuplicateModal', true)
            ->assertCount('parallelClassSchedules', 1)
            ->set('selectedTargetScheduleIds', [$scheduleB->id])
            ->call('duplicateToParallelClasses')
            ->assertSet('showDuplicateModal', false)
            ->assertHasNoErrors()
            ->assertSee('Berhasil menduplikasi modul ajar ke 1 kelas paralel');

        $this->assertDatabaseHas('learning_drafts', [
            'school_id' => $fixture['school']->id,
            'schedule_id' => $scheduleB->id,
            'source' => 'duplication',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function draftPayload(): array
    {
        return [
            'title' => 'Modul Ajar Persamaan Linear',
            'summary' => 'Draf pembelajaran untuk satu pertemuan.',
            'p5_dimensions' => ['Bernalar Kritis', 'Mandiri'],
            'learning_model' => 'Problem-Based Learning (PBL)',
            'meaningful_understanding' => 'Persamaan linear membantu memodelkan dan memecahkan persoalan penganggaran sehari-hari.',
            'inquiry_questions' => ['Bagaimana menentukan nilai yang belum diketahui dalam suatu situasi keuangan?'],
            'learning_objectives' => ['Siswa dapat menyelesaikan persamaan linear satu variabel.'],
            'activities' => [
                [
                    'stage' => 'Inti',
                    'duration_minutes' => 40,
                    'activity' => 'Diskusi dan latihan bertahap.',
                    'teacher_role' => 'Memfasilitasi diskusi.',
                    'student_role' => 'Menyelesaikan latihan.',
                ],
            ],
            'student_worksheet' => [
                'title' => 'LKPD Persamaan Linear',
                'instructions' => 'Kerjakan soal-soal berikut dengan teliti.',
                'tasks' => ['Selesaikan persamaan 2x + 5 = 15.'],
            ],
            'assessment' => [
                'diagnostic' => 'Pertanyaan pemantik.',
                'formative' => 'Observasi dan latihan.',
                'summative' => 'Tugas penyelesaian soal.',
            ],
            'assessment_rubric' => [
                [
                    'criteria' => 'Ketepatan Langkah',
                    'indicator' => 'Langkah penyelesaian aljabar runut dan benar.',
                    'scoring_guide' => 'Skor 100 bila tepat tanpa kesalahan.',
                ],
            ],
            'differentiation' => 'Berikan contoh bertahap sesuai kebutuhan kelas.',
            'resources' => ['Papan tulis', 'Lembar kerja'],
            'warnings' => ['CP/TP resmi perlu diverifikasi guru.'],
            'references' => [],
        ];
    }
}
