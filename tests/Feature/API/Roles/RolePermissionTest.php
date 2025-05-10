<?php

namespace Tests\Feature\API;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePermissionTest extends ApiTestCase
{
    public function test_roles_exist_after_seeding()
    {
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $userRole = Role::where('name', 'user')->where('guard_name', 'api')->first();
        
        $this->assertNotNull($adminRole, 'Admin role should exist');
        $this->assertNotNull($userRole, 'User role should exist');
    }

    public function test_permissions_exist_after_seeding()
    {
        $this->assertDatabaseHas('permissions', ['name' => 'view any users', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'view users', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'update any users', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'update users', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete any users', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete users', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'view any spreads', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'view spreads', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete any spreads', 'guard_name' => 'api']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete spreads', 'guard_name' => 'api']);
    }

    public function test_admin_role_has_all_permissions()
    {
        $adminRole = Role::findByName('admin', 'api');
        
        $this->assertTrue($adminRole->hasPermissionTo('view any users'));
        $this->assertTrue($adminRole->hasPermissionTo('update any users'));
        $this->assertTrue($adminRole->hasPermissionTo('delete any users'));
        $this->assertTrue($adminRole->hasPermissionTo('view any spreads'));
        $this->assertTrue($adminRole->hasPermissionTo('delete any spreads'));
    }
    
    public function test_user_role_has_limited_permissions()
    {
        $userRole = Role::findByName('user', 'api');
        
        $this->assertTrue($userRole->hasPermissionTo('view users'));
        $this->assertTrue($userRole->hasPermissionTo('update users'));
        $this->assertTrue($userRole->hasPermissionTo('delete users'));
        $this->assertTrue($userRole->hasPermissionTo('view spreads'));
        $this->assertTrue($userRole->hasPermissionTo('create spreads'));
        $this->assertTrue($userRole->hasPermissionTo('delete spreads'));
        
        $this->assertFalse($userRole->hasPermissionTo('view any users'));
        $this->assertFalse($userRole->hasPermissionTo('update any users'));
        $this->assertFalse($userRole->hasPermissionTo('delete any users'));
        $this->assertFalse($userRole->hasPermissionTo('view any spreads'));
        $this->assertFalse($userRole->hasPermissionTo('delete any spreads'));
    }

    public function test_user_gets_user_role_on_registration()
    {
        $this->createAuthenticatedUser();
        
        $this->assertTrue($this->user->hasRole('user'));
        $this->assertFalse($this->user->hasRole('admin'));
    }

    public function test_admin_can_be_created_with_admin_role()
    {
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        
        $adminUser->assignRole('admin');
        
        $this->assertTrue($adminUser->hasRole('admin'));
        $this->assertTrue($adminUser->hasPermissionTo('view any users'));
    }
    
    public function test_user_inherits_permissions_from_role()
    {
        $this->createAuthenticatedUser();
        
        $this->assertTrue($this->user->hasPermissionTo('view users'));
        $this->assertTrue($this->user->hasPermissionTo('create spreads'));
        
        $this->assertFalse($this->user->hasPermissionTo('view any users'));
    }
}