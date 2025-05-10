<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view any users',
            'view users',
            'create users',
            'update any users',
            'update users',
            'delete any users',
            'delete users',
            
            'view any spreads',
            'view spreads',
            'create spreads',
            'delete any spreads',
            'delete spreads',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);

        $adminRole->syncPermissions($permissions);

        $userRole->syncPermissions([
            'view users',
            'update users',
            'delete users',
            'view spreads',
            'create spreads',
            'delete spreads',
        ]);
    }
}