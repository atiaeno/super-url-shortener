<?php

// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indexer_settings', function (Blueprint $table) {
            $table->integer('google_daily_limit')->default(200)->after('links_per_batch');
            $table->date('google_links_sent_date')->nullable()->after('google_daily_limit');
            $table->integer('google_links_sent_today')->default(0)->after('google_links_sent_date');
        });
    }

    public function down(): void
    {
        Schema::table('indexer_settings', function (Blueprint $table) {
            $table->dropColumn(['google_daily_limit', 'google_links_sent_date', 'google_links_sent_today']);
        });
    }
};
