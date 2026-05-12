<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add referral tracking to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('referred_by_affiliate_id')->nullable()->after('avatar')->constrained('affiliates')->onDelete('set null');
            $table->index('referred_by_affiliate_id');
        });

        // Add referral earnings fields to affiliates table
        Schema::table('affiliates', function (Blueprint $table) {
            $table->decimal('referral_earnings', 12, 4)->default(0)->after('paid_earnings');
            $table->decimal('referral_pending_earnings', 12, 4)->default(0)->after('referral_earnings');
            $table->decimal('referral_paid_earnings', 12, 4)->default(0)->after('referral_pending_earnings');
        });

        // Create referral commissions log table
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_affiliate_id')->constrained('affiliates')->onDelete('cascade');
            $table->foreignId('referral_affiliate_id')->constrained('affiliates')->onDelete('cascade');
            $table->decimal('referral_earnings', 12, 4)->comment('Earnings of the referral in this period');
            $table->decimal('commission_amount', 12, 4)->comment('Commission earned by referrer');
            $table->decimal('commission_rate', 5, 2)->comment('Commission rate used (e.g., 1.50 for 1.5%)');
            $table->date('commission_date')->comment('Date of the commission period');
            $table->timestamps();

            $table->index('referrer_affiliate_id');
            $table->index('referral_affiliate_id');
            $table->index('commission_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
        
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn(['referral_earnings', 'referral_pending_earnings', 'referral_paid_earnings']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_affiliate_id']);
            $table->dropIndex(['referred_by_affiliate_id']);
            $table->dropColumn('referred_by_affiliate_id');
        });
    }
};
