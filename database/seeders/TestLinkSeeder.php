<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Link;
use Illuminate\Database\Seeder;

class TestLinkSeeder extends Seeder
{
    public function run(): void
    {
        Link::create([
            'user_id' => 1,
            'short_code' => 'test',
            'destination_url' => 'https://example.com',
            'is_active' => true,
            'visibility' => 'public',
            'clicks_count' => 0,
            'report_count' => 0,
        ]);

        echo "Test link created: http://127.0.0.1:8000/test\n";
    }
}
