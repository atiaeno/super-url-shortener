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
            $table->integer('last_offset')->default(0)->after('ping_services');
        });
    }

    public function down(): void
    {
        Schema::table('indexer_settings', function (Blueprint $table) {
            $table->dropColumn('last_offset');
        });
    }
};
