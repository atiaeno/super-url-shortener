<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateVisit extends Model
{
    protected $fillable = [
        'affiliate_id',
        'affiliate_tier_id',
        'ip_hash',
        'country_code',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(AffiliateTier::class, 'affiliate_tier_id');
    }
}
