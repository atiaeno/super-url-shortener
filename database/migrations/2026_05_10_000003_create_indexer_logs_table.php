<?php

// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indexer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('links')->onDelete('cascade');
            $table->string('service', 50);
            $table->string('response_status')->nullable();
            $table->text('response_message')->nullable();
            $table->text('request_url')->nullable();
            $table->timestamps();

            $table->index('link_id');
            $table->index('service');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indexer_logs');
    }
};
