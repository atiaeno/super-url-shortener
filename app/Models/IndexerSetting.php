<?php

// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndexerSetting extends Model
{
    protected $table = 'indexer_settings';

    protected $fillable = [
        'enabled',
        'links_per_batch',
        'interval_minutes',
        'google_service_account_json',
        'google_daily_limit',
        'indexnow_enabled',
        'indexnow_key',
        'xml_ping_enabled',
        'ping_services',
        'last_offset',
        'google_links_sent_date',
        'google_links_sent_today',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'indexnow_enabled' => 'boolean',
        'xml_ping_enabled' => 'boolean',
        'links_per_batch' => 'integer',
        'interval_minutes' => 'integer',
        'google_daily_limit' => 'integer',
        'google_links_sent_today' => 'integer',
        'last_offset' => 'integer',
        'ping_services' => 'array',
    ];

    public static function getSettings()
    {
        return self::first() ?? self::create([
            'enabled' => false,
            'links_per_batch' => 10,
            'interval_minutes' => 5,
            'indexnow_enabled' => false,
            'xml_ping_enabled' => false,
            'ping_services' => ['google', 'bing'],
        ]);
    }
}
