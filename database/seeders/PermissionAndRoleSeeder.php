<?php

namespace Database\Seeders;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionAndRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::findOrCreate('app.login', 'web');
        Permission::findOrCreate('app.admin', 'web');

        $roleSuperAdmin = Role::findOrCreate('super-admin', 'web');
        $roleAdmin = Role::findOrCreate('admin', 'web');
        $roleUser = Role::findOrCreate('user', 'web');
        $roleAdmin->givePermissionTo('app.admin');
        $roleUser->givePermissionTo('app.login');

        $sa = User::firstOrCreate(
            ['email' => 'super-admin@example.com'],
            [
                'first_name' => 'Super Admin',
                'password' => 'password',
            ]
        );
        if (! $sa->hasRole($roleSuperAdmin)) {
            $sa->assignRole($roleSuperAdmin);
        }

        $a = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'password' => 'password',
            ]
        );
        if (! $a->hasRole($roleAdmin)) {
            $a->assignRole($roleAdmin);
        }

        $u = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'first_name' => 'User',
                'password' => 'password',
            ]
        );
        if (! $u->hasRole($roleUser)) {
            $u->assignRole($roleUser);
        }

        // More Permissions to follow
    }
}
