<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders\Production;

use Database\Seeders\Production\SettingSeeder;
use Database\Seeders\Production\UserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
