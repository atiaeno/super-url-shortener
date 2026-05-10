<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'format',
        'placement',
        'content',
        'image_path',
        'target_url',
        'target_countries',
        'countdown_seconds',
        'is_active',
    ];

    protected $casts = [
        'target_countries' => 'array',
        'is_active' => 'boolean',
        'countdown_seconds' => 'integer',
    ];

    protected static string $cacheKey = 'ads_all';
    protected static int $cacheTTL = 43200;  // 30 days in minutes

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            Cache::forget(self::$cacheKey);
        });

        static::updated(function () {
            Cache::forget(self::$cacheKey);
        });

        static::deleted(function () {
            Cache::forget(self::$cacheKey);
        });
    }

    public static function getCachedActive()
    {
        return Cache::remember(self::$cacheKey, self::$cacheTTL, function () {
            return self::active()->get();
        });
    }

    public static function getCachedForPlacement(string $placement)
    {
        $ads = self::getCachedActive();
        $filtered = $ads->where('placement', $placement);
        return $filtered->isNotEmpty() ? $filtered->random() : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBanner($query)
    {
        return $query->where('format', 'banner');
    }

    public function scopeInterstitial($query)
    {
        return $query->where('format', 'interstitial');
    }

    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where(function ($q) use ($countryCode) {
            $q
                ->whereNull('target_countries')
                ->orWhereJsonContains('target_countries', $countryCode);
        });
    }

    public function scopeForPlacement($query, string $placement)
    {
        return $query->where('placement', $placement);
    }

    public function scopeRedirect($query)
    {
        return $query->where('placement', 'redirect');
    }

    public function scopeHeader($query)
    {
        return $query->where('placement', 'header');
    }

    public function scopeFooter($query)
    {
        return $query->where('placement', 'footer');
    }

    public function scopeSidebar($query)
    {
        return $query->where('placement', 'sidebar');
    }

    public function scopeLeftSide($query)
    {
        return $query->where('placement', 'left_side');
    }

    public function scopeRightSide($query)
    {
        return $query->where('placement', 'right_side');
    }

    public function scopeBeforeCounter($query)
    {
        return $query->where('placement', 'before_counter');
    }

    public function scopeUnderCounter($query)
    {
        return $query->where('placement', 'under_counter');
    }

    public function scopeAboveButton($query)
    {
        return $query->where('placement', 'above_button');
    }

    public function scopeUnderButton($query)
    {
        return $query->where('placement', 'under_button');
    }

    public function scopePopup($query)
    {
        return $query->where('placement', 'popup');
    }
}
