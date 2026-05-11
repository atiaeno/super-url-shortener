<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AliasDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'is_active',
        'is_default',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // Relationships
    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'domain_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Cache methods
    public static function getActiveDomains(): array
    {
        return Cache::remember('alias_domains:active', now()->addMonth(), function () {
            return self::active()
                ->orderBy('is_default', 'desc')
                ->orderBy('domain')
                ->get()
                ->map(function ($domain) {
                    return [
                        'id' => $domain->id,
                        'domain' => $domain->domain,
                        'is_default' => $domain->is_default,
                        'description' => $domain->description,
                    ];
                })
                ->toArray();
        });
    }

    public static function getDefaultDomain(): ?self
    {
        return Cache::remember('alias_domains:default', now()->addMonth(), function () {
            return self::active()->default()->first();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('alias_domains:active');
        Cache::forget('alias_domains:default');
    }

    // Events
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    // Validation
    public static function isDomainAvailable(string $domain, ?int $excludeId = null): bool
    {
        $query = self::where('domain', $domain);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    // Security
    public static function validateDomain(string $domain): bool
    {
        // Basic domain validation
        return filter_var($domain, FILTER_VALIDATE_URL) !== false ||
            preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain);
    }
}
