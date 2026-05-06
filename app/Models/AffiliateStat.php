<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateStat extends Model
{
    protected $fillable = [
        'affiliate_id',
        'affiliate_tier_id',
        'visits',
        'earnings',
    ];

    protected $casts = [
        'visits' => 'integer',
        'earnings' => 'decimal:4',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(AffiliateTier::class, 'affiliate_tier_id');
    }

    public static function incrementStats(int $affiliateId, int $tierId, float $earned): void
    {
        self::updateOrCreate(
            ['affiliate_id' => $affiliateId, 'affiliate_tier_id' => $tierId],
            []
        )->incrementEach([
            'visits' => 1,
            'earnings' => $earned,
        ]);
    }
}
