<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Affiliate Tiers
        Schema::create('affiliate_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('visit_threshold')->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Affiliate Country Rates (per-tier overrides)
        Schema::create('affiliate_country_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_tier_id')->constrained()->onDelete('cascade');
            $table->string('country_code', 5);
            $table->decimal('commission_rate', 5, 2);
            $table->timestamps();

            $table->unique(['affiliate_tier_id', 'country_code']);
        });

        // Affiliates (users enrolled in program)
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tier_id')->constrained('affiliate_tiers');
            $table->string('referral_code', 50)->unique();
            $table->decimal('total_earnings', 12, 4)->default(0);
            $table->decimal('pending_earnings', 12, 4)->default(0);
            $table->decimal('paid_earnings', 12, 4)->default(0);
            $table->bigInteger('total_visits')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('referral_code');
        });

        // Payouts
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->string('paypal_email', 255);
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('affiliate_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('affiliate_country_rates');
        Schema::dropIfExists('affiliate_tiers');
    }
};
