<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users\Index as UserIndex;
use App\Livewire\Parent\PaymentsIndex;
use App\Livewire\Teacher\Exams;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ParentStudentRelation;
use App\Models\PaymentCategory;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sekolah_cannot_manage_super_admin_or_assign_super_admin_role(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $school = $this->createSchool();
        $admin = $this->createUser($school, 'admin-hardening@siakad.test', 'Admin Sekolah');
        $superAdmin = $this->createUser($school, 'super-hardening@siakad.test', 'Super Admin');
        $otherAdmin = $this->createUser($school, 'other-admin-hardening@siakad.test', 'Admin Sekolah');
        $target = $this->createUser($school, 'target-hardening@siakad.test', 'Guru');

        $adminComponent = Livewire::actingAs($admin)
            ->test(UserIndex::class);

        $adminComponent
            ->assertDontSeeHtml('wire:click="editRoles('.$admin->id.')"')
            ->assertDontSeeHtml('wire:click="editRoles('.$superAdmin->id.')"')
            ->assertDontSeeHtml('wire:click="editRoles('.$otherAdmin->id.')"')
            ->assertSeeHtml('wire:click="editRoles('.$target->id.')"')
            ->call('editRoles', $superAdmin->id)
            ->assertStatus(403);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('editRoles', $otherAdmin->id)
            ->assertStatus(403);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('editRoles', $target->id)
            ->set('selectedRoles', ['Super Admin'])
            ->call('updateRoles')
            ->assertHasErrors(['roles.0']);

        $this->assertFalse($target->fresh()->hasRole('Super Admin'));
    }

    public function test_teacher_cannot_schedule_exam_for_classroom_without_teaching_schedule(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $school = $this->createSchool();
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $teacherUser = $this->createUser($school, 'teacher-exam-hardening@siakad.test', 'Guru');
        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $teacherUser->id,
            'name' => 'Guru Ujian',
            'gender' => 'L',
            'is_active' => true,
        ]);
        $ownClassroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'name' => 'X-A',
            'grade_level' => '10',
        ]);
        $foreignClassroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'X-B',
            'grade_level' => '10',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Matematika',
            'code' => 'MAT-HARDENING',
            'type' => 'Wajib',
        ]);
        Schedule::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'classroom_id' => $ownClassroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:00',
            'end_time' => '08:00',
        ]);
        $bank = QuestionBank::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Bank Ujian',
            'code' => 'BANK-HARDENING',
        ]);

        Livewire::actingAs($teacherUser)
            ->test(Exams::class)
            ->set('examTitle', 'Ujian Kelas Lain')
            ->set('examSubjectId', (string) $subject->id)
            ->set('examClassroomId', (string) $foreignClassroom->id)
            ->set('examBankId', (string) $bank->id)
            ->call('saveExam')
            ->assertHasErrors('examClassroomId');

        $this->assertDatabaseMissing('exams', [
            'classroom_id' => $foreignClassroom->id,
            'question_bank_id' => $bank->id,
        ]);
    }

    public function test_parent_payment_proof_is_stored_on_private_disk(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
        Storage::fake('public');

        $school = $this->createSchool();
        $parent = $this->createUser($school, 'parent-proof-hardening@siakad.test', 'Orang Tua');
        $student = Student::create([
            'school_id' => $school->id,
            'name' => 'Anak Bukti',
            'gender' => 'L',
            'status' => 'Aktif',
        ]);
        $category = PaymentCategory::create([
            'school_id' => $school->id,
            'name' => 'SPP',
            'type' => 'monthly_spp',
            'default_amount' => 100000,
            'is_active' => true,
        ]);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $payment = StudentPayment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'payment_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'month' => 1,
            'amount' => 100000,
            'status' => 'unpaid',
        ]);
        $student->user_id = null;
        $student->save();

        ParentStudentRelation::create([
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'relationship_type' => 'ayah',
        ]);

        Livewire::actingAs($parent)
            ->test(PaymentsIndex::class)
            ->call('openUploadModal', $payment->id)
            ->set('proofFile', UploadedFile::fake()->image('proof.png', 200, 200))
            ->call('uploadProof');

        $path = $payment->fresh()->proof_file;
        $this->assertIsString($path);
        $this->assertStringStartsWith('payment_proofs/', $path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_finance_payment_proof_download_is_scoped_to_the_active_school(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');

        $school = $this->createSchool();
        $otherSchool = $this->createSchool();
        $admin = $this->createUser($school, 'admin-proof-download@siakad.test', 'Admin Sekolah');
        $otherAdmin = $this->createUser($otherSchool, 'other-proof-download@siakad.test', 'Admin Sekolah');
        $student = Student::create([
            'school_id' => $school->id,
            'name' => 'Anak Unduh',
            'gender' => 'L',
            'status' => 'Aktif',
        ]);
        $category = PaymentCategory::create([
            'school_id' => $school->id,
            'name' => 'SPP',
            'type' => 'monthly_spp',
            'default_amount' => 100000,
            'is_active' => true,
        ]);
        $payment = StudentPayment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'payment_category_id' => $category->id,
            'month' => 1,
            'amount' => 100000,
            'status' => 'pending_confirmation',
            'proof_file' => 'payment_proofs/'.$school->id.'/1/proof.png',
        ]);
        Storage::disk('local')->put($payment->proof_file, 'proof');

        $this->actingAs($admin)
            ->withSession(['active_role' => 'Admin Sekolah'])
            ->get(route('admin.finance.payments.proof', $payment->id))
            ->assertOk();

        app()->forgetScopedInstances();

        $this->actingAs($otherAdmin)
            ->withSession(['active_role' => 'Admin Sekolah'])
            ->get(route('admin.finance.payments.proof', $payment->id))
            ->assertNotFound();
    }

    public function test_payment_deduplication_key_is_generated_and_unique(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $school = $this->createSchool();
        $student = Student::create([
            'school_id' => $school->id,
            'name' => 'Anak Tagihan',
            'gender' => 'L',
            'status' => 'Aktif',
        ]);
        $category = PaymentCategory::create([
            'school_id' => $school->id,
            'name' => 'SPP',
            'type' => 'monthly_spp',
            'default_amount' => 100000,
            'is_active' => true,
        ]);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $payment = StudentPayment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'payment_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'month' => 1,
            'amount' => 100000,
            'status' => 'unpaid',
        ]);

        $this->assertSame(
            StudentPayment::makeDeduplicationKey($school->id, $student->id, $category->id, $year->id, 1),
            $payment->deduplication_key,
        );
        $this->assertSame(1, StudentPayment::where('deduplication_key', $payment->deduplication_key)->count());
    }

    public function test_web_responses_include_baseline_security_headers(): void
    {
        $this->createSchool();

        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Sekolah Hardening',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
    }

    private function createUser(School $school, string $email, string $role): User
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
}
