<?php

namespace Tests\Feature;

use App\Livewire\Admin\Master\AcademicYear\Index as AcademicYearIndex;
use App\Livewire\Admin\Master\Classroom\Index as ClassroomIndex;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SchoolDataSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_annual_settings_and_class_profile_are_saved_in_their_own_scopes(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $school = School::create([
            'name' => 'Sekolah Data Terpisah Test',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $admin = User::create([
            'name' => 'Admin Data Terpisah Test',
            'email' => 'admin-data-terpisah@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');

        Livewire::actingAs($admin)
            ->test(AcademicYearIndex::class)
            ->call('create')
            ->set('name', '2026/2027')
            ->set('semester', 'Ganjil')
            ->set('curriculum_type', 'MERDEKA')
            ->set('local_content', 'Bahasa daerah')
            ->set('p5_focus', 'Bernalar kritis dan gotong royong')
            ->set('effective_weeks', 18)
            ->set('calendar_notes', 'Asesmen sumatif pada akhir semester.')
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $academicYear = AcademicYear::query()->where('school_id', $school->id)->firstOrFail();
        $this->assertSame('MERDEKA', $academicYear->curriculum_type);
        $this->assertSame('Bahasa daerah', $academicYear->local_content);
        $this->assertSame(18, $academicYear->effective_weeks);

        Livewire::actingAs($admin)
            ->test(ClassroomIndex::class)
            ->call('create')
            ->set('academic_year_id', $academicYear->id)
            ->set('grade_level', '7')
            ->set('name', 'VII A')
            ->set('student_needs', 'Kemampuan awal siswa beragam; perlu penguatan literasi.')
            ->set('available_facilities', 'Papan tulis dan perpustakaan.')
            ->set('learning_environment', 'Ruang kelas dan perpustakaan')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('classrooms', [
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'VII A',
            'student_needs' => 'Kemampuan awal siswa beragam; perlu penguatan literasi.',
            'available_facilities' => 'Papan tulis dan perpustakaan.',
            'learning_environment' => 'Ruang kelas dan perpustakaan',
        ]);
    }
}
