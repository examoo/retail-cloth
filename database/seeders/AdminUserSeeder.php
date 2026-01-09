<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default super admin user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // Assign super_admin role using Spatie
        $user->syncRoles([User::ROLE_SUPER_ADMIN]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@gmail.com');
        $this->command->info('Password: 12345678');
        $this->command->warn('Please change this password after first login!');
    }
}
