<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->foreignId('domain_id')->nullable()->after('id')->constrained('alias_domains');

            // Performance indexes
            $table->index(['domain_id', 'short_code']);
            $table->index(['domain_id', 'custom_alias']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['domain_id']);
            $table->dropIndex(['domain_id', 'short_code']);
            $table->dropIndex(['domain_id', 'custom_alias']);
            $table->dropColumn('domain_id');
        });
    }
};
