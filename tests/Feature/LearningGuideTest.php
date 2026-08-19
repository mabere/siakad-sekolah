<?php

namespace Tests\Feature;

use App\Livewire\Teacher\LearningGuide;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LearningGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_teacher_only_sees_guru_guide_and_cannot_switch_to_other_role_tabs(): void
    {
        $fixture = $this->createTeacherFixture();

        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.learning-guide'))
            ->assertOk()
            ->assertSee('Panduan Penggunaan Perangkat Pembelajaran AI')
            ->assertSee('Panduan Khusus Peran Anda')
            ->assertSee('Panduan Langkah Praktis untuk Guru Pengampu')
            ->assertDontSee('Peran Strategis Wakasek Kurikulum')
            ->assertDontSee('Peran Pengawasan &amp; Pengesahan Kepala Sekolah', false)
            ->assertDontSee('Konfigurasi Google Gemini AI Provider');

        // Verify Livewire component rejects switching to unauthorized tabs
        Livewire::actingAs($fixture['user'])
            ->test(LearningGuide::class)
            ->assertSet('activeRoleTab', 'guru')
            ->call('setRoleTab', 'admin')
            ->assertSet('activeRoleTab', 'guru')
            ->call('setRoleTab', 'wakasek')
            ->assertSet('activeRoleTab', 'guru')
            ->call('setRoleTab', 'kepsek')
            ->assertSet('activeRoleTab', 'guru');
    }

    public function test_admin_can_see_and_switch_between_all_four_role_guides(): void
    {
        $school = School::create([
            'name' => 'SMA Alam Jaya',
            'level' => 'SMA',
            'status' => 'SWASTA',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        $adminUser = User::create([
            'name' => 'Super Admin User',
            'email' => 'admin.'.uniqid().'@test.com',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $adminUser->assignRole('Super Admin');

        $this->actingAs($adminUser)
            ->withSession(['active_role' => 'Super Admin'])
            ->get(route('admin.academic.curriculum-guide'))
            ->assertOk()
            ->assertSee('Panduan Penggunaan Perangkat Pembelajaran AI')
            ->assertSee('Guru &amp; Wali Kelas', false)
            ->assertSee('Wakasek Kurikulum')
            ->assertSee('Kepala Sekolah')
            ->assertSee('Administrator Sekolah');

        Livewire::actingAs($adminUser)
            ->test(\App\Livewire\Admin\CurriculumGuide::class)
            ->assertSet('activeRoleTab', 'admin')
            ->assertSee('Konfigurasi Google Gemini AI Provider')
            ->call('setRoleTab', 'guru')
            ->assertSet('activeRoleTab', 'guru')
            ->assertSee('Panduan Langkah Praktis untuk Guru Pengampu')
            ->call('setRoleTab', 'wakasek')
            ->assertSet('activeRoleTab', 'wakasek')
            ->assertSee('Peran Strategis Wakasek Kurikulum')
            ->call('setRoleTab', 'kepsek')
            ->assertSet('activeRoleTab', 'kepsek')
            ->assertSee('Pengesahan Dokumen Cetak Ber-Kop Resmi');
    }

    public function test_wakasek_and_kepsek_only_see_their_respective_guide(): void
    {
        $school = School::create([
            'name' => 'SMA Alam Nusantara',
            'level' => 'SMA',
            'status' => 'SWASTA',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        // 1. Wakasek Kurikulum
        $wakasekUser = User::create([
            'name' => 'Bambang Wakasek',
            'email' => 'wakasek.'.uniqid().'@test.com',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $wakasekUser->assignRole('Wakasek Kurikulum');

        $this->actingAs($wakasekUser)
            ->withSession(['active_role' => 'Wakasek Kurikulum'])
            ->get(route('wakasek.academic.curriculum-guide'))
            ->assertOk()
            ->assertSee('Peran Strategis Wakasek Kurikulum')
            ->assertDontSee('Konfigurasi Google Gemini AI Provider');

        Livewire::actingAs($wakasekUser)
            ->test(\App\Livewire\Admin\CurriculumGuide::class)
            ->assertSet('activeRoleTab', 'wakasek')
            ->call('setRoleTab', 'admin')
            ->assertSet('activeRoleTab', 'wakasek');

        // 2. Kepala Sekolah
        $kepsekUser = User::create([
            'name' => 'Dr. H. Mulyono',
            'email' => 'kepsek.'.uniqid().'@test.com',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $kepsekUser->assignRole('Kepala Sekolah');

        $this->actingAs($kepsekUser)
            ->withSession(['active_role' => 'Kepala Sekolah'])
            ->get(route('kepsek.academic.curriculum-guide'))
            ->assertOk()
            ->assertSee('Pengesahan Dokumen Cetak Ber-Kop Resmi')
            ->assertDontSee('Konfigurasi Google Gemini AI Provider');

        Livewire::actingAs($kepsekUser)
            ->test(\App\Livewire\Admin\CurriculumGuide::class)
            ->assertSet('activeRoleTab', 'kepsek')
            ->call('setRoleTab', 'admin')
            ->assertSet('activeRoleTab', 'kepsek');
    }

    public function test_teacher_dashboard_displays_ai_quick_start_hub(): void
    {
        $fixture = $this->createTeacherFixture();

        $this->actingAs($fixture['user'])
            ->withSession(['active_role' => 'Guru'])
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('Pusat Otomasi Administrasi')
            ->assertSee('Generator 7 Dokumen AI')
            ->assertSee('Rekomendasi Diferensiasi')
            ->assertSee('Remedial');
    }

    /**
     * @return array{
     *   school: School,
     *   user: User,
     *   teacher: Teacher
     * }
     */
    private function createTeacherFixture(): array
    {
        $school = School::create([
            'name' => 'SMA Alam Nusantara',
            'level' => 'SMA',
            'status' => 'SWASTA',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        $user = User::create([
            'name' => 'Siti Guru',
            'email' => 'siti.'.uniqid().'@test.com',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole('Guru');

        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'nip' => '199001012015012001',
            'name' => 'Siti Guru, M.Pd.',
            'gender' => 'P',
            'is_active' => true,
        ]);

        AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'curriculum_type' => 'MERDEKA',
        ]);

        return [
            'school' => $school,
            'user' => $user,
            'teacher' => $teacher,
        ];
    }
}
