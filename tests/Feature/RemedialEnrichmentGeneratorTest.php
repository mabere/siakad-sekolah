<?php

namespace Tests\Feature;

use App\Livewire\Teacher\RemedialEnrichmentIndex;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Grade;
use App\Models\QuestionBank;
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

class RemedialEnrichmentGeneratorTest extends TestCase
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

    public function test_teacher_can_open_remedial_enrichment_page(): void
    {
        $fixture = $this->createFixture();

        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.remedial-enrichment'))
            ->assertOk()
            ->assertSee('Generator Lembar Kerja Remedial')
            ->assertSee($fixture['classroom']->name);
    }

    public function test_teacher_can_generate_remedial_and_enrichment_packages_from_assessment_data(): void
    {
        $fixture = $this->createFixture();

        // Create sample students and grades
        for ($i = 1; $i <= 4; $i++) {
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
                'tugas' => $i <= 2 ? 60 : 90,
                'uts' => $i <= 2 ? 65 : 88,
                'uas' => $i <= 2 ? 60 : 92,
                'final_score' => $i <= 2 ? 62 : 90,
            ]);
        }

        $mockResponse = [
            'analysis_summary' => [
                'root_cause_analysis' => 'Siswa kesulitan mengidentifikasi variabel bebas dan terikat pada soal cerita.',
                'misconceptions_identified' => [
                    'Menganggap semua koefisien bernilai positif',
                    'Keliru dalam eliminasi variabel kedua',
                ],
                'intervention_strategy' => 'Gunakan metode substitusi visual bertahap dengan penanda warna.',
            ],
            'remedial_package' => [
                'title' => 'Penguatan Konsep Sistem Persamaan Linear',
                'target_competency' => 'Menyelesaikan SPLTV dengan metode substitusi terarah',
                'concept_recap' => 'Sistem persamaan linear adalah kumpulan persamaan yang memiliki variabel berpangkat satu.',
                'worked_example' => [
                    'problem_statement' => 'Tentukan nilai x dari sistem: x + y = 5 dan x - y = 1',
                    'step_by_step_solution' => [
                        'Langkah 1: Ubah persamaan pertama menjadi x = 5 - y',
                        'Langkah 2: Substitusikan ke persamaan kedua: (5 - y) - y = 1',
                        'Langkah 3: 5 - 2y = 1 => 2y = 4 => y = 2',
                        'Langkah 4: Cari nilai x: x = 5 - 2 = 3',
                    ],
                    'key_takeaway' => 'Selalu periksa kembali nilai yang diperoleh ke dalam persamaan awal.',
                ],
                'practice_items' => [
                    [
                        'item_number' => 1,
                        'question_text' => 'Jika 2x + 3 = 11, berapakah nilai x?',
                        'type' => 'pg',
                        'options' => ['A. 2', 'B. 3', 'C. 4', 'D. 5'],
                        'hint' => 'Kurangkan kedua ruas dengan 3 terlebih dahulu.',
                        'answer_key' => 'C',
                        'explanation' => '2x = 8, maka x = 4.',
                    ],
                ],
                'teacher_scaffolding_guide' => 'Bimbing siswa menuliskan setiap operasi di kedua ruas secara eksplisit.',
            ],
            'enrichment_package' => [
                'title' => 'Tantangan Pemodelan Optimasi Produksi UMKM',
                'target_competency' => 'Merumuskan dan memecahkan model SPLTV dalam optimasi ekonomi nyata',
                'real_world_case' => 'Sebuah usaha konveksi memproduksi tiga jenis pakaian dengan kendala bahan baku kain katun, kancing, dan waktu penjahitan...',
                'hots_items' => [
                    [
                        'item_number' => 1,
                        'question_text' => 'Analisislah kombinasi produksi terbaik jika pasokan kain katun mengalami penurunan 20%!',
                        'cognitive_level' => 'C5-Evaluasi',
                        'expected_response_guide' => 'Siswa mengevaluasi sistem persamaan baru dan menyimpulkan prioritas produk dengan margin tertinggi.',
                    ],
                ],
                'mini_project_prompt' => [
                    'project_title' => 'Mini Riset: Efisiensi Biaya Toko Kelontong Sekitar Sekolah',
                    'instructions' => 'Wawancarai pemilik toko dan rumuskan sistem persamaan biaya operasional bulanan.',
                    'estimated_duration' => '1 Minggu',
                    'deliverable_product' => 'Laporan Analisis 2 Halaman & Infografis',
                ],
                'scoring_rubric' => [
                    [
                        'criteria' => 'Ketepatan Model Matematika',
                        'indicator' => 'Merumuskan 3 persamaan lengkap dan akurat dari data wawancara',
                        'score_range' => '85 - 100',
                    ],
                ],
            ],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($mockResponse, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(RemedialEnrichmentIndex::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Sistem Persamaan Linear Tiga Variabel')
            ->call('generatePackage')
            ->assertHasNoErrors()
            ->assertSet('package.remedial_package.title', 'Penguatan Konsep Sistem Persamaan Linear')
            ->assertSet('package.enrichment_package.title', 'Tantangan Pemodelan Optimasi Produksi UMKM')
            ->assertSee('Lembar Kerja Remedial')
            ->assertSee('Tentukan nilai x dari sistem');
    }

    public function test_teacher_can_export_remedial_items_to_cbt_question_bank(): void
    {
        $fixture = $this->createFixture();

        $mockResponse = [
            'analysis_summary' => [
                'root_cause_analysis' => 'Analisis singkat.',
                'misconceptions_identified' => ['Miskonsepsi 1'],
                'intervention_strategy' => 'Strategi 1',
            ],
            'remedial_package' => [
                'title' => 'Remedial Aljabar',
                'target_competency' => 'Aljabar dasar',
                'concept_recap' => 'Rangkuman aljabar',
                'worked_example' => [
                    'problem_statement' => 'Contoh soal',
                    'step_by_step_solution' => ['Langkah 1'],
                    'key_takeaway' => 'Takeaway',
                ],
                'practice_items' => [
                    [
                        'item_number' => 1,
                        'question_text' => 'Berapakah 2x jika x = 5?',
                        'type' => 'pg',
                        'options' => ['A. 10', 'B. 8', 'C. 12', 'D. 15'],
                        'hint' => 'Kalikan 2 dengan 5',
                        'answer_key' => 'A',
                        'explanation' => '2 * 5 = 10',
                    ],
                ],
                'teacher_scaffolding_guide' => 'Panduan guru',
            ],
            'enrichment_package' => [
                'title' => 'Pengayaan Aljabar',
                'target_competency' => 'Aljabar lanjut',
                'real_world_case' => 'Studi kasus nyata',
                'hots_items' => [
                    [
                        'item_number' => 1,
                        'question_text' => 'Soal HOTS 1',
                        'cognitive_level' => 'C4-Analisis',
                        'expected_response_guide' => 'Panduan 1',
                    ],
                ],
                'mini_project_prompt' => [
                    'project_title' => 'Projek 1',
                    'instructions' => 'Instruksi',
                    'deliverable_product' => 'Produk',
                ],
                'scoring_rubric' => [
                    [
                        'criteria' => 'Kriteria 1',
                        'indicator' => 'Indikator 1',
                        'score_range' => '80-100',
                    ],
                ],
            ],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($mockResponse, JSON_UNESCAPED_UNICODE)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($fixture['user'])
            ->test(RemedialEnrichmentIndex::class)
            ->set('selectedScheduleId', (string) $fixture['schedule']->id)
            ->set('topic', 'Aljabar Linear')
            ->call('generatePackage')
            ->call('exportRemedialToCbt')
            ->assertHasNoErrors()
            ->assertSee('Butir soal remedial berhasil diekspor');

        $this->assertDatabaseHas('question_banks', [
            'school_id' => $fixture['school']->id,
            'teacher_id' => $fixture['teacher']->id,
            'title' => 'Soal Remedial: Remedial Aljabar',
        ]);

        $this->assertDatabaseHas('questions', [
            'question_text' => 'Berapakah 2x jika x = 5?',
            'correct_answer' => 'A',
            'type' => 'pg',
        ]);
    }

    public function test_teacher_can_print_and_export_word_for_remedial_and_enrichment(): void
    {
        $fixture = $this->createFixture();

        $packageData = [
            'package' => [
                'analysis_summary' => [
                    'root_cause_analysis' => 'Analisis ringkas.',
                    'misconceptions_identified' => ['Miskonsepsi 1'],
                    'intervention_strategy' => 'Strategi 1',
                ],
                'remedial_package' => [
                    'title' => 'Remedial Eksponensial',
                    'target_competency' => 'Fungsi eksponen dasar',
                    'concept_recap' => 'Sifat perkalian pangkat',
                    'worked_example' => [
                        'problem_statement' => 'Selesaikan 2^3 * 2^2',
                        'step_by_step_solution' => ['Jumlahkan pangkat: 3 + 2 = 5', '2^5 = 32'],
                        'key_takeaway' => 'a^m * a^n = a^(m+n)',
                    ],
                    'practice_items' => [
                        [
                            'item_number' => 1,
                            'question_text' => 'Sederhanakan 3^2 * 3^3',
                            'type' => 'pg',
                            'options' => ['A. 3^5', 'B. 3^6', 'C. 9^5', 'D. 9^6'],
                            'hint' => 'Gunakan aturan penjumlahan eksponen',
                            'answer_key' => 'A',
                            'explanation' => '2 + 3 = 5',
                        ],
                    ],
                    'teacher_scaffolding_guide' => 'Beri analogi perkalian berulang',
                ],
                'enrichment_package' => [
                    'title' => 'Pengayaan Model Pertumbuhan Bakteri',
                    'target_competency' => 'Aplikasi eksponensial dalam bioteknologi',
                    'real_world_case' => 'Kultur bakteri membelah diri setiap 20 menit...',
                    'hots_items' => [
                        [
                            'item_number' => 1,
                            'question_text' => 'Estimasi populasi setelah 4 jam dengan asumsi 5% mengalami lisis',
                            'cognitive_level' => 'C5-Evaluasi',
                            'expected_response_guide' => 'Model diferensial terdiskritkan',
                        ],
                    ],
                    'mini_project_prompt' => [
                        'project_title' => 'Simulasi Pertumbuhan Ragi',
                        'instructions' => 'Lakukan pengamatan mandiri',
                        'deliverable_product' => 'Tabel data & grafik',
                    ],
                    'scoring_rubric' => [
                        [
                            'criteria' => 'Akurasi Model',
                            'indicator' => 'Model tepat',
                            'score_range' => '90-100',
                        ],
                    ],
                ],
            ],
            'topic' => 'Eksponen dan Logaritma',
            'subject' => 'Matematika',
            'classroom' => 'X-A',
            'remedial_students' => [],
            'enrichment_students' => [],
        ];

        // Print Remedial
        $this->actingAs($fixture['user'])
            ->withSession([
                'active_role' => 'Guru',
                'remedial_enrichment_active_package' => $packageData,
            ])
            ->get(route('guru.remedial-enrichment.print', ['type' => 'remedial']))
            ->assertOk()
            ->assertSee('LEMBAR KERJA REMEDIAL')
            ->assertSee('Sifat perkalian pangkat');

        // Print Enrichment
        $this->actingAs($fixture['user'])
            ->withSession([
                'active_role' => 'Guru',
                'remedial_enrichment_active_package' => $packageData,
            ])
            ->get(route('guru.remedial-enrichment.print', ['type' => 'enrichment']))
            ->assertOk()
            ->assertSee('LEMBAR KERJA PENGAYAAN')
            ->assertSee('Kultur bakteri membelah diri');

        // Export Word Remedial
        $this->actingAs($fixture['user'])
            ->withSession([
                'active_role' => 'Guru',
                'remedial_enrichment_active_package' => $packageData,
            ])
            ->get(route('guru.remedial-enrichment.export-word', ['type' => 'remedial']))
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
            'name' => 'SMA Alam Gemilang',
            'level' => 'SMA',
            'status' => 'SWASTA',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        $user = User::create([
            'name' => 'Ahmad Guru',
            'email' => 'ahmad.'.uniqid().'@test.com',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole('Guru');

        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'nip' => '198901012015011002',
            'name' => 'Ahmad Guru, S.Pd.',
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
            'name' => 'Matematika',
            'code' => 'MTK-'.uniqid(),
            'type' => 'Wajib',
        ]);

        $schedule = Schedule::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'Selasa',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
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
