<?php

namespace Tests\Feature;

use App\Livewire\Admin\Master\Subject\Index;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SmaSubjectsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_view_paginated_subjects_and_filter(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Prestasi',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        $admin = User::create([
            'school_id' => $school->id,
            'name' => 'Admin Sekolah',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');

        $this->seed(SmaSubjectsSeeder::class);

        $this->actingAs($admin)->withSession(['active_role' => 'Admin Sekolah']);

        // Test Livewire pagination and rendering
        Livewire::test(Index::class)
            ->assertOk()
            ->assertSee('Daftar Mata Pelajaran')
            ->assertSee('37 Total')
            ->set('search', 'Biologi')
            ->assertSee('Biologi')
            ->assertDontSee('Fisika')
            ->set('search', '')
            ->set('selectedType', 'Muatan Lokal')
            ->assertSee('Bahasa Daerah')
            ->assertSee('Pendidikan Lingkungan Hidup')
            ->assertDontSee('Matematika');
    }

    public function test_admin_can_create_edit_and_delete_subject(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Prestasi',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        $admin = User::create([
            'school_id' => $school->id,
            'name' => 'Admin Sekolah',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');

        $this->actingAs($admin)->withSession(['active_role' => 'Admin Sekolah']);

        // 1. Create Subject
        Livewire::test(Index::class)
            ->call('create')
            ->assertSet('isFormOpen', true)
            ->set('name', 'Robotika dan Otomasi')
            ->set('code', 'ROBO')
            ->set('type', 'Muatan Lokal')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isFormOpen', false);

        $this->assertDatabaseHas('subjects', [
            'school_id' => $school->id,
            'code' => 'ROBO',
            'name' => 'Robotika dan Otomasi',
        ]);

        $subject = Subject::where('school_id', $school->id)->where('code', 'ROBO')->first();
        $this->assertNotNull($subject);

        // 2. Edit Subject
        Livewire::test(Index::class)
            ->call('edit', $subject->id)
            ->assertSet('isEdit', true)
            ->assertSet('name', 'Robotika dan Otomasi')
            ->set('name', 'Robotika & AI Lanjut')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Robotika & AI Lanjut',
        ]);

        // 3. Delete Subject
        Livewire::test(Index::class)
            ->call('delete', $subject->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('subjects', [
            'id' => $subject->id,
        ]);
    }
}
