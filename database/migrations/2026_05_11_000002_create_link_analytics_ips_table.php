<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_analytics_ips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('link_id');
            $table->date('date');
            $table->string('ip_hash', 64);
            $table->timestamps();

            $table->unique(['link_id', 'date', 'ip_hash']);
            $table->index(['link_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_analytics_ips');
    }
};
