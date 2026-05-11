<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AffiliateVisit extends Model
{
    use HasFactory;

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
