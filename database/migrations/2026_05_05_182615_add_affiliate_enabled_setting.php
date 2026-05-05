<?php
// © Atia Hegazy — atiaeno.com

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'affiliate_enabled'],
            [
                'value' => true,
                'group' => 'affiliate',
                'description' => 'Enable or disable the affiliate program',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('key', 'affiliate_enabled')->delete();
    }
};
