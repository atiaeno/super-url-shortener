<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // LinkSeeder::class,
            // AdSeeder::class,
            //  ReportSeeder::class,
            //  AffiliateSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
