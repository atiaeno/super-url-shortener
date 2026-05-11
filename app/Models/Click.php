<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'link_id',
        'ip_hash',
        'country_code',
        'device_type',
        'browser',
        'os',
        'referrer',
        'referrer_domain',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    public function scopeForLink($query, $linkId)
    {
        return $query->where('link_id', $linkId);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeFromCountry($query, $countryCode)
    {
        return $query->whereRaw('UPPER(country_code) = ?', [strtoupper($countryCode)]);
    }

    public function scopeOnDevice($query, $deviceType)
    {
        return $query->whereRaw('LOWER(device_type) = ?', [strtolower($deviceType)]);
    }

    public function scopeWithReferrer($query, $domain)
    {
        return $query->where('referrer_domain', $domain);
    }
}
