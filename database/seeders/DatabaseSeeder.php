<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'system_access']);

        Permission::create(['name' => 'role_view']);
        Permission::create(['name' => 'role_create']);
        Permission::create(['name' => 'role_update']);
        Permission::create(['name' => 'role_delete']);

        Permission::create(['name' => 'permission_view']);
        Permission::create(['name' => 'permission_create']);
        Permission::create(['name' => 'permission_update']);
        Permission::create(['name' => 'permission_delete']);

        Permission::create(['name' => 'user_view']);
        Permission::create(['name' => 'user_create']);
        Permission::create(['name' => 'user_update']);
        Permission::create(['name' => 'user_delete']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'user']);
        $role1->givePermissionTo('system_access');

        $role2 = Role::create(['name' => 'admin']);
        $role2->givePermissionTo('user_view');
        $role2->givePermissionTo('user_create');
        $role2->givePermissionTo('user_update');
        $role2->givePermissionTo('user_delete');


        $role3 = Role::create(['name' => 'SuperAdmin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        $user = \App\Models\User::factory()->create([
            'name' => 'User',
            'login' => 'User',
            'email' => 'user@oeeapp.com',
            'password' => Hash::make(sha1('teste123'))
        ]);
        $user->assignRole($role1);

        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'login' => 'Admin',
            'email' => 'admin@oeeapp.com',
            'password' => Hash::make(sha1('teste123'))
        ]);
        $user->assignRole($role2);

        $user = \App\Models\User::factory()->create([
            'name' => 'SuperAdmin',
            'login' => 'SuperAdmin',
            'email' => 'superadmin@oeeapp.com',
            'password' => Hash::make(sha1('teste123'))
        ]);
        $user->assignRole($role3);
    }
}
