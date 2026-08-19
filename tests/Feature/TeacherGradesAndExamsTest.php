<?php

namespace Tests\Feature;

use App\Livewire\Teacher\Exams;
use App\Livewire\Teacher\Grades;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\FullSemesterLearningSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherGradesAndExamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_grades_page_with_persisted_url_schedule(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        $teacherUser = User::where('email', 'guru.biologi@siakad.test')->first();
        $schedule = Schedule::first();

        $this->actingAs($teacherUser)
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.grades', ['schedule' => $schedule->id]))
            ->assertOk()
            ->assertSee('Input Nilai')
            ->assertSee('Evaluasi Pembelajaran')
            ->assertSee('Biologi (Fase E)')
            ->assertSee('X-1')
            ->assertSee('Aditya Pratama')
            ->assertSee('Simpan Semua Nilai');
    }

    public function test_teacher_can_save_all_grades_simultaneously(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        $teacherUser = User::where('email', 'guru.biologi@siakad.test')->first();
        $schedule = Schedule::first();
        $students = Student::where('classroom_id', $schedule->classroom_id)->get();

        session(['active_role' => 'Guru']);

        $component = Livewire::actingAs($teacherUser)
            ->test(Grades::class, ['selectedScheduleId' => (string) $schedule->id]);

        // Modify grades for first 2 students
        $component->set('gradeData.'.$students[0]->id.'.tugas', 90)
            ->set('gradeData.'.$students[0]->id.'.uts', 90)
            ->set('gradeData.'.$students[0]->id.'.uas', 90)
            ->set('gradeData.'.$students[1]->id.'.tugas', 80)
            ->set('gradeData.'.$students[1]->id.'.uts', 80)
            ->set('gradeData.'.$students[1]->id.'.uas', 80);

        $component->call('saveAllGrades')
            ->assertHasNoErrors()
            ->assertSee('Semua nilai siswa (25 siswa) telah tersimpan permanen');

        // Assert database persistence
        $grade0 = Grade::where('student_id', $students[0]->id)->where('subject_id', $schedule->subject_id)->first();
        $this->assertNotNull($grade0);
        $this->assertEquals(90.0, (float) $grade0->final_score);
        $this->assertEquals('A', $grade0->grade_letter);

        $grade1 = Grade::where('student_id', $students[1]->id)->where('subject_id', $schedule->subject_id)->first();
        $this->assertNotNull($grade1);
        $this->assertEquals(80.0, (float) $grade1->final_score);
        $this->assertEquals('B', $grade1->grade_letter);
    }

    public function test_teacher_can_lock_and_unlock_grades(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        $teacherUser = User::where('email', 'guru.biologi@siakad.test')->first();
        $schedule = Schedule::first();

        session(['active_role' => 'Guru']);

        $component = Livewire::actingAs($teacherUser)
            ->test(Grades::class, ['selectedScheduleId' => (string) $schedule->id]);

        // 1. Lock Grades
        $component->call('toggleLockGrades')
            ->assertSee('Nilai kelas berhasil dikunci');

        $this->assertTrue((bool) Grade::where('classroom_id', $schedule->classroom_id)->first()->is_locked);

        // 2. Attempt save while locked -> blocked
        $component->call('saveAllGrades')
            ->assertSee('Nilai sedang dikunci');

        // 3. Unlock Grades
        $component->call('toggleLockGrades')
            ->assertSee('Kunci nilai berhasil dibuka');

        $this->assertFalse((bool) Grade::where('classroom_id', $schedule->classroom_id)->first()->is_locked);
    }

    public function test_teacher_exams_component_supports_submissions_page_navigation(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        $teacherUser = User::where('email', 'guru.biologi@siakad.test')->first();

        session(['active_role' => 'Guru']);

        $component = Livewire::actingAs($teacherUser)
            ->test(Exams::class)
            ->set('activeTab', 'submissions')
            ->assertSee('Daftar Lembar Jawaban Ujian Siswa');

        // Navigate to Page 2 of submissions without route errors
        $component->call('gotoPage', 2, 'submissionsPage')
            ->assertHasNoErrors()
            ->assertOk();
    }

    public function test_teacher_can_configure_custom_letter_score_thresholds_and_presets(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        $teacherUser = User::where('email', 'guru.biologi@siakad.test')->first();
        $schedule = Schedule::first();

        session(['active_role' => 'Guru']);

        $component = Livewire::actingAs($teacherUser)
            ->test(Grades::class, ['selectedScheduleId' => (string) $schedule->id]);

        // Apply KKTP 75 Preset
        $component->call('applyGradeScalePreset', 'kktp_75')
            ->assertSet('minScoreA', 92)
            ->assertSet('minScoreB', 83)
            ->assertSet('minScoreC', 75)
            ->assertSet('minScoreD', 65);

        // Save Weights & Scales
        $component->call('saveWeights')
            ->assertHasNoErrors()
            ->assertSee('Pengaturan bobot')
            ->assertSee('A: ≥92')
            ->assertSee('B: ≥83')
            ->assertSee('C: ≥75')
            ->assertSee('D: ≥65');

        // Verify database persistence
        $this->assertDatabaseHas('grade_weights', [
            'classroom_id' => $schedule->classroom_id,
            'subject_id' => $schedule->subject_id,
            'min_score_a' => 92,
            'min_score_b' => 83,
            'min_score_c' => 75,
            'min_score_d' => 65,
        ]);
    }
}
