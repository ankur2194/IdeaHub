<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@ideahub.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'IT',
            'job_title' => 'System Administrator',
            'is_active' => true,
        ]);

        // Create department head
        User::create([
            'name' => 'John Manager',
            'email' => 'manager@ideahub.test',
            'password' => Hash::make('password'),
            'role' => 'department_head',
            'department' => 'Product',
            'job_title' => 'Product Manager',
            'is_active' => true,
        ]);

        // Create team lead
        User::create([
            'name' => 'Sarah Lead',
            'email' => 'lead@ideahub.test',
            'password' => Hash::make('password'),
            'role' => 'team_lead',
            'department' => 'Engineering',
            'job_title' => 'Engineering Lead',
            'is_active' => true,
        ]);

        // Create regular users
        User::create([
            'name' => 'Alice Developer',
            'email' => 'alice@ideahub.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department' => 'Engineering',
            'job_title' => 'Software Developer',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Bob Designer',
            'email' => 'bob@ideahub.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department' => 'Design',
            'job_title' => 'UX Designer',
            'is_active' => true,
        ]);

        // Seed categories and tags
        $this->call([
            CategorySeeder::class,
            TagSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('---');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@ideahub.test / password');
        $this->command->info('Manager: manager@ideahub.test / password');
        $this->command->info('User: alice@ideahub.test / password');
    }
}
