<?php

// © Atia Hegazy — atiaeno.com

namespace App\Services;

use App\Models\IndexerQueue;
use App\Models\IndexerSetting;
use App\Models\Link;
use Illuminate\Support\Facades\Log;

class IndexerService
{
    private GoogleIndexerService $googleIndexer;
    private IndexNowService $indexNow;
    private XmlPingService $xmlPing;
    private IndexerSetting $settings;

    public function __construct()
    {
        $this->settings = IndexerSetting::getSettings();
        $this->googleIndexer = new GoogleIndexerService();
        $this->indexNow = new IndexNowService();
        $this->xmlPing = new XmlPingService();
    }

    public function run(): array
    {
        $results = [
            'google' => ['processed' => 0, 'success' => 0, 'failed' => 0],
            'indexnow' => ['processed' => 0, 'success' => 0, 'failed' => 0],
            'xml_ping' => ['processed' => 0, 'success' => 0, 'failed' => 0],
        ];

        if (!$this->settings->enabled) {
            Log::info('Indexer: Disabled, skipping');
            return $results;
        }

        // Process Google Indexer queue
        if ($this->googleIndexer->isEnabled()) {
            $results['google'] = $this->processQueue('google');
        }

        // Process IndexNow
        if ($this->indexNow->isEnabled()) {
            $results['indexnow'] = $this->processQueue('indexnow');
        }

        // XML Ping - run once per execution
        if ($this->xmlPing->isEnabled()) {
            $this->runXmlPing();
        }

        return $results;
    }

    private function processQueue(string $type): array
    {
        $results = ['processed' => 0, 'success' => 0, 'failed' => 0];
        $batchSize = $this->settings->links_per_batch;

        $queueItems = IndexerQueue::where('status', 'pending')
            ->where('type', $type)
            ->limit($batchSize)
            ->get();

        foreach ($queueItems as $item) {
            $item->markAsProcessing();

            $link = $item->link;
            if (!$link || !$this->isPublicLink($link)) {
                $item->markAsFailed('Link not found or not public');
                $results['failed']++;
                continue;
            }

            $url = $this->getPublicUrl($link);
            $success = false;

            switch ($type) {
                case 'google':
                    $success = $this->googleIndexer->submitUrl($url, $link->id);
                    break;
                case 'indexnow':
                    $success = $this->indexNow->submitUrl($url);
                    break;
            }

            if ($success) {
                $item->markAsCompleted();
                $results['success']++;
            } else {
                $item->markAsFailed('Submission failed');
                $results['failed']++;
            }

            $results['processed']++;
        }

        // Retry failed items
        $this->retryFailedItems($type);

        return $results;
    }

    private function retryFailedItems(string $type): void
    {
        $failedItems = IndexerQueue::where('status', 'failed')
            ->where('type', $type)
            ->whereRaw('attempts < max_attempts')
            ->limit($this->settings->links_per_batch)
            ->get();

        foreach ($failedItems as $item) {
            $item->update(['status' => 'pending']);
        }
    }

    private function runXmlPing(): void
    {
        $this->xmlPing->pingSitemap();
    }

    public function addToQueue(Link $link, string $type = 'google'): void
    {
        if (!$this->isPublicLink($link)) {
            return;
        }

        // Check if already in queue
        $exists = IndexerQueue::where('link_id', $link->id)
            ->where('type', $type)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($exists) {
            return;
        }

        IndexerQueue::create([
            'link_id' => $link->id,
            'type' => $type,
            'status' => 'pending',
        ]);
    }

    public function addToAllQueues(Link $link): void
    {
        if ($this->googleIndexer->isEnabled()) {
            $this->addToQueue($link, 'google');
        }

        if ($this->indexNow->isEnabled()) {
            $this->addToQueue($link, 'indexnow');
        }
    }

    public function indexNow(int $linkId): bool
    {
        $link = Link::find($linkId);
        if (!$link || !$this->isPublicLink($link)) {
            return false;
        }

        $url = $this->getPublicUrl($link);

        return $this->googleIndexer->submitUrl($url, $link->id);
    }

    private function isPublicLink(Link $link): bool
    {
        return !$link->is_private && !$link->password;
    }

    private function getPublicUrl(Link $link): string
    {
        return config('app.url') . '/' . $link->short_code;
    }

    public function getQueueStats(): array
    {
        return [
            'pending' => IndexerQueue::where('status', 'pending')->count(),
            'processing' => IndexerQueue::where('status', 'processing')->count(),
            'completed' => IndexerQueue::where('status', 'completed')->count(),
            'failed' => IndexerQueue::where('status', 'failed')->count(),
        ];
    }
}
