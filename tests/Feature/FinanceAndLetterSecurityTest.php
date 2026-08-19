<?php

namespace Tests\Feature;

use App\Livewire\Admin\Finance\StudentPaymentIndex;
use App\Livewire\Tu\StudentLettersIndex;
use App\Models\AcademicYear;
use App\Models\PaymentCategory;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentLetter;
use App\Models\StudentPayment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceAndLetterSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_overpay_a_student_payment(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $school = $this->createSchool('Sekolah Keuangan');
        $user = $this->createUser($school, 'tu-finance@test.local', 'Staf Tata Usaha');
        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $student = Student::create([
            'school_id' => $school->id,
            'name' => 'Siswa Keuangan',
            'gender' => 'L',
            'status' => 'Aktif',
        ]);
        $category = PaymentCategory::create([
            'school_id' => $school->id,
            'name' => 'SPP',
            'type' => 'monthly_spp',
            'default_amount' => 50000,
            'is_active' => true,
        ]);
        $payment = StudentPayment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'payment_category_id' => $category->id,
            'academic_year_id' => $academicYear->id,
            'amount' => 50000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        Livewire::actingAs($user)
            ->test(StudentPaymentIndex::class)
            ->set('payPaymentId', $payment->id)
            ->set('payAmount', 50001)
            ->call('processPayment')
            ->assertHasErrors('payAmount');

        $this->assertDatabaseHas('student_payments', [
            'id' => $payment->id,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
    }

    public function test_letter_numbers_remain_unique_when_multiple_letters_are_created(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $school = $this->createSchool('Sekolah Surat');
        $user = $this->createUser($school, 'tu-letter@test.local', 'Staf Tata Usaha');
        $student = Student::create([
            'school_id' => $school->id,
            'name' => 'Siswa Surat',
            'gender' => 'L',
            'status' => 'Aktif',
        ]);

        $component = Livewire::actingAs($user)->test(StudentLettersIndex::class)
            ->set('studentId', (string) $student->id)
            ->set('letterType', 'surat_keterangan_aktif')
            ->call('createLetter')
            ->call('createLetter');

        $this->assertSame(2, StudentLetter::query()->where('school_id', $school->id)->count());
        $numbers = StudentLetter::query()->where('school_id', $school->id)->pluck('letter_number');
        $this->assertCount(2, $numbers->unique());
        $this->assertTrue($numbers->every(fn (?string $number): bool => is_string($number) && str_starts_with($number, '421.3/TU/')));
        $component->assertHasNoErrors();
    }

    private function createSchool(string $name): School
    {
        return School::create([
            'name' => $name,
            'level' => 'SMA',
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
}
