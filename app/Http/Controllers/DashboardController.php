<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Link;
use App\Models\LinkAnalyticsDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle dashboard request.
     */
    public function __invoke(Request $request): Response
    {
        $userId = Auth::id();

        $totalLinks = Link::forUser($userId)->count();
        $activeLinks = Link::forUser($userId)->active()->count();

        // Get fresh total clicks directly from database
        $totalClicks = Link::forUser($userId)->selectRaw('SUM(clicks_count) as total')->first()->total ?? 0;

        // Get user's link IDs
        $linkIds = Link::forUser($userId)->pluck('id');

        // Get summary data
        $today = now()->toDateString();
        $weekAgo = now()->subDays(7)->toDateString();

        $summaries = LinkAnalyticsDaily::whereIn('link_id', $linkIds)->get();

        // Clicks today from summary
        $clicksToday = $summaries->where('date', $today)->sum('total_clicks');

        $recentLinks = Link::forUser($userId)
            ->latest()
            ->limit(5)
            ->get(['id', 'short_code', 'destination_url', 'is_active', 'created_at'])
            ->map(function ($link) use ($userId) {
                // Get fresh click count directly from database with user filter
                $freshData = Link::forUser($userId)->where('id', $link->id)->first(['clicks_count']);
                $link->clicks_count = $freshData->clicks_count ?? 0;
                return $link;
            });

        // Chart data: clicks over last 7 days from summary
        $clicksData = $summaries->where('date', '>=', $weekAgo)->keyBy(fn($r) => $r->date->toDateString());

        $clicksOverTime = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $found = $clicksData->get($date);
            $clicksOverTime[] = [
                'date' => now()->subDays($i)->format('M d'),
                'count' => $found ? (int) $found->total_clicks : 0,
            ];
        }

        // Top countries from summary
        $byCountry = $summaries->pluck('by_country')->filter()->flatMap(fn($c) => (array) $c)->groupBy(fn($v, $k) => $k)->map(fn($v) => $v->sum());

        $topCountries = $byCountry->map(fn($count, $code) => [
            'country' => strtoupper($code),
            'count' => $count,
        ])->sortByDesc('count')->take(5)->values();

        // Device analytics from clicks
        $devices = Click::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        $deviceStats = $devices->map(fn($device) => [
            'device' => ucfirst($device->device_type),
            'count' => $device->count,
        ])->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_links' => $totalLinks,
                'active_links' => $activeLinks,
                'total_clicks' => $totalClicks,
                'clicks_today' => $clicksToday,
            ],
            'recentLinks' => $recentLinks,
            'charts' => [
                'clicks_over_time' => $clicksOverTime,
                'top_countries' => $topCountries,
                'device_stats' => $deviceStats,
            ],
        ]);
    }
}
