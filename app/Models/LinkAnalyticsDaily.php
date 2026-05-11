<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LinkAnalyticsDaily extends Model
{
    use HasFactory;

    protected $table = 'link_analytics_daily';

    protected $fillable = [
        'link_id',
        'date',
        'total_clicks',
        'unique_visitors',
        'by_country',
        'by_device',
        'by_browser',
        'by_os',
        'by_referrer',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'total_clicks' => 'integer',
        'unique_visitors' => 'integer',
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
        $ipHash = $data['ip_hash'] ?? null;

        $isNewVisitor = false;
        if ($ipHash) {
            $isNewVisitor = self::insertUniqueIp($linkId, $date, $ipHash);
        }

        \Illuminate\Support\Facades\DB::table('link_analytics_daily')->insertOrIgnore([
            'link_id' => $linkId,
            'date' => $date,
            'total_clicks' => 0,
            'unique_visitors' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $record = self::where('link_id', $linkId)->whereDate('date', $date)->first();
        $record->increment('total_clicks');

        if ($isNewVisitor) {
            $record->increment('unique_visitors');
        }

        foreach (['country_code' => 'by_country', 'device_type' => 'by_device', 'browser' => 'by_browser', 'os' => 'by_os', 'referrer_domain' => 'by_referrer'] as $key => $field) {
            if (!empty($data[$key])) {
                $record->incrementJson($field, $data[$key]);
            }
        }
    }

    /**
     * Check and record unique IP - returns true if new, false if duplicate
     */
    private static function insertUniqueIp(int $linkId, string $date, string $ipHash): bool
    {
        try {
            return \Illuminate\Support\Facades\DB::table('link_analytics_ips')->insertOrIgnore([
                'link_id' => $linkId,
                'date' => $date,
                'ip_hash' => $ipHash,
                'created_at' => now(),
                'updated_at' => now(),
            ]) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function incrementJson(string $column, string $key, int $value = 1): void
    {
        $current = $this->$column ?? [];
        $current[$key] = ($current[$key] ?? 0) + $value;
        $this->update([$column => $current]);
    }
}
