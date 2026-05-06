<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Click;
use App\Models\LinkAnalyticsDaily;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LinkAnalyticsDailySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Aggregating historical click data...');

        $dateRange = Click::selectRaw('MIN(DATE(created_at)) as min_date, MAX(DATE(created_at)) as max_date')
            ->first();

        if (!$dateRange->min_date) {
            $this->command->info('No click data found.');
            return;
        }

        $startDate = Carbon::parse($dateRange->min_date);
        $endDate = Carbon::parse($dateRange->max_date);

        $this->command->info("Processing clicks from {$startDate} to {$endDate}...");

        $currentDate = $startDate;
        $totalLinks = 0;

        while ($currentDate <= $endDate) {
            $clicks = Click::selectRaw('
                link_id,
                DATE(created_at) as click_date,
                country_code,
                device_type,
                browser,
                os,
                referrer_domain,
                COUNT(*) as total
            ')
                ->whereDate('created_at', $currentDate)
                ->groupBy('link_id', 'click_date', 'country_code', 'device_type', 'browser', 'os', 'referrer_domain')
                ->get();

            if ($clicks->isEmpty()) {
                $currentDate = $currentDate->copy()->addDay();
                continue;
            }

            $grouped = $clicks->groupBy('link_id');

            foreach ($grouped as $linkId => $linkClicks) {
                $dateStr = $currentDate->format('Y-m-d');
                $record = LinkAnalyticsDaily::firstOrNew([
                    'link_id' => $linkId,
                    'date' => $dateStr,
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

                $totalLinks++;
            }

            $currentDate = $currentDate->copy()->addDay();
        }

        $this->command->info("Done! Aggregated {$totalLinks} link-day records.");
    }
}
