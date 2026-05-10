<?php

// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class IndexerLog extends Model
{
    protected $table = 'indexer_logs';

    protected $fillable = [
        'link_id',
        'service',
        'response_status',
        'response_message',
        'request_url',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    public function scopeByService($query, string $service)
    {
        return $query->where('service', $service);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
