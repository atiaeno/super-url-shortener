<?php
// © Atia Hegazy — atiaeno.com

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldIps extends Command
{
    protected $signature = 'analytics:cleanup-ips';

    protected $description = 'Delete old unique IP records (keep only today)';

    public function handle(): int
    {
        $cutoff = now()->subDays(2)->toDateString(); // Keep yesterday and today

        $deleted = DB::table('link_analytics_ips')
            ->where('date', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} old IP records.");

        return self::SUCCESS;
    }
}
