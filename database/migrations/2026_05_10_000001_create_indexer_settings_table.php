<?php

// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indexer_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->integer('links_per_batch')->default(10);
            $table->integer('interval_minutes')->default(5);
            $table->text('google_service_account_json')->nullable();
            $table->boolean('indexnow_enabled')->default(false);
            $table->boolean('xml_ping_enabled')->default(false);
            $table->json('ping_services')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indexer_settings');
    }
};
