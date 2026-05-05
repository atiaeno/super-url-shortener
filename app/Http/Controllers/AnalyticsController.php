<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $userId = Auth::id();

        // Clicks by time period
        $clicksToday = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereDate('created_at', today())->count();

        $clicksWeek = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('created_at', '>=', now()->subDays(7))->count();

        $clicksMonth = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('created_at', '>=', now()->subDays(30))->count();

        $clicksYear = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('created_at', '>=', now()->subDays(365))->count();

        // Device breakdown
        $devices = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        // OS breakdown
        $os = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->selectRaw('os, COUNT(*) as total')
            ->groupBy('os')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Browser breakdown
        $browsers = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->selectRaw('browser, COUNT(*) as total')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Countries
        $countries = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->selectRaw('country_code, COUNT(*) as total')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($row) => array_merge($row->toArray(), [
                'flag' => $this->countryFlag($row->country_code),
            ]));

        // Referrers
        $referrers = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->selectRaw('referrer_domain, COUNT(*) as total')
            ->groupBy('referrer_domain')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Clicks over time (last 30 days)
        $clicksData = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as clicks')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $clicksOverTime = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $found = $clicksData->firstWhere('date', $date);
            $clicksOverTime[] = [
                'date' => now()->subDays($i)->format('M d'),
                'clicks' => $found ? (int) $found->clicks : 0,
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
}
