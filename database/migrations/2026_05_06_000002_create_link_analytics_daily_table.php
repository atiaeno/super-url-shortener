<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_analytics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained()->onDelete('cascade');
            $table->date('date')->index();
            $table->unsignedInteger('total_clicks')->default(0);
            $table->json('by_country')->nullable(); // {"US": 100, "GB": 50}
            $table->json('by_device')->nullable();   // {"mobile": 80, "desktop": 20}
            $table->json('by_browser')->nullable();  // {"Chrome": 100, "Safari": 50}
            $table->json('by_os')->nullable();       // {"Windows": 60, "iOS": 40}
            $table->json('by_referrer')->nullable(); // {"google.com": 100, "twitter.com": 50}
            $table->timestamps();

            $table->unique(['link_id', 'date']);
            $table->index(['link_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_analytics_daily');
    }
};
