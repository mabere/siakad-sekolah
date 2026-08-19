<?php

namespace Tests\Feature;

use App\Livewire\Teacher\Attendances;
use App\Livewire\Teacher\Grades;
use App\Livewire\Teacher\Journals;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
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

class AcademicRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_management_users_cannot_open_the_legacy_grade_input_route(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUser($school, 'Admin Sekolah', 'admin-academic@siakad.test');

        $this->actingAs($admin)
            ->withSession(['active_role' => 'Admin Sekolah'])
            ->get('/admin/academic/grades/input/1/1')
            ->assertNotFound();
    }

    public function test_management_attendance_screen_is_read_only(): void
    {
        $school = $this->createSchool();
        AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $admin = $this->createUser($school, 'Admin Sekolah', 'admin-attendance@siakad.test');

        $this->actingAs($admin)
            ->withSession(['active_role' => 'Admin Sekolah'])
            ->get('/admin/academic/attendances')
            ->assertOk()
            ->assertSee('Mode monitoring')
            ->assertDontSee('Simpan Presensi')
            ->assertDontSee('wire:click="saveAttendances"');
    }

    public function test_teacher_role_without_teacher_profile_cannot_open_teacher_input_portal(): void
    {
        $school = $this->createSchool();
        $user = $this->createUser($school, 'Guru', 'unlinked-teacher@siakad.test');

        $this->actingAs($user)
            ->withSession(['active_role' => 'Guru'])
            ->get('/guru/grades')
            ->assertForbidden();
    }

    public function test_non_guru_teaching_roles_cannot_open_teacher_operational_inputs(): void
    {
        $school = $this->createSchool();
        $user = $this->createUser($school, 'Wali Kelas', 'homeroom-only@siakad.test');

        foreach (['/guru/journals', '/guru/attendances', '/guru/grades'] as $route) {
            $this->actingAs($user)
                ->withSession(['active_role' => 'Wali Kelas'])
                ->get($route)
                ->assertRedirect(route('guru.dashboard'));
        }
    }

    public function test_teacher_cannot_submit_journal_for_another_teachers_schedule(): void
    {
        $fixture = $this->createAcademicFixture();

        $this->actingAs($fixture['teacherUser']);

        Livewire::test(Journals::class)
            ->set('formScheduleId', $fixture['foreignSchedule']->id)
            ->set('date', '2026-08-11')
            ->set('topicSummary', 'Percobaan akses jadwal guru lain')
            ->call('saveJournal');

        $this->assertDatabaseMissing('teaching_journals', [
            'schedule_id' => $fixture['foreignSchedule']->id,
        ]);
    }

    public function test_teacher_cannot_submit_attendance_for_another_teachers_schedule(): void
    {
        $fixture = $this->createAcademicFixture();

        $this->actingAs($fixture['teacherUser']);

        Livewire::test(Attendances::class)
            ->set('selectedScheduleId', (string) $fixture['foreignSchedule']->id)
            ->set('attendanceDate', '2026-08-11')
            ->call('saveSubjectAttendance');

        $this->assertDatabaseMissing('subject_attendances', [
            'schedule_id' => $fixture['foreignSchedule']->id,
        ]);
    }

    public function test_teacher_cannot_submit_grade_for_another_teachers_schedule(): void
    {
        $fixture = $this->createAcademicFixture();

        $this->actingAs($fixture['teacherUser']);

        Livewire::test(Grades::class)
            ->set('selectedScheduleId', (string) $fixture['foreignSchedule']->id)
            ->call('saveGrade', $fixture['foreignStudent']->id);

        $this->assertDatabaseMissing('grades', [
            'student_id' => $fixture['foreignStudent']->id,
            'subject_id' => $fixture['foreignSchedule']->subject_id,
        ]);
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Sekolah Role Test',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
    }

    private function createUser(School $school, string $role, string $email): User
    {
        $user = User::create([
            'name' => $role.' Test',
            'email' => $email,
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    /**
     * @return array{
     *     school: School,
     *     academicYear: AcademicYear,
     *     teacherUser: User,
     *     teacher: Teacher,
     *     foreignSchedule: Schedule,
     *     foreignStudent: Student
     * }
     */
    private function createAcademicFixture(): array
    {
        $school = $this->createSchool();
        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $teacherUser = $this->createUser($school, 'Guru', 'teacher-owner@siakad.test');
        $foreignUser = $this->createUser($school, 'Guru', 'teacher-foreign@siakad.test');

        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $teacherUser->id,
            'nip' => '19800101001',
            'name' => 'Guru Pemilik Jadwal',
            'gender' => 'L',
            'is_active' => true,
        ]);
        $foreignTeacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $foreignUser->id,
            'nip' => '19800101002',
            'name' => 'Guru Pemilik Lain',
            'gender' => 'P',
            'is_active' => true,
        ]);

        $classroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'name' => 'VII A',
            'grade_level' => '7',
        ]);
        $foreignClassroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $foreignTeacher->id,
            'name' => 'VII B',
            'grade_level' => '7',
        ]);

        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Matematika',
            'code' => 'MAT-ROLE',
            'type' => 'Wajib',
        ]);
        $foreignSchedule = Schedule::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'classroom_id' => $foreignClassroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $foreignTeacher->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:00',
            'end_time' => '08:00',
        ]);

        $foreignStudent = Student::create([
            'school_id' => $school->id,
            'classroom_id' => $foreignClassroom->id,
            'nisn' => 'ROLE-001',
            'nis' => 'ROLE-001',
            'name' => 'Siswa Jadwal Lain',
            'gender' => 'L',
            'status' => 'Aktif',
        ]);

        return [
            'school' => $school,
            'academicYear' => $academicYear,
            'teacherUser' => $teacherUser,
            'teacher' => $teacher,
            'foreignSchedule' => $foreignSchedule,
            'foreignStudent' => $foreignStudent,
        ];
    }
}
