<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'domain_id',
        'short_code',
        'destination_url',
        'destination_url_hash',
        'custom_alias',
        'campaign_tag',
        'og_title',
        'og_description',
        'og_image',
        'is_active',
        'visibility',
        'password',
        'expires_at',
        'clicks_count',
        'report_count',
        'auto_suspended_at',
        'ad_id',
        'ad_override',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'visibility' => 'string',
        'expires_at' => 'datetime',
        'clicks_count' => 'integer',
        'report_count' => 'integer',
        'auto_suspended_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($link) {
            // Always sync hash whenever destination_url changes
            if ($link->isDirty('destination_url')) {
                $link->destination_url_hash = hash('sha256', $link->destination_url);
            }

            // Only hash password when it has actually changed
            if ($link->isDirty('password') && $link->visibility === 'private' && $link->password) {
                $link->password = bcrypt($link->password);
            }
            if ($link->visibility === 'public') {
                $link->password = null;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(AliasDomain::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getShortUrlAttribute(): string
    {
        $code = $this->custom_alias ?: $this->short_code;

        // Use domain from relationship if available, otherwise default to app.url
        $domain = $this->domain?->domain ?? config('app.url');

        // Ensure domain has protocol
        if (!str_starts_with($domain, 'http')) {
            $domain = 'https://' . $domain;
        }

        return rtrim($domain, '/') . '/' . $code;
    }

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }
}
