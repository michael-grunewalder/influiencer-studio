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
        Permission::create(['name' => 'app.login', 'guard_name' => 'web']);
        Permission::create(['name' => 'app.admin', 'guard_name' => 'web']);

        $roleSuperAdmin = Role::create(['name' => 'super-admin']);
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleUser = Role::create(['name' => 'user']);
        $roleAdmin->givePermissionTo('app.admin');
        $roleUser->givePermissionTo('app.login');

        $sa = User::create([
            'name' => 'Super Admin',
            'email' => 'super-admin@example.com',
            'password' => 'password',
        ]);
        $sa->assignRole($roleSuperAdmin);

        $a = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $sa->assignRole($roleAdmin);

        $u = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $u->assignRole($roleUser);
    }
}
