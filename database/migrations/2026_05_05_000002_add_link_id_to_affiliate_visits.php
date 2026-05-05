<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_visits', function (Blueprint $table) {
            $table->foreignId('link_id')->nullable()->after('affiliate_tier_id')->constrained()->onDelete('set null');
            $table->date('visit_date')->after('country_code')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_visits', function (Blueprint $table) {
            $table->dropForeign(['link_id']);
            $table->dropColumn(['link_id', 'visit_date']);
        });
    }
};
