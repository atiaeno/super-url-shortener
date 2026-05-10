<?php

// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndexerLog;
use App\Models\IndexerQueue;
use App\Models\IndexerSetting;
use App\Models\Link;
use App\Services\IndexerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexerController extends Controller
{
    public function index(Request $request)
    {
        $settings = IndexerSetting::getSettings();
        $stats = (new IndexerService())->getQueueStats();

        $page = $request->input('page', 1);
        $perPage = 20;

        $logs = IndexerLog::with('link')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return Inertia::render('Admin/Settings/Indexer', [
            'settings' => $settings,
            'stats' => $stats,
            'recentLogs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'links_per_batch' => 'integer|min:1|max:100',
            'interval_minutes' => 'integer|min:1|max:1440',
            'google_service_account_json' => 'nullable|string',
            'google_daily_limit' => 'integer|min:1|max:200',
            'indexnow_enabled' => 'boolean',
            'indexnow_key' => 'nullable|string|min:8|max:128',
            'xml_ping_enabled' => 'boolean',
            'ping_services' => 'array',
        ]);

        $settings = IndexerSetting::getSettings();
        $settings->update($validated);
        $settings->save();

        // Create IndexNow key verification file in public/
        if (!empty($validated['indexnow_key'])) {
            $key = $validated['indexnow_key'];
            file_put_contents(public_path($key . '.txt'), $key);
        }

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

    public function pingSitemap()
    {
        $xmlPing = new \App\Services\XmlPingService();
        $results = $xmlPing->pingSitemap();

        $successCount = count(array_filter($results, fn($r) => $r === true));
        $totalCount = count($results);

        return back()->with('success', "Sitemap ping completed: {$successCount}/{$totalCount} services");
    }

    public function clearQueue()
    {
        $count = IndexerLog::count();
        IndexerLog::truncate();

        return back()->with('success', "{$count} log entries cleared");
    }

    public function queueAll()
    {
        $indexer = new IndexerService();

        $publicLinks = Link::where('is_private', false)
            ->whereNull('password')
            ->get();

        $count = 0;
        foreach ($publicLinks as $link) {
            $indexer->addToAllQueues($link);
            $count++;
        }

        return back()->with('success', "{$count} links added to queue");
    }
}
