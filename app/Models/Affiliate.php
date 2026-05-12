<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tier_id',
        'referral_code',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'referral_earnings',
        'referral_pending_earnings',
        'referral_paid_earnings',
        'total_visits',
        'is_active',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:4',
        'pending_earnings' => 'decimal:4',
        'paid_earnings' => 'decimal:4',
        'referral_earnings' => 'decimal:4',
        'referral_pending_earnings' => 'decimal:4',
        'referral_paid_earnings' => 'decimal:4',
        'total_visits' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(AffiliateTier::class, 'tier_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_affiliate_id');
    }

    public function referralCommissionsAsReferrer(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_affiliate_id');
    }

    public function referralCommissionsAsReferral(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referral_affiliate_id');
    }

    /**
     * Determine if affiliate can request a payout (default $50 minimum).
     */
    public function canRequestPayout(float $minimum = 50.0): bool
    {
        return (float) $this->pending_earnings >= $minimum;
    }

    /**
     * Progress percentage toward next tier threshold.
     */
    public function tierProgressPercent(int $nextThreshold): int
    {
        if ($nextThreshold <= 0)
            return 100;

        return (int) min(100, round(($this->total_visits / $nextThreshold) * 100));
    }

    /**
     * Generate a unique 10-char referral code.
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Get total earnings including referral earnings.
     */
    public function getTotalEarningsIncludingReferrals(): float
    {
        return (float) $this->total_earnings + (float) $this->referral_earnings;
    }

    /**
     * Get total pending earnings including referral pending earnings.
     */
    public function getTotalPendingEarningsIncludingReferrals(): float
    {
        return (float) $this->pending_earnings + (float) $this->referral_pending_earnings;
    }

    /**
     * Determine if affiliate can request a payout including referral earnings.
     */
    public function canRequestPayoutWithReferrals(float $minimum = 50.0): bool
    {
        return $this->getTotalPendingEarningsIncludingReferrals() >= $minimum;
    }

    /**
     * Get count of active referred users who have affiliate accounts.
     */
    public function getReferredAffiliatesCount(): int
    {
        return $this
            ->referredUsers()
            ->whereHas('affiliate')
            ->whereHas('affiliate', function ($query) {
                $query->where('is_active', true);
            })
            ->count();
    }
}
