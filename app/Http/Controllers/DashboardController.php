<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $totalClicks = Link::forUser($userId)->sum('clicks_count');

        $clicksToday = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereDate('created_at', today())->count();

        $recentLinks = Link::forUser($userId)
            ->latest()
            ->limit(5)
            ->get(['id', 'short_code', 'destination_url', 'clicks_count', 'is_active', 'created_at']);

        // Chart data: clicks over last 7 days
        $clicksData = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Build array with all 7 days, filling missing with 0
        $clicksOverTime = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $found = $clicksData->firstWhere('date', $date);
            $clicksOverTime[] = [
                'date' => now()->subDays($i)->format('M d'),
                'count' => $found ? (int) $found->count : 0,
            ];
        }

        // Top countries
        $topCountries = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->whereNotNull('country_code')
            ->selectRaw('country_code, COUNT(*) as count')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'country' => $item->country_code,
                'count' => $item->count,
            ]);

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
            ],
        ]);
    }
}
