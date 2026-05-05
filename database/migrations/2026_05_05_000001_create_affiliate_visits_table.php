<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('affiliate_tier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_hash', 64);
            $table->string('country_code', 5)->nullable();
            $table->timestamps();

            // One unique visit per IP per affiliate
            $table->unique(['affiliate_id', 'ip_hash']);
            $table->index(['affiliate_id', 'affiliate_tier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_visits');
    }
};
