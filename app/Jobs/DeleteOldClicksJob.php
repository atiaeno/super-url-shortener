<?php
// © Atia Hegazy — atiaeno.com

namespace App\Jobs;

use App\Models\Click;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteOldClicksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        // GDPR / data retention: delete click records older than configured days
        // Default 365 days; admin can override via settings key 'gdpr_data_retention_days'
        $retentionDays = (int) Setting::get('gdpr_data_retention_days', 365);

        if ($retentionDays <= 0) {
            return;
        }

        Click::where('created_at', '<', now()->subDays($retentionDays))
            ->chunkById(1000, fn ($rows) => $rows->each->delete());
    }
}
