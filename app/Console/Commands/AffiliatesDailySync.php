<?php
// © Atia Hegazy — atiaeno.com

namespace App\Console\Commands;

use App\Jobs\SyncAffiliateEarningsJob;
use App\Models\AffiliateVisit;
use Illuminate\Console\Command;

class AffiliatesDailySync extends Command
{
    protected $signature = 'affiliates:daily-sync';
    protected $description = 'Sync affiliate earnings and prune visits older than 30 days';

    public function handle(): void
    {
        // 1. Dispatch earnings sync for all affiliates
        SyncAffiliateEarningsJob::dispatch();
        $this->info('Earnings sync job dispatched.');

        // 2. Prune visits older than 30 days
        $deleted = AffiliateVisit::where('created_at', '<', now()->subDays(30))->delete();
        $this->info("Pruned {$deleted} old visit records.");
    }
}
