<?php

// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indexer_logs', function (Blueprint $table) {
            $table->string('type')->nullable()->after('service');
            $table->string('url')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('indexer_logs', function (Blueprint $table) {
            $table->dropColumn(['type', 'url']);
        });
    }
};
