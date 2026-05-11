<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkAnalyticsDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $userId = Auth::id();

        // Get user's link IDs
        $linkIds = Link::where('user_id', $userId)->pluck('id');

        // Get summary data for last 365 days
        $summaries = LinkAnalyticsDaily::whereIn('link_id', $linkIds)
            ->where('date', '>=', now()->subDays(365)->toDateString())
            ->get();

        // Aggregate by period
        $today = now()->toDateString();
        $weekAgo = now()->subDays(7)->toDateString();
        $monthAgo = now()->subDays(30)->toDateString();
        $yearAgo = now()->subDays(365)->toDateString();

        $clicksToday = $summaries->filter(fn($r) => $r->date->toDateString() === $today)->sum('total_clicks');
        $clicksWeek = $summaries->filter(fn($r) => $r->date->toDateString() >= $weekAgo)->sum('total_clicks');
        $clicksMonth = $summaries->filter(fn($r) => $r->date->toDateString() >= $monthAgo)->sum('total_clicks');
        $clicksYear = $summaries->sum('total_clicks');

        // Aggregate JSON fields efficiently using SQL
        $byDevice = $this->aggregateJsonFieldSql($linkIds, $yearAgo, 'by_device');
        $byOs = $this->aggregateJsonFieldSql($linkIds, $yearAgo, 'by_os');
        $byBrowser = $this->aggregateJsonFieldSql($linkIds, $yearAgo, 'by_browser');
        $byCountry = $this->aggregateJsonFieldSql($linkIds, $yearAgo, 'by_country');
        $byReferrer = $this->aggregateJsonFieldSql($linkIds, $yearAgo, 'by_referrer');

        $devices = $byDevice->map(fn($total, $name) => ['device_type' => $name, 'total' => $total])->values();
        $os = $byOs->map(fn($total, $name) => ['os' => $name, 'total' => $total])->sortByDesc('total')->take(10)->values();
        $browsers = $byBrowser->map(fn($total, $name) => ['browser' => $name, 'total' => $total])->sortByDesc('total')->take(10)->values();
        $countries = $byCountry->map(fn($total, $code) => ['country_code' => $code, 'total' => $total, 'flag' => $this->countryFlag($code)])->sortByDesc('total')->take(10)->values();
        $referrers = $byReferrer->map(fn($total, $name) => ['referrer_domain' => $name, 'total' => $total])->sortByDesc('total')->take(10)->values();

        // Clicks over time (last 30 days) - group by date and sum across all links
        $clicksData = $summaries
            ->where('date', '>=', $monthAgo)
            ->groupBy(fn($r) => $r->date->toDateString())
            ->map(fn($group) => $group->sum('total_clicks'));
        $clicksOverTime = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $clicksOverTime[] = [
                'date' => now()->subDays($i)->format('M d'),
                'clicks' => (int) ($clicksData->get($date) ?? 0),
            ];
        }

        return Inertia::render('Analytics', [
            'stats' => [
                'clicks_today' => $clicksToday,
                'clicks_week' => $clicksWeek,
                'clicks_month' => $clicksMonth,
                'clicks_year' => $clicksYear,
            ],
            'devices' => $devices,
            'os' => $os,
            'browsers' => $browsers,
            'countries' => $countries,
            'referrers' => $referrers,
            'clicks_over_time' => $clicksOverTime,
        ]);
    }

    private function countryFlag(?string $code): string
    {
        if (!$code || strlen($code) !== 2) {
            return '🌐';
        }
        $code = strtoupper($code);
        $flag = '';
        foreach (str_split($code) as $char) {
            $flag .= mb_chr(ord($char) - ord('A') + 0x1F1E6);
        }
        return $flag;
    }

    private function aggregateJsonFieldSql($linkIds, string $yearAgo, string $field): \Illuminate\Support\Collection
    {
        // Only fetch the JSON column we need - much less data transfer
        $rows = DB::table('link_analytics_daily')
            ->whereIn('link_id', $linkIds)
            ->where('date', '>=', $yearAgo)
            ->whereNotNull($field)
            ->pluck($field);

        $totals = [];
        foreach ($rows as $json) {
            $data = json_decode($json, true);
            if (!is_array($data))
                continue;
            foreach ($data as $key => $value) {
                $totals[$key] = ($totals[$key] ?? 0) + $value;
            }
        }

        return collect($totals);
    }
}
