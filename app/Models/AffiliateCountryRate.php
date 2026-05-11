<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AffiliateCountryRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_tier_id',
        'country_code',
        'commission_rate',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(AffiliateTier::class, 'affiliate_tier_id');
    }
}
