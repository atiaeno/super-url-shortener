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
        Schema::table('links', function (Blueprint $table) {
            // Composite index for user link listings with active status
            $table->index(['user_id', 'is_active', 'created_at'], 'idx_user_active_created');
            
            // Index for fast redirect lookups
            $table->index(['short_code', 'is_active'], 'idx_shortcode_active');
            
            // Index for custom aliases
            $table->index(['custom_alias', 'is_active'], 'idx_custom_alias_active');
            
            // Index for campaign tag filtering
            $table->index(['campaign_tag', 'is_active'], 'idx_campaign_active');
            
            // Index for expiration checks
            $table->index(['expires_at', 'is_active'], 'idx_expires_active');
            
            // Index for domain-specific queries
            $table->index(['domain_id', 'is_active'], 'idx_domain_active');
            
            // Hash index for URL duplicate checking
            $table->index('destination_url_hash', 'idx_destination_url_hash');
        });

        Schema::table('clicks', function (Blueprint $table) {
            // Index for analytics queries
            $table->index(['link_id', 'created_at'], 'idx_link_created');
            $table->index(['link_id', 'ip_hash'], 'idx_link_ip');
            $table->index(['country_code', 'created_at'], 'idx_country_created');
        });

        Schema::table('users', function (Blueprint $table) {
            // Index for admin user lookups
            $table->index('role', 'idx_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex('idx_user_active_created');
            $table->dropIndex('idx_shortcode_active');
            $table->dropIndex('idx_custom_alias_active');
            $table->dropIndex('idx_campaign_active');
            $table->dropIndex('idx_expires_active');
            $table->dropIndex('idx_domain_active');
            $table->dropIndex('idx_destination_url_hash');
        });

        Schema::table('clicks', function (Blueprint $table) {
            $table->dropIndex('idx_link_created');
            $table->dropIndex('idx_link_ip');
            $table->dropIndex('idx_country_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_role');
        });
    }
};
