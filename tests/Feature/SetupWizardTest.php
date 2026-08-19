<?php

namespace Tests\Feature;

use App\Livewire\SetupWizard;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SetupWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_wizard_requires_authentication(): void
    {
        School::create([
            'name' => 'Sekolah Belum Setup',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => false,
        ]);

        $this->get('/setup/wizard')
            ->assertRedirect(route('login'));
    }

    public function test_setup_wizard_rejects_authenticated_non_super_admin(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $school = School::create([
            'name' => 'Sekolah Belum Setup',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => false,
        ]);

        $user = User::create([
            'name' => 'Siswa Test',
            'email' => 'siswa-test@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole('Siswa');

        $this->actingAs($user)
            ->withSession(['active_role' => 'Siswa'])
            ->get('/setup/wizard')
            ->assertRedirect(route('siswa.dashboard'));
    }

    public function test_super_admin_can_complete_setup_once_and_repeated_access_is_idempotent(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $school = School::create([
            'name' => 'Sekolah Belum Setup',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => false,
        ]);

        $admin = User::create([
            'name' => 'Super Admin Test',
            'email' => 'admin-setup@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Super Admin');

        Livewire::actingAs($admin)
            ->test(SetupWizard::class)
            ->set('schoolName', 'SMA Negeri 1 Test')
            ->set('npsn', '12345678')
            ->set('level', 'SMA')
            ->set('address', 'Jalan Pendidikan')
            ->set('academicYearName', '2026/2027')
            ->set('semester', 'Ganjil')
            ->call('nextStep')
            ->call('nextStep')
            ->call('completeSetup')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'name' => 'SMA Negeri 1 Test',
            'is_setup_completed' => true,
        ]);
        $this->assertSame(1, AcademicYear::where('school_id', $school->id)->count());

        $this->actingAs($admin)
            ->withSession(['active_role' => 'Super Admin'])
            ->get('/setup/wizard')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame(1, AcademicYear::where('school_id', $school->id)->count());
    }
}
