<?php

// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Link;
use App\Models\LinkAnalyticsDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function overview(Request $request): Response
    {
        $range = (int) $request->input('range', 30);
        $startDate = now()->subDays($range);

        // Fetch summary rows for the range
        $dailyRows = LinkAnalyticsDaily::where('date', '>=', $startDate)->get();

        // Basic stats from summary table
        $totalClicks = $dailyRows->sum('total_clicks');
        $stats = [
            'clicks_today' => LinkAnalyticsDaily::whereDate('date', now()->toDateString())->sum('total_clicks'),
            'clicks_week' => LinkAnalyticsDaily::where('date', '>=', now()->subDays(7))->sum('total_clicks'),
            'clicks_month' => $range >= 30 ? $totalClicks : LinkAnalyticsDaily::where('date', '>=', now()->subDays(30))->sum('total_clicks'),
            'clicks_year' => LinkAnalyticsDaily::where('date', '>=', now()->subDays(365))->sum('total_clicks'),
            'unique_visitors' => Click::where('created_at', '>=', $startDate)->distinct('ip_hash')->count('ip_hash'),
            'total_links' => Link::count(),
            'active_links' => Link::where('is_active', true)->count(),
        ];

        // Clicks over time from summary
        $clicksOverTime = $dailyRows
            ->groupBy(fn($row) => $row->date->toDateString())
            ->map(fn($g, $date) => ['date' => $date, 'clicks' => $g->sum('total_clicks')])
            ->sortKeys()
            ->values();

        // Hourly distribution - still needs clicks table (not in summary)
        $hourlyDistribution = Click::select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($i) => ['hour' => (int) $i->hour, 'count' => (int) $i->count]);

        // Day of week - still needs clicks table
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dayOfWeek = Click::select(DB::raw('DAYOFWEEK(created_at) as day'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('day')
            ->get()
            ->map(fn($i) => ['day' => $days[$i->day - 1] ?? 'Unknown', 'count' => (int) $i->count])
            ->values();

        // Top countries from summary JSON
        $topCountries = $this
            ->aggregateJsonField($dailyRows, 'by_country', 10)
            ->map(fn($count, $code) => ['country_code' => $code, 'count' => $count, 'flag' => $this->countryFlag($code)])
            ->values();

        // Devices from summary JSON
        $devices = $this
            ->aggregateJsonField($dailyRows, 'by_device', 10)
            ->map(fn($count, $type) => ['device_type' => $type, 'count' => $count])
            ->values();

        // Browsers from summary JSON
        $browsers = $this
            ->aggregateJsonField($dailyRows, 'by_browser', 8)
            ->map(fn($count, $name) => ['browser' => $name, 'count' => $count])
            ->values();

        // OS from summary JSON
        $os = $this
            ->aggregateJsonField($dailyRows, 'by_os', 8)
            ->map(fn($count, $name) => ['os' => $name, 'count' => $count])
            ->values();

        // Referrers from summary JSON
        $referrers = $this
            ->aggregateJsonField($dailyRows, 'by_referrer', 10)
            ->map(fn($count, $domain) => ['referrer_domain' => $domain, 'count' => $count])
            ->values();

        // Top links - use clicks_count from links table (no join)
        $topLinks = Link::select('id', 'short_code', 'destination_url', 'clicks_count')
            ->orderByDesc('clicks_count')
            ->limit(15)
            ->get()
            ->map(fn($i) => ['id' => $i->id, 'short_code' => $i->short_code, 'url' => $i->destination_url, 'total_clicks' => (int) ($i->clicks_count ?? 0)]);

        // New links
        $newLinks = Link::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($i) => ['date' => $i->date, 'count' => (int) $i->count]);

        // New users
        $newUsers = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($i) => ['date' => $i->date, 'count' => (int) $i->count]);

        // Recent clicks - need individual rows from clicks table
        $recentClicks = Click::select('id', 'link_id', 'country_code', 'device_type', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(fn($i) => ['id' => $i->id, 'link_id' => $i->link_id, 'country_code' => $i->country_code, 'device_type' => $i->device_type, 'created_at' => $i->created_at]);

        return Inertia::render('Admin/Analytics', [
            'stats' => $stats,
            'range' => $range,
            'charts' => [
                'clicks_over_time' => $clicksOverTime,
                'hourly_distribution' => $hourlyDistribution,
                'day_of_week' => $dayOfWeek,
                'top_countries' => $topCountries,
                'devices' => $devices,
                'browsers' => $browsers,
                'os' => $os,
                'referrers' => $referrers,
                'new_links' => $newLinks,
                'new_users' => $newUsers,
            ],
            'top_links' => $topLinks,
            'recent_clicks' => $recentClicks,
        ]);
    }

    public function linkAnalytics(Request $request, int $id): Response
    {
        $link = Link::findOrFail($id);
        $range = (int) $request->input('range', 30);
        $startDate = now()->subDays($range);

        // Fetch summary rows for this link
        $dailyRows = LinkAnalyticsDaily::where('link_id', $id)
            ->where('date', '>=', $startDate)
            ->get();

        $totalClicks = $dailyRows->sum('total_clicks');

        $stats = [
            'total_clicks' => $totalClicks,
            'unique_visitors' => Click::where('link_id', $id)->where('created_at', '>=', $startDate)->distinct('ip_hash')->count('ip_hash'),
            'avg_per_day' => round($totalClicks / max($range, 1), 1),
        ];

        // Clicks over time from summary
        $clicksOverTime = $dailyRows
            ->map(fn($row) => ['date' => $row->date->toDateString(), 'clicks' => $row->total_clicks])
            ->sortBy('date')
            ->values();

        // Hourly - still needs clicks table (not stored in summary)
        $hourly = Click::select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($i) => ['hour' => (int) $i->hour, 'count' => (int) $i->count]);

        // Countries from summary JSON
        $countries = $this
            ->aggregateJsonField($dailyRows, 'by_country', 10)
            ->map(fn($count, $code) => ['country_code' => $code, 'count' => $count, 'flag' => $this->countryFlag($code)])
            ->values();

        // Devices from summary JSON
        $devices = $this
            ->aggregateJsonField($dailyRows, 'by_device', 10)
            ->map(fn($count, $type) => ['device_type' => $type, 'count' => $count])
            ->values();

        // Browsers from summary JSON
        $browsers = $this
            ->aggregateJsonField($dailyRows, 'by_browser', 8)
            ->map(fn($count, $name) => ['browser' => $name, 'count' => $count])
            ->values();

        // OS from summary JSON
        $os = $this
            ->aggregateJsonField($dailyRows, 'by_os', 8)
            ->map(fn($count, $name) => ['os' => $name, 'count' => $count])
            ->values();

        // Referrers from summary JSON
        $referrers = $this
            ->aggregateJsonField($dailyRows, 'by_referrer', 10)
            ->map(fn($count, $domain) => ['referrer_domain' => $domain, 'count' => $count])
            ->values();

        // Recent clicks - need individual rows
        $recentClicks = Click::where('link_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($i) => ['id' => $i->id, 'country_code' => $i->country_code, 'device_type' => $i->device_type, 'browser' => $i->browser, 'referrer' => $i->referrer, 'created_at' => $i->created_at]);

        return Inertia::render('Admin/Links/Analytics', [
            'link' => ['id' => $link->id, 'short_code' => $link->short_code, 'url' => $link->destination_url, 'created_at' => $link->created_at],
            'stats' => $stats,
            'range' => $range,
            'charts' => ['clicks_over_time' => $clicksOverTime, 'hourly' => $hourly, 'countries' => $countries, 'devices' => $devices, 'browsers' => $browsers, 'os' => $os, 'referrers' => $referrers],
            'recent_clicks' => $recentClicks,
        ]);
    }

    public function userAnalytics(Request $request, int $id): Response
    {
        $user = User::findOrFail($id);
        $range = (int) $request->input('range', 30);
        $startDate = now()->subDays($range);

        $userLinkIds = Link::where('user_id', $id)->pluck('id');

        $stats = [
            'total_links' => Link::where('user_id', $id)->count(),
            'total_clicks' => Click::whereIn('link_id', $userLinkIds)->where('created_at', '>=', $startDate)->count(),
            'active_links' => Link::where('user_id', $id)->where('is_active', true)->count(),
            'avg_clicks_per_link' => $userLinkIds->isNotEmpty() ? round(Click::whereIn('link_id', $userLinkIds)->where('created_at', '>=', $startDate)->count() / max($userLinkIds->count(), 1), 1) : 0,
        ];

        $clicksOverTime = Click::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereIn('link_id', $userLinkIds)
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($i) => ['date' => $i->date, 'clicks' => (int) $i->count]);

        $countries = Click::select('country_code', DB::raw('COUNT(*) as count'))
            ->whereIn('link_id', $userLinkIds)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn($i) => ['country_code' => $i->country_code, 'count' => (int) $i->count, 'flag' => $this->countryFlag($i->country_code)]);

        $devices = Click::select('device_type', DB::raw('COUNT(*) as count'))
            ->whereIn('link_id', $userLinkIds)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get()
            ->map(fn($i) => ['device_type' => $i->device_type, 'count' => (int) $i->count]);

        $links = Link::where('user_id', $id)->get()->map(fn($i) => ['id' => $i->id, 'short_code' => $i->short_code, 'url' => $i->destination_url, 'is_active' => $i->is_active, 'total_clicks' => (int) ($i->clicks_count ?? 0), 'created_at' => $i->created_at])->sortByDesc('total_clicks')->values();

        return Inertia::render('Admin/Users/Analytics', [
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'created_at' => $user->created_at],
            'stats' => $stats,
            'range' => $range,
            'charts' => ['clicks_over_time' => $clicksOverTime, 'countries' => $countries, 'devices' => $devices],
            'links' => $links,
        ]);
    }

    /**
     * Aggregate a JSON field from daily summary rows into a sorted collection.
     */
    private function aggregateJsonField($dailyRows, string $field, int $limit)
    {
        $aggregated = [];

        foreach ($dailyRows as $row) {
            $data = $row->$field ?? [];
            foreach ($data as $key => $count) {
                if (!empty($key)) {
                    $aggregated[$key] = ($aggregated[$key] ?? 0) + $count;
                }
            }
        }

        arsort($aggregated);

        return collect(array_slice($aggregated, 0, $limit, true));
    }

    private function countryFlag(?string $code): string
    {
        if (!$code || strlen($code) !== 2)
            return '';
        $code = strtoupper($code);
        $flag = '';
        foreach (str_split($code) as $char)
            $flag .= mb_chr(ord($char) - ord('A') + 0x1F1E6);
        return $flag;
    }
}
