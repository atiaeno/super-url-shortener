<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('affiliate_tiers', function (Blueprint $table) {
            // Make commission_rate nullable (for view-based tiers)
            $table->decimal('commission_rate', 5, 2)->nullable()->change();
            // Add view-based rate field (per X views)
            $table->decimal('view_rate', 10, 4)->default(0)->after('commission_rate')->comment('Rate per view_multiplier views');
            $table->integer('view_multiplier')->default(1000)->after('view_rate')->comment('View increment (e.g., 1000 for per 1k, 10000 for per 10k)');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_tiers', function (Blueprint $table) {
            $table->dropColumn(['view_rate', 'view_multiplier']);
        });
    }
};
