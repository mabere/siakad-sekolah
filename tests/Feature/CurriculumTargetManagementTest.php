<?php

namespace Tests\Feature;

use App\Livewire\Admin\Academic\CurriculumTarget\Index;
use App\Livewire\Teacher\LearningAssistant;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CurriculumTarget;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CurriculumTargetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_wakasek_and_admin_can_access_curriculum_targets_index(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Test',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $wakasekUser = User::create([
            'school_id' => $school->id,
            'name' => 'Wakasek Kurikulum Test',
            'email' => 'wakasek.kurikulum@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $wakasekUser->assignRole('Wakasek Kurikulum');

        $this->actingAs($wakasekUser)
            ->withSession(['active_role' => 'Wakasek Kurikulum'])
            ->get(route('wakasek.academic.curriculum-targets'))
            ->assertOk()
            ->assertSee('Bank Kurikulum');

        $adminUser = User::create([
            'school_id' => $school->id,
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $adminUser->assignRole('Admin Sekolah');

        $this->actingAs($adminUser)
            ->withSession(['active_role' => 'Admin Sekolah'])
            ->get(route('admin.academic.curriculum-targets'))
            ->assertOk()
            ->assertSee('Bank Kurikulum');
    }

    public function test_unauthorized_roles_are_redirected_to_their_portal(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Test',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $studentUser = User::create([
            'school_id' => $school->id,
            'name' => 'Siswa Test',
            'email' => 'siswa@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('Siswa');

        $this->actingAs($studentUser)
            ->withSession(['active_role' => 'Siswa'])
            ->get('/admin/academic/curriculum-targets')
            ->assertRedirect(route('siswa.dashboard'));
    }

    public function test_wakasek_can_create_update_toggle_and_delete_curriculum_target(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Test',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $user = User::create([
            'school_id' => $school->id,
            'name' => 'Wakasek Test',
            'email' => 'wakasek@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('Wakasek Kurikulum');

        $this->actingAs($user)->withSession(['active_role' => 'Wakasek Kurikulum']);

        // 1. Create Target
        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('subject_name', 'Bahasa Indonesia')
            ->set('phase', 'Fase E')
            ->set('grade_level', 10)
            ->set('semester', '1')
            ->set('chapter_number', 1)
            ->set('chapter_title', 'Bab 1: Menulis Teks Esai Kritis')
            ->set('element', 'Menulis')
            ->set('topic', 'Teks Esai Kritis')
            ->set('learning_objectives', 'Peserta didik mampu menyusun esai kritis berbasis data faktual.')
            ->set('learning_model', 'Problem-Based Learning (PBL)')
            ->set('p5_dimensions', ['Bernalar Kritis', 'Mandiri'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertSee('Target Capaian Pembelajaran');

        $this->assertDatabaseHas('curriculum_targets', [
            'school_id' => $school->id,
            'chapter_title' => 'Bab 1: Menulis Teks Esai Kritis',
            'topic' => 'Teks Esai Kritis',
        ]);

        $created = CurriculumTarget::where('school_id', $school->id)->first();
        $this->assertNotNull($created);

        // 2. Edit Target
        Livewire::test(Index::class)
            ->call('editTarget', $created->id)
            ->assertSet('isEditing', true)
            ->assertSet('chapter_title', 'Bab 1: Menulis Teks Esai Kritis')
            ->set('chapter_title', 'Bab 1: Menulis Teks Esai Kritis & Reflektif')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('curriculum_targets', [
            'id' => $created->id,
            'chapter_title' => 'Bab 1: Menulis Teks Esai Kritis & Reflektif',
        ]);

        // 3. Toggle Status
        Livewire::test(Index::class)
            ->call('toggleStatus', $created->id)
            ->assertHasNoErrors();

        $this->assertEquals(false, $created->fresh()->is_active);

        // 4. Delete Target
        Livewire::test(Index::class)
            ->call('confirmDelete', $created->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteTarget')
            ->assertHasNoErrors()
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('curriculum_targets', [
            'id' => $created->id,
        ]);
    }

    public function test_wakasek_can_load_national_presets(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Test',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $user = User::create([
            'school_id' => $school->id,
            'name' => 'Wakasek Test',
            'email' => 'wakasek2@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('Wakasek Kurikulum');

        $this->actingAs($user)->withSession(['active_role' => 'Wakasek Kurikulum']);

        $this->assertEquals(0, CurriculumTarget::where('school_id', $school->id)->count());

        Livewire::test(Index::class)
            ->call('loadNationalPresets')
            ->assertHasNoErrors()
            ->assertSee('Berhasil memuat');

        $this->assertGreaterThan(10, CurriculumTarget::where('school_id', $school->id)->count());
        $this->assertDatabaseHas('curriculum_targets', [
            'school_id' => $school->id,
            'subject_name' => 'Bahasa Indonesia',
            'chapter_title' => 'Bab 1: Mengungkap Fakta Alam Secara Objektif (Teks Laporan Hasil Observasi)',
        ]);
        $this->assertDatabaseHas('curriculum_targets', [
            'school_id' => $school->id,
            'subject_name' => 'Matematika',
            'chapter_title' => 'Bab 1: Eksponen dan Logaritma',
        ]);
    }

    public function test_teacher_assistant_prioritizes_database_curriculum_targets(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Test',
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
        $user = User::create([
            'school_id' => $school->id,
            'name' => 'Guru Bahasa',
            'email' => 'guru.bahasa@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('Guru');

        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'name' => 'Guru Bahasa',
            'nip' => '198701012010011001',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Bahasa Indonesia',
            'code' => 'BIN-10',
            'type' => 'Wajib',
            'order' => 1,
        ]);

        $classroom = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $teacher->id,
            'name' => 'X-A',
            'grade_level' => '10',
            'order' => 1,
        ]);

        $schedule = Schedule::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
            'order' => 1,
        ]);

        // Create a custom school-level curriculum target in DB
        $customTarget = CurriculumTarget::create([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'subject_name' => 'Bahasa Indonesia',
            'phase' => 'Fase E (Kelas 10 SMA/SMK)',
            'grade_level' => 10,
            'semester' => '1',
            'chapter_number' => 1,
            'chapter_title' => 'Bab Kustom Sekolah: Riset Kearifan Lokal Maritim',
            'element' => 'Membaca dan Memirsa, Menulis',
            'topic' => 'Kearifan Lokal Maritim',
            'learning_objectives' => 'Murid mampu mendokumentasikan kearifan nelayan lokal secara naratif dan kritis.',
            'learning_model' => 'Project-Based Learning (PjBL)',
            'p5_dimensions' => ['Kearifan Lokal', 'Bernalar Kritis'],
            'meaningful_understanding' => 'Kearifan lokal maritim adalah warisan budaya yang perlu dilestarikan.',
            'inquiry_questions' => ['Bagaimana tradisi melaut mengajarkan keberlanjutan ekosistem?'],
            'suggested_duration_jp' => '6 JP',
            'reference_source' => 'Kurikulum KSP SMAN 1',
            'is_active' => true,
        ]);

        $this->actingAs($user)->withSession(['active_role' => 'Guru']);

        Livewire::test(LearningAssistant::class)
            ->set('selectedScheduleId', (string) $schedule->id)
            ->assertCount('availableBankTopics', 1)
            ->call('selectBankTopic', 'db-target-'.$customTarget->id)
            ->assertSet('topic', 'Kearifan Lokal Maritim')
            ->assertSet('selectedLearningModel', 'Project-Based Learning (PjBL)')
            ->assertSet('selectedP5Dimensions', ['Kearifan Lokal', 'Bernalar Kritis'])
            ->assertSee('Riset Kearifan Lokal Maritim');
    }
}
