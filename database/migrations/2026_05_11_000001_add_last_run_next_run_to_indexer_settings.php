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
            $table->timestamp('last_run')->nullable()->after('last_offset');
            $table->timestamp('next_run')->nullable()->after('last_run');
        });
    }

    public function down(): void
    {
        Schema::table('indexer_settings', function (Blueprint $table) {
            $table->dropColumn(['last_run', 'next_run']);
        });
    }
};
