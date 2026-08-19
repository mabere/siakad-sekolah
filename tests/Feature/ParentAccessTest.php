<?php

namespace Tests\Feature;

use App\Livewire\Parent\Dashboard;
use App\Livewire\Parent\PaymentsIndex;
use App\Models\AcademicYear;
use App\Models\ParentStudentRelation;
use App\Models\PaymentCategory;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ParentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_select_only_a_child_from_their_school_relation(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $school = $this->createSchool('Sekolah A');
        $otherSchool = $this->createSchool('Sekolah B');
        $parent = $this->createUser($school, 'parent@access.test', 'Orang Tua');
        $ownChild = $this->createStudent($school, 'Anak Sendiri');
        $sameSchoolOtherChild = $this->createStudent($school, 'Anak Orang Lain');
        $differentSchoolChild = $this->createStudent($otherSchool, 'Anak Sekolah B');

        ParentStudentRelation::create([
            'parent_user_id' => $parent->id,
            'student_id' => $ownChild->id,
            'relationship_type' => 'ayah',
        ]);
        ParentStudentRelation::create([
            'parent_user_id' => $parent->id,
            'student_id' => $differentSchoolChild->id,
            'relationship_type' => 'ayah',
        ]);

        $component = Livewire::actingAs($parent)->test(Dashboard::class);
        $component->call('selectStudent', $ownChild->id)
            ->assertSet('selectedStudentId', $ownChild->id)
            ->assertDontSee('Anak Sekolah B');

        $component = Livewire::actingAs($parent)->test(Dashboard::class);
        $component->call('selectStudent', $sameSchoolOtherChild->id)
            ->assertStatus(403);
    }

    public function test_parent_cannot_open_payment_outside_accessible_child_scope(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $school = $this->createSchool('Sekolah A');
        $parent = $this->createUser($school, 'parent-payment@access.test', 'Orang Tua');
        $ownChild = $this->createStudent($school, 'Anak Sendiri');
        $otherChild = $this->createStudent($school, 'Anak Orang Lain');
        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $category = PaymentCategory::create([
            'school_id' => $school->id,
            'name' => 'SPP',
            'type' => 'monthly_spp',
            'default_amount' => 100000,
            'is_active' => true,
        ]);

        ParentStudentRelation::create([
            'parent_user_id' => $parent->id,
            'student_id' => $ownChild->id,
            'relationship_type' => 'ayah',
        ]);
        $payment = StudentPayment::create([
            'school_id' => $school->id,
            'student_id' => $otherChild->id,
            'payment_category_id' => $category->id,
            'academic_year_id' => $academicYear->id,
            'amount' => 100000,
            'status' => 'unpaid',
        ]);

        Livewire::actingAs($parent)
            ->test(PaymentsIndex::class)
            ->call('openUploadModal', $payment->id)
            ->assertStatus(403);
    }

    private function createSchool(string $name): School
    {
        return School::create([
            'name' => $name,
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
    }

    private function createUser(School $school, string $email, string $role): User
    {
        $user = User::create([
            'name' => $role,
            'email' => $email,
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createStudent(School $school, string $name): Student
    {
        $user = User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@student.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole('Siswa');

        return Student::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'name' => $name,
            'gender' => 'L',
            'status' => 'Aktif',
        ]);
    }
}
