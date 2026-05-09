<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin accounts
            [
                'name' => 'Super Admin',
                'email' => 'admin@mail.com',
                'password' => bcrypt('123456'),
                'is_admin' => true,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Admin CRM',
                'email' => 'admin@crm.com',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@shop.com',
                'password' => bcrypt('manager123'),
                'is_admin' => true,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            // Customer accounts
            [
                'name' => 'Test User',
                'email' => 'user@test.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Customer Demo',
                'email' => 'demo@customer.com',
                'password' => bcrypt('demo123'),
                'is_admin' => false,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Іван Петренко',
                'email' => 'ivan@example.com',
                'password' => bcrypt('ivan123'),
                'is_admin' => false,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Марія Коваленко',
                'email' => 'maria@example.com',
                'password' => bcrypt('maria123'),
                'is_admin' => false,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Create additional random users if needed
        if (User::count() < 10) {
            User::factory(10 - User::count())->create();
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin accounts:');
        $this->command->info('  - admin@mail.com / 123456');
        $this->command->info('  - admin@crm.com / admin123');
        $this->command->info('  - manager@shop.com / manager123');
        $this->command->info('Customer accounts:');
        $this->command->info('  - user@test.com / password');
        $this->command->info('  - demo@customer.com / demo123');
    }
}
