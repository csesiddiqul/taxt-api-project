<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Define permissions
        $permissions = [
            "dashboard",
            "profile_all",
            "user_all",
            "role_all",
            "permission_all",
            "setting_all",
            "sliders_all",
            "galleries-all",
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $administrationRole = Role::firstOrCreate(['name' => 'administration']);
        $adminRole          = Role::firstOrCreate(['name' => 'admin']);
        $guestRole          = Role::firstOrCreate(['name' => 'guest']);
        $userRole           = Role::firstOrCreate(['name' => 'user']);

        // Assign permissions to roles
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);
        $administrationRole->syncPermissions($allPermissions);
        $guestRole->syncPermissions(['dashboard']);
        $userRole->syncPermissions(['dashboard', 'profile_all']);


        if ($admin = User::find(1)) {
            $admin->assignRole('administration');
        }


        // Assign roles to users
        if ($admin = User::find(2)) {
            $admin->assignRole('admin');
        }

        if ($guest = User::find(3)) {
            $guest->assignRole('guest');
        }

        if ($user = User::find(4)) {
            $user->assignRole('user');
        }
    }
}
