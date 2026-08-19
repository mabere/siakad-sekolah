<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignParentRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the role exists
        $roleName = 'Orang Tua';
        $role = Role::firstOrCreate(['name' => $roleName]);

        // Find the user by email
        $email = 'ortu@siakad.test';
        $user = User::where('email', $email)->first();
        if (! $user) {
            echo "User with email {$email} not found.\n";

            return;
        }

        // Assign the role if not already assigned
        if (! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
            echo "Role '{$roleName}' assigned to user {$email}.\n";
        } else {
            echo "User {$email} already has role '{$roleName}'.\n";
        }
    }
}
