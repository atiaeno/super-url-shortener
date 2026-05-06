<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class AffiliateTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'visit_threshold',
        'commission_rate',
        'view_rate',
        'view_multiplier',
        'is_active',
    ];

    protected $casts = [
        'visit_threshold' => 'integer',
        'commission_rate' => 'decimal:2',
        'view_rate' => 'decimal:4',
        'view_multiplier' => 'integer',
        'is_active' => 'boolean',
    ];

    public function affiliates(): HasMany
    {
        return $this->hasMany(Affiliate::class, 'tier_id');
    }

    public function countryRates(): HasMany
    {
        return $this->hasMany(AffiliateCountryRate::class, 'affiliate_tier_id');
    }

    /**
     * Get commission rate for a specific country, falling back to tier default.
     */
    public function rateForCountry(?string $countryCode): float
    {
        if ($countryCode) {
            $override = $this
                ->countryRates()
                ->where('country_code', strtoupper($countryCode))
                ->value('commission_rate');

            if ($override !== null) {
                return (float) $override;
            }
        }

        return (float) $this->commission_rate;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
