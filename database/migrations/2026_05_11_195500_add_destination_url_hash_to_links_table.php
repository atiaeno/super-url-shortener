<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->string('destination_url_hash', 64)->nullable()->after('destination_url')->index();
        });

        // Update existing records with hash values (compatible with SQLite)
        if (config('database.default') === 'sqlite') {
            // SQLite doesn't have SHA2, use PHP hash instead
            $links = DB::table('links')->whereNull('destination_url_hash')->get();
            foreach ($links as $link) {
                DB::table('links')
                    ->where('id', $link->id)
                    ->update(['destination_url_hash' => hash('sha256', $link->destination_url)]);
            }
        } else {
            // MySQL/PostgreSQL support SHA2
            DB::statement('UPDATE links SET destination_url_hash = SHA2(destination_url, 256) WHERE destination_url_hash IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('destination_url_hash');
        });
    }
};
