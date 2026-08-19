<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_save_and_reload_school_vision_and_mission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $school = School::create([
            'name' => 'Sekolah Profil Test',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $admin = User::create([
            'name' => 'Admin Profil Test',
            'email' => 'admin-profile@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin Sekolah');

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('vision', 'Terwujudnya peserta didik yang berkarakter dan berprestasi.')
            ->set('mission', "Menyelenggarakan pembelajaran yang bermutu.\nMengembangkan karakter peserta didik.")
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'vision' => 'Terwujudnya peserta didik yang berkarakter dan berprestasi.',
            'mission' => "Menyelenggarakan pembelajaran yang bermutu.\nMengembangkan karakter peserta didik.",
        ]);

        app()->forgetScopedInstances();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->assertSet('vision', 'Terwujudnya peserta didik yang berkarakter dan berprestasi.')
            ->assertSet('mission', "Menyelenggarakan pembelajaran yang bermutu.\nMengembangkan karakter peserta didik.");
    }
}
