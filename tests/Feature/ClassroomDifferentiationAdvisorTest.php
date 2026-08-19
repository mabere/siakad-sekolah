<?php

namespace Tests\Feature;

use App\Livewire\Teacher\ClassroomDifferentiationIndex;
use App\Livewire\Teacher\LearningAssistant;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ClassroomDifferentiationAdvisorTest extends TestCase
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

    public function test_teacher_can_open_differentiation_page(): void
    {
        $fixture = $this->createFixture();

        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.differentiation'))
            ->assertOk()
            ->assertSee('Rekomendasi Diferensiasi Pengajaran AI')
            ->assertSee($fixture['classroom']->name);
    }

    public function test_teacher_can_generate_differentiation_recommendation_from_class_statistics(): void
    {
        $fixture = $this->createFixture();

        // Create sample students, grades, and attendances for the classroom
        for ($i = 1; $i <= 5; $i++) {
            $student = Student::create([
                'school_id' => $fixture['school']->id,
                'classroom_id' => $fixture['classroom']->id,
                'nis' => '100'.$i,
                'nisn' => '001000'.$i,
                'name' => 'Siswa '.$i,
                'gender' => $i % 2 === 0 ? 'P' : 'L',
                'status' => 'Aktif',
            ]);

            Grade::create([
                'school_id' => $fixture['school']->id,
                'academic_year_id' => $fixture['academic_year']->id,
                'classroom_id' => $fixture['classroom']->id,
                'subject_id' => $fixture['subject']->id,
                'student_id' => $student->id,
                'tugas' => 70 + ($i * 5),
                'uts' => 65 + ($i * 5),
                'uas' => 70 + ($i * 4),
                'final_score' => 68 + ($i * 5),
                'tp_highest' => 'Mengevaluasi informasi teks LHO',
                'tp_lowest' => 'Menulis struktur kalimat definisi',
            ]);

            Attendance::create([
                'school_id' => $fixture['school']->id,
                'academic_year_id' => $fixture['academic_year']->id,
                'classroom_id' => $fixture['classroom']->id,
                'student_id' => $student->id,
                'date' => now()->subDays($i)->toDateString(),
                'status' => $i === 1 ? 'Sakit' : 'Hadir',
            ]);
        }

        $differentiationResponse = [
            'classroom_summary' => 'Kelas X-A memiliki variasi kemampuan sedang dengan 20% siswa memerlukan bimbingan awal pada aspek struktur teks.',
            'readiness_level_distribution' => [
                'scaffolding_percentage' => '20%',
                'regular_percentage' => '60%',
                'advanced_percentage' => '20%',
            ],
            'recommended_learning_models' => [
                'Problem-Based Learning (PBL)',
                'Diferensiasi Berbasis Stasiun',
            ],
            'differentiation_content' => [
                'strategy' => 'Penyediaan teks observasi bertingkat dan infografis pendukung.',
                'for_scaffolding' => 'Teks ringkas dengan penanda warna pada kalimat definisi.',
                'for_regular' => 'Teks observasi standar lengkap dengan data saintifik.',
                'for_advanced' => 'Jurnal ilmiah populer bertema biodiversitas lokal.',
            ],
            'differentiation_process' => [
                'strategy' => 'Scaffolding bertahap dengan model stasiun belajar mandiri.',
                'for_scaffolding' => 'Bimbingan terfokus di meja guru dengan kalimat pemandu.',
                'for_regular' => 'Kerja kelompok mandiri menganalisis struktur wacana.',
                'for_advanced' => 'Investigasi mandiri merumuskan klasifikasi objektif.',
            ],
            'differentiation_product' => [
                'strategy' => 'Kebebasan memilih format laporan observasi akhir.',
                'options' => [
                    [
                        'target_group' => 'Perlu Bimbingan',
                        'product_type' => 'Poster / Infografis',
                        'description' => 'Menyajikan struktur LHO dalam bentuk diagram visual berlabel.',
                    ],
                    [
                        'target_group' => 'Reguler & Pengayaan',
                        'product_type' => 'Artikel Laporan Utuh',
                        'description' => 'Menyusun laporan teks LHO lengkap 4 paragraf padu.',
                    ],
                ],
            ],
            'student_grouping' => [
                'scaffolding_group' => [
                    'title' => 'Kelompok 1: Fondasi Berbimbing',
                    'characteristics' => 'Nilai < 75, memerlukan bantuan membedakan fakta dan opini.',
                    'teacher_intervention' => 'Berikan lembar panduan kata kerja relasional dan kalimat definisi.',
                    'sample_tasks' => ['Temukan 3 kata definisi dalam teks model'],
                ],
                'regular_group' => [
                    'title' => 'Kelompok 2: Reguler Mandiri',
                    'characteristics' => 'Nilai 75-84, menguasai struktur dasar dengan baik.',
                    'teacher_intervention' => 'Fasilitasi diskusi rekan sejawat dan verifikasi fakta.',
                    'sample_tasks' => ['Susun kerangka klasifikasi observasi'],
                ],
                'advanced_group' => [
                    'title' => 'Kelompok 3: Eksplorasi HOTS',
                    'characteristics' => 'Nilai >= 85, cepat memahami konsep dan analitis.',
                    'teacher_intervention' => 'Beri tantangan meneliti isu lingkungan kompleks.',
                    'sample_tasks' => ['Sintesis 2 laporan menjadi ulasan komparatif'],
                ],
            ],
            'pedagogical_action_plan' => [
                [
                    'step_number' => 1,
                    'action_title' => 'Asesmen Diagnostik Singkat Awal KBM',
                    'teacher_action' => 'Gunakan kuis lisan 3 menit untuk memetakan kelompok kesiapan.',
                    'expected_outcome' => 'Siswa langsung terarah ke stasiun belajar yang tepat.',
                ],
                [
                    'step_number' => 2,
                    'action_title' => 'Rotasi Stasiun & Scaffolding Terfokus',
                    'teacher_action' => 'Guru mendampingi kelompok 1 selama 15 menit pertama.',
                    'expected_outcome' => 'Kelompok 1 percaya diri menyelesaikan draf awal.',
                ],
            ],
            'assessment_tips' => [
                'Gunakan tiket keluar (exit ticket) 1 menit di akhir sesi.',
            ],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($differentiationResponse, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(ClassroomDifferentiationIndex::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->call('generateRecommendation')
            ->assertHasNoErrors()
            ->assertSet('recommendation.classroom_summary', 'Kelas X-A memiliki variasi kemampuan sedang dengan 20% siswa memerlukan bimbingan awal pada aspek struktur teks.')
            ->assertSee('Diferensiasi Konten')
            ->assertSee('Kelompok 1: Fondasi Berbimbing');
    }

    public function test_teacher_can_apply_differentiation_to_learning_assistant(): void
    {
        $fixture = $this->createFixture();

        $differentiationResponse = [
            'classroom_summary' => 'Analisis ringkas kelas.',
            'readiness_level_distribution' => [
                'scaffolding_percentage' => '20%',
                'regular_percentage' => '60%',
                'advanced_percentage' => '20%',
            ],
            'recommended_learning_models' => ['Problem-Based Learning (PBL)'],
            'differentiation_content' => [
                'strategy' => 'Konten visual dan bertingkat',
                'for_scaffolding' => 'Infografis',
                'for_regular' => 'Teks standar',
                'for_advanced' => 'Jurnal ilmiah',
            ],
            'differentiation_process' => [
                'strategy' => 'Scaffolding guru dan tutor sebaya',
                'for_scaffolding' => 'Bimbingan meja guru',
                'for_regular' => 'Diskusi kelompok',
                'for_advanced' => 'Investigasi mandiri',
            ],
            'differentiation_product' => [
                'strategy' => 'Pilihan produk poster atau esai',
                'options' => [
                    ['target_group' => 'Semua', 'product_type' => 'Poster', 'description' => 'Poster ringkas'],
                ],
            ],
            'student_grouping' => [
                'scaffolding_group' => [
                    'title' => 'Kelompok 1',
                    'characteristics' => 'Perlu bimbingan',
                    'teacher_intervention' => 'Bimbingan intensif kalimat pembuka',
                    'sample_tasks' => ['Tugas 1'],
                ],
                'regular_group' => [
                    'title' => 'Kelompok 2',
                    'characteristics' => 'Reguler',
                    'teacher_intervention' => 'Tutor sebaya',
                    'sample_tasks' => ['Tugas 2'],
                ],
                'advanced_group' => [
                    'title' => 'Kelompok 3',
                    'characteristics' => 'Mahir',
                    'teacher_intervention' => 'Pengayaan mandiri',
                    'sample_tasks' => ['Tugas 3'],
                ],
            ],
            'pedagogical_action_plan' => [
                [
                    'step_number' => 1,
                    'action_title' => 'Apersepsi',
                    'teacher_action' => 'Tanya jawab pemantik',
                    'expected_outcome' => 'Motivasi terbangun',
                ],
            ],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($differentiationResponse, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(ClassroomDifferentiationIndex::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->call('generateRecommendation')
            ->call('applyToLearningAssistant')
            ->assertRedirect(route('guru.learning-assistant', ['from_differentiation' => 1]));

        // Check that LearningAssistant adopts the prefilled differentiation context
        Livewire::actingAs($fixture['user'])
            ->test(LearningAssistant::class)
            ->assertSet('selectedScheduleId', (string) $fixture['schedule']->id)
            ->assertSet('selectedLearningModel', 'Problem-Based Learning (PBL)')
            ->assertSet('studentNeeds', 'Kebutuhan Belajar Kelas: Bimbingan intensif kalimat pembuka')
            ->assertSet('additionalContext', fn (string $val): bool => str_contains($val, '[Rekomendasi Diferensiasi AI]'))
            ->assertSee('Rekomendasi Diferensiasi AI Berhasil Diterapkan');
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
            'student_needs' => 'Siswa kinestetik dan visual',
            'available_facilities' => 'Laboratorium Komputer dan Proyektor',
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
