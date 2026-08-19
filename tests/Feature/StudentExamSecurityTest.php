<?php

namespace Tests\Feature;

use App\Livewire\Student\Exams as StudentExams;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StudentExamSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_payload_does_not_expose_answer_key_and_score_uses_database_key(): void
    {
        $fixture = $this->createFixture();

        $component = Livewire::actingAs($fixture['studentUser'])->test(StudentExams::class)
            ->call('startExam', $fixture['exam']->id);

        $questions = $component->get('questionsList');
        $this->assertNotEmpty($questions);
        $this->assertArrayNotHasKey('correct_answer', $questions[0]);

        // Simulate a browser tampering with a client-side copy of the question.
        $component
            ->set('questionsList.0.correct_answer', 'a')
            ->call('selectAnswer', $fixture['question']->id, 'a')
            ->call('submitExam');

        $submission = ExamSubmission::query()->firstOrFail();
        $this->assertSame('Selesai', $submission->status);
        $this->assertSame(0, $submission->total_correct);
        $this->assertSame(0.0, (float) $submission->score);
    }

    public function test_expired_attempt_is_finalized_using_server_time_and_server_questions(): void
    {
        $fixture = $this->createFixture();

        $component = Livewire::actingAs($fixture['studentUser'])->test(StudentExams::class)
            ->call('startExam', $fixture['exam']->id)
            ->call('selectAnswer', $fixture['question']->id, 'b');

        $submission = ExamSubmission::query()->firstOrFail();
        $submission->update(['started_at' => now()->subMinutes(61)]);

        $component->call('submitExam');

        $submission->refresh();
        $this->assertSame('Selesai', $submission->status);
        $this->assertNotNull($submission->submitted_at);
        $this->assertSame(1, $submission->total_correct);
        $this->assertSame(100.0, (float) $submission->score);
    }

    /** @return array{studentUser: User, exam: Exam, question: Question} */
    private function createFixture(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $school = School::create([
            'name' => 'Sekolah CBT Test',
            'level' => 'SMA',
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
        $studentUser = User::create([
            'name' => 'Siswa CBT',
            'email' => 'siswa-cbt@test.local',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $studentUser->assignRole('Siswa');
        $teacherUser = User::create([
            'name' => 'Guru CBT',
            'email' => 'guru-cbt@test.local',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $teacherUser->assignRole('Guru');
        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $teacherUser->id,
            'name' => 'Guru CBT',
            'gender' => 'L',
            'is_active' => true,
        ]);
        $classroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'name' => 'X IPA 1',
            'grade_level' => 'X',
        ]);
        $student = Student::create([
            'school_id' => $school->id,
            'user_id' => $studentUser->id,
            'classroom_id' => $classroom->id,
            'name' => 'Siswa CBT',
            'gender' => 'L',
            'status' => 'Aktif',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Matematika',
            'code' => 'MTK',
            'type' => 'Wajib',
        ]);
        $questionBank = QuestionBank::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Bank Soal CBT',
            'code' => 'CBT-001',
        ]);
        $question = Question::create([
            'question_bank_id' => $questionBank->id,
            'type' => 'pg',
            'question_text' => 'Berapakah 1 + 1?',
            'options' => ['a' => '3', 'b' => '2'],
            'correct_answer' => 'b',
            'score_weight' => 1,
        ]);
        $exam = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'question_bank_id' => $questionBank->id,
            'title' => 'Ujian CBT Matematika',
            'duration_minutes' => 60,
            'randomize_questions' => false,
            'status' => 'Aktif',
        ]);

        return [
            'studentUser' => $studentUser,
            'exam' => $exam,
            'question' => $question,
        ];
    }
}
