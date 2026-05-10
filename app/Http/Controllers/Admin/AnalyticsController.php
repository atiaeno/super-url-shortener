<?php

// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Link;
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

        // Basic stats - single queries
        $stats = [
            'clicks_today' => Click::whereDate('created_at', now()->toDateString())->count(),
            'clicks_week' => Click::where('created_at', '>=', now()->subDays(7))->count(),
            'clicks_month' => Click::where('created_at', '>=', now()->subDays(30))->count(),
            'clicks_year' => Click::where('created_at', '>=', now()->subDays(365))->count(),
            'unique_visitors' => Click::where('created_at', '>=', $startDate)->distinct('ip_hash')->count('ip_hash'),
            'total_links' => Link::count(),
            'active_links' => Link::where('is_active', true)->count(),
        ];

        // Single aggregated query for all chart data
        $chartData = Click::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as clicks'),
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('DAYOFWEEK(created_at) as day'),
            DB::raw('MAX(country_code) as country'),
            DB::raw('MAX(device_type) as device'),
            DB::raw('MAX(browser) as browser'),
            DB::raw('MAX(os) as os'),
            DB::raw('MAX(referrer_domain) as referrer')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date', 'hour', 'day')
            ->get();

        // Process clicks over time
        $clicksOverTime = $chartData->groupBy('date')->map(fn($g) => ['date' => $g->first()->date, 'clicks' => $g->count()])->values();

        // Hourly distribution
        $hourlyDistribution = $chartData->groupBy('hour')->map(fn($g, $k) => ['hour' => (int) $k, 'count' => $g->count()])->values();

        // Day of week
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dayOfWeek = $chartData->groupBy('day')->map(fn($g, $k) => ['day' => $days[$k - 1] ?? 'Unknown', 'count' => $g->count()])->values();

        // Top countries - separate lightweight query
        $topCountries = Click::select('country_code', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['country_code' => $i->country_code, 'count' => (int) $i->count, 'flag' => $this->countryFlag($i->country_code)]);

        // Devices
        $devices = Click::select('device_type', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get()
            ->map(fn($i) => ['device_type' => $i->device_type, 'count' => (int) $i->count]);

        // Browsers
        $browsers = Click::select('browser', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn($i) => ['browser' => $i->browser, 'count' => (int) $i->count]);

        // OS
        $os = Click::select('os', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn($i) => ['os' => $i->os, 'count' => (int) $i->count]);

        // Referrers
        $referrers = Click::select('referrer_domain', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('referrer_domain')
            ->groupBy('referrer_domain')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['referrer_domain' => $i->referrer_domain, 'count' => (int) $i->count]);

        // Top links - use clicks_count from links table (no join)
        $topLinks = Link::select('id', 'short_code', 'destination_url', 'clicks_count')
            ->orderByDesc('clicks_count')
            ->limit(20)
            ->get()
            ->map(fn($i) => ['id' => $i->id, 'short_code' => $i->short_code, 'url' => $i->destination_url, 'total_clicks' => (int) ($i->clicks_count ?? 0)])
            ->take(15);

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

        // Recent clicks - limit to 20
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

        $stats = [
            'total_clicks' => Click::where('link_id', $id)->where('created_at', '>=', $startDate)->count(),
            'unique_visitors' => Click::where('link_id', $id)->where('created_at', '>=', $startDate)->distinct('ip_hash')->count('ip_hash'),
            'avg_per_day' => round(Click::where('link_id', $id)->where('created_at', '>=', $startDate)->count() / max($range, 1), 1),
        ];

        $clicksOverTime = Click::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($i) => ['date' => $i->date, 'clicks' => (int) $i->count]);

        $hourly = Click::select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($i) => ['hour' => (int) $i->hour, 'count' => (int) $i->count]);

        $countries = Click::select('country_code', DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['country_code' => $i->country_code, 'count' => (int) $i->count, 'flag' => $this->countryFlag($i->country_code)]);

        $devices = Click::select('device_type', DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get()
            ->map(fn($i) => ['device_type' => $i->device_type, 'count' => (int) $i->count]);

        $browsers = Click::select('browser', DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn($i) => ['browser' => $i->browser, 'count' => (int) $i->count]);

        $os = Click::select('os', DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn($i) => ['os' => $i->os, 'count' => (int) $i->count]);

        $referrers = Click::select('referrer_domain', DB::raw('COUNT(*) as count'))
            ->where('link_id', $id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('referrer_domain')
            ->groupBy('referrer_domain')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($i) => ['referrer_domain' => $i->referrer_domain, 'count' => (int) $i->count]);

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
