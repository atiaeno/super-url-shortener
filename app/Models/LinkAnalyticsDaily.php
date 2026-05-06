<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LinkAnalyticsDaily extends Model
{
    protected $table = 'link_analytics_daily';

    protected $fillable = [
        'link_id',
        'date',
        'total_clicks',
        'by_country',
        'by_device',
        'by_browser',
        'by_os',
        'by_referrer',
    ];

    protected $casts = [
        'date' => 'date',
        'total_clicks' => 'integer',
        'by_country' => 'array',
        'by_device' => 'array',
        'by_browser' => 'array',
        'by_os' => 'array',
        'by_referrer' => 'array',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    public static function recordClick(int $linkId, array $data): void
    {
        $date = now()->toDateString();

        $record = self::firstOrCreate(
            ['link_id' => $linkId, 'date' => $date]
        );

        $record->increment('total_clicks');

        // Increment JSON fields manually
        foreach (['country_code' => 'by_country', 'device_type' => 'by_device', 'browser' => 'by_browser', 'os' => 'by_os', 'referrer_domain' => 'by_referrer'] as $key => $field) {
            if (!empty($data[$key])) {
                $record->incrementJson($field, $data[$key]);
            }
        }
    }

    public function incrementJson(string $column, string $key, int $value = 1): void
    {
        $current = $this->$column ?? [];
        $current[$key] = ($current[$key] ?? 0) + $value;
        $this->update([$column => $current]);
    }
}
