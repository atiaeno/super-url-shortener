<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Payout;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'totalLinks' => Link::count(),
                'totalClicks' => Link::sum('clicks_count') ?? 0,
                'pendingPayouts' => Payout::where('status', 'pending')->count(),
            ],
        ]);
    }
}
