<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    /**
     * Report a link as spam/malicious.
     */
    public function store(Request $request, Link $link)
    {
        $validated = $request->validate([
            'reason' => 'required|in:spam,phishing,malware,violence,other',
            'details' => 'nullable|string|max:1000',
        ]);

        $ipHash = hash('sha256', $request->ip());

        // Prevent duplicate reports from same IP within 24 hours
        $cacheKey = "report:{$link->id}:{$ipHash}";
        if (Cache::has($cacheKey)) {
            return response()->json([
                'error' => 'You have already reported this link recently.',
            ], 429);
        }

        // Create the report
        Report::create([
            'link_id' => $link->id,
            'reporter_ip_hash' => $ipHash,
            'reason' => $validated['reason'],
            'details' => $validated['details'],
        ]);

        // Increment report count
        $link->increment('report_count');

        // Auto-suspend if threshold reached
        $threshold = config('app.auto_suspend_threshold', 3);

        // Count unique IPs in last 24 hours
        $recentReports = $link
            ->reports()
            ->where('created_at', '>=', now()->subDay())
            ->distinct('reporter_ip_hash')
            ->count('reporter_ip_hash');

        if ($recentReports >= $threshold && !$link->auto_suspended_at) {
            $link->update([
                'is_active' => false,
                'auto_suspended_at' => now(),
            ]);

            // Notify admins (optional, via queue)
            // SendAdminNotification::dispatch($link, $recentReports);
        }

        // Cache this report for 24h to prevent spam
        Cache::put($cacheKey, true, now()->addDay());

        return response()->json([
            'message' => 'Report submitted successfully. Thank you for keeping our platform safe.',
        ]);
    }

    /**
     * Show report form for a link.
     */
    public function create(Link $link)
    {
        return view('report.create', compact('link'));
    }
}
