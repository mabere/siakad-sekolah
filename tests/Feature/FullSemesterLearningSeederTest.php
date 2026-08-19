<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\CurriculumTarget;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Grade;
use App\Models\LearningDraft;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\User;
use Database\Seeders\FullSemesterLearningSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullSemesterLearningSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_semester_learning_seeder_creates_all_demo_data_properly(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        // 1. Verify Users & Roles
        $guruUser = User::where('email', 'guru.biologi@siakad.test')->first();
        $this->assertNotNull($guruUser);
        $this->assertTrue($guruUser->hasRole('Guru'));
        $this->assertTrue($guruUser->hasRole('Wali Kelas'));

        $kepsekUser = User::where('email', 'kepsek@siakad.test')->first();
        $this->assertNotNull($kepsekUser);
        $this->assertTrue($kepsekUser->hasRole('Kepala Sekolah'));

        $wakasekUser = User::where('email', 'wakasek.kurikulum@siakad.test')->first();
        $this->assertNotNull($wakasekUser);
        $this->assertTrue($wakasekUser->hasRole('Wakasek Kurikulum'));

        // 2. Verify School, Academic Year, Classroom, Subject, Schedule
        $school = School::where('name', 'SMA Merdeka Nusantara')->first();
        $this->assertNotNull($school);
        $this->assertTrue($school->is_setup_completed);

        $academicYear = AcademicYear::where('school_id', $school->id)->where('is_active', true)->first();
        $this->assertNotNull($academicYear);
        $this->assertEquals('MERDEKA', $academicYear->curriculum_type);

        $classroom = Classroom::where('school_id', $school->id)->where('name', 'X-1')->first();
        $this->assertNotNull($classroom);
        $this->assertEquals(10, $classroom->grade_level);

        $subject = Subject::where('school_id', $school->id)->where('code', 'BIO-10')->first();
        $this->assertNotNull($subject);

        $schedule = Schedule::where('school_id', $school->id)
            ->where('classroom_id', $classroom->id)
            ->where('subject_id', $subject->id)
            ->first();
        $this->assertNotNull($schedule);

        // 3. Verify 25 Students
        $this->assertEquals(25, Student::where('classroom_id', $classroom->id)->count());

        // 4. Verify 16 Teaching Journals
        $this->assertEquals(16, TeachingJournal::where('schedule_id', $schedule->id)->count());

        // 5. Verify 400 Subject Attendances (16 x 25)
        $this->assertEquals(400, SubjectAttendance::where('schedule_id', $schedule->id)->count());

        // 6. Verify 25 Class Attendance Summary
        $this->assertEquals(25, Attendance::where('classroom_id', $classroom->id)->count());

        // 7. Verify 25 Grades with Remedial (<75) & Enrichment (>=85) distribution
        $grades = Grade::where('classroom_id', $classroom->id)->where('subject_id', $subject->id)->get();
        $this->assertCount(25, $grades);
        $this->assertEquals(5, $grades->where('final_score', '<', 75)->count());
        $this->assertEquals(6, $grades->where('final_score', '>=', 85)->count());

        // 8. Verify QuestionBank, Questions, Exam, and Exam Submissions
        $questionBank = QuestionBank::where('code', 'BIO-X-STS')->first();
        $this->assertNotNull($questionBank);
        $this->assertEquals(5, $questionBank->questions()->count());

        $exam = Exam::where('classroom_id', $classroom->id)->first();
        $this->assertNotNull($exam);
        $this->assertEquals('Aktif', $exam->status);
        $this->assertEquals(25, ExamSubmission::where('exam_id', $exam->id)->count());

        // 9. Verify 4 Curriculum Targets
        $this->assertEquals(4, CurriculumTarget::where('school_id', $school->id)->count());

        // 10. Verify Approved Learning Draft
        $learningDraft = LearningDraft::where('schedule_id', $schedule->id)->first();
        $this->assertNotNull($learningDraft);
        $this->assertEquals('approved', $learningDraft->status);
        $this->assertEquals($kepsekUser->id, $learningDraft->approved_by);
    }

    public function test_seeded_teacher_can_login_and_access_all_learning_modules(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        $guruUser = User::where('email', 'guru.biologi@siakad.test')->first();

        // 1. Dashboard Guru
        $this->actingAs($guruUser)
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('Pusat Otomasi Administrasi')
            ->assertSee('Generator 7 Dokumen AI');

        // 2. Perangkat Pembelajaran AI (Learning Assistant)
        $this->actingAs($guruUser)
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.learning-assistant'))
            ->assertOk()
            ->assertSee('Biologi (Fase E)')
            ->assertSee('X-1')
            ->assertSee('Modul Ajar (RPP+ Berdiferensiasi)')
            ->assertSee('Disetujui');

        // 3. Rekomendasi Diferensiasi AI
        $this->actingAs($guruUser)
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.differentiation'))
            ->assertOk()
            ->assertSee('Rekomendasi Diferensiasi Pengajaran AI')
            ->assertSee('Biologi (Fase E)')
            ->assertSee('X-1');

        // 4. Remedial & Pengayaan AI
        $this->actingAs($guruUser)
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.remedial-enrichment'))
            ->assertOk()
            ->assertSee('Generator Lembar Kerja Remedial')
            ->assertSee('Biologi (Fase E)')
            ->assertSee('X-1');

        // 5. Panduan Perangkat AI
        $this->actingAs($guruUser)
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.learning-guide'))
            ->assertOk()
            ->assertSee('Panduan Khusus Peran Anda')
            ->assertSee('Panduan Langkah Praktis untuk Guru Pengampu');
    }

    public function test_seeded_kepsek_can_login_and_access_curriculum_guide(): void
    {
        $this->seed(FullSemesterLearningSeeder::class);

        $kepsekUser = User::where('email', 'kepsek@siakad.test')->first();

        $this->actingAs($kepsekUser)
            ->withSession(['active_role' => 'Kepala Sekolah'])
            ->get(route('kepsek.academic.curriculum-guide'))
            ->assertOk()
            ->assertSee('Panduan Khusus Peran Anda')
            ->assertSee('Pengesahan Dokumen Cetak Ber-Kop Resmi')
            ->assertDontSee('Konfigurasi Google Gemini AI Provider');
    }
}
