<?php

// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndexerLog;
use App\Models\IndexerQueue;
use App\Models\IndexerSetting;
use App\Services\IndexerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexerController extends Controller
{
    public function index()
    {
        $settings = IndexerSetting::getSettings();
        $stats = (new IndexerService())->getQueueStats();
        $recentLogs = IndexerLog::with('link')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('Admin/Settings/Indexer', [
            'settings' => $settings,
            'stats' => $stats,
            'recentLogs' => $recentLogs,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'links_per_batch' => 'integer|min:1|max:100',
            'interval_minutes' => 'integer|min:1|max:1440',
            'google_service_account_json' => 'nullable|string',
            'indexnow_enabled' => 'boolean',
            'xml_ping_enabled' => 'boolean',
            'ping_services' => 'array',
        ]);

        $settings = IndexerSetting::getSettings();
        $settings->update($validated);
        $settings->save();

        return back()->with('success', 'Indexer settings updated');
    }

    public function indexNow(Request $request)
    {
        $request->validate([
            'link_id' => 'required|exists:links,id',
        ]);

        $indexer = new IndexerService();
        $success = $indexer->indexNow($request->link_id);

        if ($success) {
            return back()->with('success', 'Link submitted for indexing');
        }

        return back()->with('error', 'Failed to submit link for indexing');
    }

    public function runNow()
    {
        $indexer = new IndexerService();
        $results = $indexer->run();

        return back()->with('success', 'Indexer ran successfully');
    }

    public function clearQueue()
    {
        IndexerQueue::whereIn('status', ['completed', 'failed'])
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        return back()->with('success', 'Old queue entries cleared');
    }
}
