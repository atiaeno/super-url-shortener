<?php
// © Atia Hegazy — atiaeno.com

namespace App\Console\Commands;

use App\Models\IndexerSetting;
use App\Models\Setting;
use App\Services\IndexerService;
use Illuminate\Console\Command;

class RunIndexer extends Command
{
    protected $signature = 'indexer:run {--force : Force run even if disabled}';

    protected $description = 'Run the URL indexer to submit public links to search engines';

    public function handle(IndexerService $indexer): int
    {
        $settings = IndexerSetting::getSettings();
        $intervalMinutes = (int) Setting::get('indexer_interval_minutes', 60);

        if (!$this->option('force') && $settings->next_run && now()->isBefore($settings->next_run)) {
            $this->info("Skipping - next run at {$settings->next_run} (interval: {$intervalMinutes}min)");
            return Command::SUCCESS;
        }

        $this->info('Starting URL indexer...');

        $results = $indexer->run();

        $this->info('Google Indexer:');
        $this->info("  Processed: {$results['google']['processed']}");
        $this->info("  Success: {$results['google']['success']}");
        $this->info("  Failed: {$results['google']['failed']}");

        $this->info('IndexNow:');
        $this->info("  Processed: {$results['indexnow']['processed']}");
        $this->info("  Success: {$results['indexnow']['success']}");
        $this->info("  Failed: {$results['indexnow']['failed']}");

        $this->info('XML Ping: Completed');

        $stats = $indexer->getQueueStats();
        $this->info("Queue Status: Pending: {$stats['pending']}, Failed: {$stats['failed']}");

        // Update last_run and next_run in database
        $settings->last_run = now();
        $settings->next_run = now()->addMinutes($intervalMinutes);
        $settings->save();

        return Command::SUCCESS;
    }
}
