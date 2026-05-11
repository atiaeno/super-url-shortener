<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('link_analytics_daily', function (Blueprint $table) {
            $table->integer('unique_visitors')->default(0)->after('total_clicks');
            $table->json('unique_ips')->nullable()->after('unique_visitors');
        });
    }

    public function down(): void
    {
        Schema::table('link_analytics_daily', function (Blueprint $table) {
            $table->dropColumn(['unique_visitors', 'unique_ips']);
        });
    }
};
