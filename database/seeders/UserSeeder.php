<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'birthdate' => '1990-01-01',
                'password' => Hash::make('password123'),
            ]
        );
        
        $testUser->assignRole('user');
        
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'birthdate' => '1990-01-01',
                'password' => Hash::make('password123'),
            ]
        );
        
        $adminUser->assignRole('admin');
        
        $this->command->info('🧪 Test user created: test@example.com / password123');
        $this->command->info('👑 Admin user created: admin@example.com / password123');
    }
}