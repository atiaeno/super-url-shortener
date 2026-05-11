<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders\Production;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user with secure password (change in production)
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@' . parse_url(config('app.url'), PHP_URL_HOST),
            'password' => Hash::make('change-this-password-in-production'),
            'role' => 'admin',
        ]);

        // Note: Remove or change the default password before production deployment
        $this->command->warn('⚠️  IMPORTANT: Change the default admin password before going live!');
        $this->command->warn('   Email: admin@' . parse_url(config('app.url'), PHP_URL_HOST));
        $this->command->warn('   Password: change-this-password-in-production');
    }
}
