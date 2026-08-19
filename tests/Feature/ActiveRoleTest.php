<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActiveRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_revoked_active_role_cannot_authorize_management_route(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $school = School::create([
            'name' => 'Sekolah Aktif Role',
            'level' => 'SMP',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $user = User::create([
            'name' => 'Siswa Role Test',
            'email' => 'active-role@siakad.test',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $user->assignRole('Siswa');

        $this->actingAs($user)
            ->withSession(['active_role' => 'Super Admin'])
            ->get('/admin')
            ->assertRedirect('/login');
    }
}
