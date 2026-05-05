<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('payouts', 'payment_email')) {
                $table->string('payment_email', 255)->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->string('paypal_email', 255)->change();
        });
    }
};
