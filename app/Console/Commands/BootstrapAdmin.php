<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class BootstrapAdmin extends Command
{
    protected $signature = 'gazu:bootstrap-admin
        {--email=admin@gazu.com}
        {--password=changeme123}
        {--name=Admin}';

    protected $description = 'Idempotent: create admin user + default merchant warehouse if missing.';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');
        $name = (string) $this->option('name');

        $admin = User::query()->where('email', $email)->first();
        if (! $admin) {
            $admin = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
            $this->info("Admin created: {$email} / {$password}");
        } else {
            if (! $admin->is_admin) {
                $admin->is_admin = true;
                $admin->save();
                $this->info("Existing user promoted to admin: {$email}");
            } else {
                $this->info("Admin already exists: {$email}");
            }
        }

        if (Schema::hasTable('merchant_warehouses')) {
            $exists = DB::table('merchant_warehouses')->where('code', 'MAIN-01')->exists();
            if (! $exists) {
                DB::table('merchant_warehouses')->insert([
                    'code' => 'MAIN-01',
                    'name' => 'Головний склад',
                    'type' => 'own',
                    'city' => 'Київ',
                    'is_active' => true,
                    'is_default' => true,
                    'sort_order' => 0,
                    'pickup_supported' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info('Default warehouse MAIN-01 created.');
            } else {
                $this->info('Default warehouse MAIN-01 already exists.');
            }
        }

        return self::SUCCESS;
    }
}
