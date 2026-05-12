<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_affiliate_id',
        'referral_affiliate_id',
        'referral_earnings',
        'commission_amount',
        'commission_rate',
        'commission_date',
    ];

    protected $casts = [
        'referral_earnings' => 'float',
        'commission_amount' => 'float',
        'commission_rate' => 'float',
        'commission_date' => 'date',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class, 'referrer_affiliate_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class, 'referral_affiliate_id');
    }

    /**
     * Scope to get commissions for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('commission_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get commissions for a specific referrer.
     */
    public function scopeForReferrer($query, $affiliateId)
    {
        return $query->where('referrer_affiliate_id', $affiliateId);
    }

    /**
     * Scope to get commissions for a specific referral.
     */
    public function scopeForReferral($query, $affiliateId)
    {
        return $query->where('referral_affiliate_id', $affiliateId);
    }
}
