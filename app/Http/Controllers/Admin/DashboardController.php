<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Link;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Clicks over last 7 days
        $clicksOverTime = Click::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'count' => (int) $item->count,
            ]);

        // Fill missing dates with 0
        $dates = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));
        $clicksOverTime = $dates->map(fn($date) => [
            'date' => $date,
            'count' => $clicksOverTime->firstWhere('date', $date)['count'] ?? 0,
        ]);

        // Top countries
        $topCountries = Click::select('country_code', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'country' => $item->country_code,
                'count' => (int) $item->count,
            ]);

        // Latest registered users
        $latestUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        // Latest payment requests with user info
        $latestPayouts = Payout::with('affiliate.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($payout) {
                return [
                    'id' => $payout->id,
                    'amount' => $payout->amount,
                    'status' => $payout->status,
                    'created_at' => $payout->created_at,
                    'user' => $payout->affiliate?->user ? [
                        'name' => $payout->affiliate->user->name,
                        'email' => $payout->affiliate->user->email,
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'totalLinks' => Link::count(),
                'totalClicks' => Link::sum('clicks_count') ?? 0,
                'pendingPayouts' => Payout::where('status', 'pending')->count(),
                'activeLinks' => Link::where('is_active', true)->count(),
                'flaggedLinks' => Link::whereNotNull('auto_suspended_at')->count(),
            ],
            'charts' => [
                'clicksOverTime' => $clicksOverTime,
                'topCountries' => $topCountries,
            ],
            'latestUsers' => $latestUsers,
            'latestPayouts' => $latestPayouts,
        ]);
    }
}
