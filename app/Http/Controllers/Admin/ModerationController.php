<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Link;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModerationController extends Controller
{
    /**
     * Display moderation queue with pending reports.
     */
    public function index(): Response
    {
        $pendingReports = Report::with('link')
            ->pending()
            ->latest()
            ->paginate(25);

        $flaggedLinks = Link::where('report_count', '>', 0)
            ->withCount('reports')
            ->orderByDesc('report_count')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Moderation/Index', [
            'reports' => $pendingReports,
            'flaggedLinks' => $flaggedLinks,
            'stats' => [
                'pending' => Report::pending()->count(),
                'today' => Report::whereDate('created_at', today())->count(),
                'auto_suspended' => Link::whereNotNull('auto_suspended_at')->count(),
            ],
        ]);
    }

    /**
     * Display flagged links.
     */
    public function flagged(): Response
    {
        $flaggedLinks = Link::where('report_count', '>', 0)
            ->withCount('reports')
            ->with(['reports' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->orderByDesc('report_count')
            ->paginate(50);

        return Inertia::render('Admin/Moderation/Flagged', [
            'flaggedLinks' => $flaggedLinks,
            'stats' => [
                'flagged' => Link::where('report_count', '>', 0)->count(),
                'auto_suspended' => Link::whereNotNull('auto_suspended_at')->count(),
                'high_priority' => Link::where('report_count', '>=', 5)->count(),
            ],
        ]);
    }

    /**
     * Display all reports queue.
     */
    public function reports(Request $request): Response
    {
        $query = Report::with(['link', 'reviewer']);

        // Search by link short code or destination URL
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('link', function ($q) use ($search) {
                $q
                    ->where('short_code', 'like', "%{$search}%")
                    ->orWhere('destination_url', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter by reason
        if ($request->filled('reason') && $request->input('reason') !== 'all') {
            $query->where('reason', $request->input('reason'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Filter by reviewer
        if ($request->filled('reviewer_id') && $request->input('reviewer_id') !== 'all') {
            $query->where('reviewed_by', $request->input('reviewer_id'));
        }

        $reports = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Moderation/Reports', [
            'reports' => $reports,
            'filters' => $request->only(['search', 'status', 'reason', 'date_from', 'date_to', 'reviewer_id']),
            'reviewers' => \App\Models\User::whereHas('reviewedReports')
                ->select('id', 'name')
                ->distinct()
                ->orderBy('name')
                ->get(),
            'stats' => [
                'pending' => Report::pending()->count(),
                'reviewed' => Report::reviewed()->count(),
                'today' => Report::whereDate('created_at', today())->count(),
            ],
        ]);
    }

    /**
     * Review a report and take action.
     */
    public function review(Request $request, Report $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:dismiss,deactivate,delete',
            'notes' => 'nullable|string|max:1000',
        ]);

        $link = $report->link;

        switch ($validated['action']) {
            case 'dismiss':
                $report->markReviewed(auth()->id(), 'dismissed', $validated['notes']);
                break;

            case 'deactivate':
                $link->update(['is_active' => false]);
                $report->markReviewed(auth()->id(), 'actioned', $validated['notes']);

                // Close all pending reports for this link
                $link->reports()->pending()->update([
                    'status' => 'actioned',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
                break;

            case 'delete':
                $link->delete();
                $report->markReviewed(auth()->id(), 'actioned', $validated['notes']);
                break;
        }

        // Log the action
        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'report_reviewed',
            'target_type' => 'report',
            'target_id' => $report->id,
            'metadata' => [
                'link_id' => $link->id,
                'action_taken' => $validated['action'],
                'notes' => $validated['notes'],
            ],
        ]);

        return redirect()->back()->with('success', 'Report reviewed successfully.');
    }

    /**
     * Show activity log of all moderation actions.
     */
    public function activityLog(): Response
    {
        $logs = ActivityLog::with('actor')
            ->where('action', 'like', 'report_%')
            ->orWhere('action', 'like', 'link_%')
            ->latest()
            ->paginate(50);

        return Inertia::render('Admin/Moderation/ActivityLog', [
            'logs' => $logs,
        ]);
    }

    /**
     * Batch review multiple reports.
     */
    public function batchReview(Request $request)
    {
        $validated = $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:reports,id',
            'action' => 'required|in:dismiss,deactivate',
            'notes' => 'nullable|string',
        ]);

        $reports = Report::whereIn('id', $validated['report_ids'])->get();

        foreach ($reports as $report) {
            if ($validated['action'] === 'deactivate') {
                $report->link->update(['is_active' => false]);
            }
            $report->markReviewed(auth()->id(),
                $validated['action'] === 'dismiss' ? 'dismissed' : 'actioned',
                $validated['notes']);
        }

        return redirect()->back()->with('success', count($reports) . ' reports processed.');
    }
}
