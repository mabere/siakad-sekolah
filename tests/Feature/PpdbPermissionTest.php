<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PpdbPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PpdbPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_panitia_cannot_manage_period_configuration(): void
    {
        $user = $this->user('panitia-permission', 'Panitia PPDB');
        $this->actingAs($user)->withSession(['active_role' => 'Panitia PPDB']);

        $this->assertFalse(PpdbPermissions::allows(PpdbPermissions::MANAGE_PERIODS));
        $this->expectException(HttpException::class);
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_PERIODS);
    }

    public function test_panitia_can_use_payment_operations_but_not_convert_student(): void
    {
        $user = $this->user('panitia-operations', 'Panitia PPDB');
        $this->actingAs($user)->withSession(['active_role' => 'Panitia PPDB']);

        $this->assertTrue(PpdbPermissions::allows(PpdbPermissions::VERIFY_PAYMENTS));
        $this->assertFalse(PpdbPermissions::allows(PpdbPermissions::CONVERT_STUDENT));
    }

    public function test_only_school_administrators_can_cancel_selection_finalization(): void
    {
        $panitia = $this->user('panitia-cancel-finalization', 'Panitia PPDB');
        $admin = $this->user('admin-cancel-finalization', 'Admin Sekolah');

        $this->actingAs($panitia)->withSession(['active_role' => 'Panitia PPDB']);
        $this->assertFalse(PpdbPermissions::allows(PpdbPermissions::CANCEL_FINALIZATION));

        $this->actingAs($admin)->withSession(['active_role' => 'Admin Sekolah']);
        $this->assertTrue(PpdbPermissions::allows(PpdbPermissions::CANCEL_FINALIZATION));
    }

    private function user(string $prefix, string $role): User
    {
        $user = User::create([
            'name' => $prefix,
            'email' => $prefix.'@siakad.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }
}
