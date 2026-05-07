<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestAdsRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-ads-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $controller = new \App\Http\Controllers\Admin\AdController();
            $this->info('AdController instantiated successfully');

            // Test the index method
            $response = $controller->index();
            $this->info('AdController::index() executed successfully');
            $this->info('Response type: ' . get_class($response));

            // Check if ads table exists and has data
            $adsCount = \App\Models\Ad::count();
            $this->info('Ads table count: ' . $adsCount);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
        }
    }
}
