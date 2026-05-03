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
