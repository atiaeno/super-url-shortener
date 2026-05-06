<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('affiliate_tier_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('visits')->default(0);
            $table->decimal('earnings', 12, 4)->default(0);
            $table->timestamps();

            $table->unique(['affiliate_id', 'affiliate_tier_id']);
            $table->index('affiliate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_stats');
    }
};
