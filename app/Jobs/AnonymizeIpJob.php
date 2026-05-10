<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnonymizeIpJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Anonymize IP hashes for clicks older than the retention period.
     */
    public function handle(): void
    {
        $retentionDays = (int) config('app.ip_retention_days', 30);
        $cutoff = now()->subDays($retentionDays);

        \App\Models\Click::where('created_at', '<', $cutoff)
            ->whereNotNull('ip_hash')
            ->chunkById(500, function ($clicks) {
                foreach ($clicks as $click) {
                    $click->update([
                        'ip_hash' => hash('sha256', 'anonymized-' . $click->id),
                    ]);
                }
            });
    }
}
