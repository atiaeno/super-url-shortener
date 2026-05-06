<?php
// © Atia Hegazy — atiaeno.com

namespace App\Console\Commands;

use App\Models\Click;
use App\Models\LinkAnalyticsDaily;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateLinkAnalytics extends Command
{
    protected $signature = 'analytics:aggregate {date? : The date to aggregate (Y-m-d format). Defaults to yesterday.}';

    protected $description = 'Aggregate click data into daily analytics table';

    public function handle(): int
    {
        $date = $this->argument('date') ?? now()->subDay()->toDateString();

        $this->info("Aggregating analytics for {$date}...");

        $clicks = Click::selectRaw("
            link_id,
            DATE(created_at) as click_date,
            country_code,
            device_type,
            browser,
            os,
            referrer_domain,
            COUNT(*) as total
        ")
        ->whereDate('created_at', $date)
        ->groupBy('link_id', 'click_date', 'country_code', 'device_type', 'browser', 'os', 'referrer_domain')
        ->get();

        if ($clicks->isEmpty()) {
            $this->info('No clicks found for the specified date.');
            return Command::SUCCESS;
        }

        $grouped = $clicks->groupBy('link_id');

        foreach ($grouped as $linkId => $linkClicks) {
            $record = LinkAnalyticsDaily::firstOrNew([
                'link_id' => $linkId,
                'date' => $date,
            ]);

            $byCountry = [];
            $byDevice = [];
            $byBrowser = [];
            $byOs = [];
            $byReferrer = [];

            foreach ($linkClicks as $click) {
                $record->total_clicks = ($record->total_clicks ?? 0) + $click->total;

                if ($click->country_code) {
                    $byCountry[$click->country_code] = ($byCountry[$click->country_code] ?? 0) + $click->total;
                }
                if ($click->device_type) {
                    $byDevice[$click->device_type] = ($byDevice[$click->device_type] ?? 0) + $click->total;
                }
                if ($click->browser) {
                    $byBrowser[$click->browser] = ($byBrowser[$click->browser] ?? 0) + $click->total;
                }
                if ($click->os) {
                    $byOs[$click->os] = ($byOs[$click->os] ?? 0) + $click->total;
                }
                if ($click->referrer_domain) {
                    $byReferrer[$click->referrer_domain] = ($byReferrer[$click->referrer_domain] ?? 0) + $click->total;
                }
            }

            $record->by_country = $byCountry;
            $record->by_device = $byDevice;
            $record->by_browser = $byBrowser;
            $record->by_os = $byOs;
            $record->by_referrer = $byReferrer;
            $record->save();
        }

        $this->info("Aggregated {$grouped->count()} links with {$clicks->sum('total')} total clicks.");

        return Command::SUCCESS;
    }
}
