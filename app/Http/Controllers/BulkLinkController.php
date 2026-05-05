<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BulkLinkController extends Controller
{
    private const MAX_URLS = 500;

    /**
     * Story 3.7: Show bulk shortening page.
     */
    public function index(): Response
    {
        return Inertia::render('Links/Bulk');
    }

    /**
     * Story 3.7: Process up to 500 URLs and return results.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'urls' => ['required', 'array', 'min:1', 'max:' . self::MAX_URLS],
            'urls.*' => ['required', 'string', 'max:2048'],
        ]);

        $userId = $request->user()->id;
        $results = [];

        // Deduplicate URLs first
        $uniqueUrls = array_unique(array_map('trim', $request->urls));
        $uniqueUrls = array_filter($uniqueUrls, fn($url) => !empty($url));

        // Check for existing links to avoid duplicates
        $existingLinks = Link::where('user_id', $userId)
            ->whereIn('destination_url', $uniqueUrls)
            ->pluck('destination_url', 'short_code')
            ->toArray();

        $createdLinks = [];

        foreach ($uniqueUrls as $url) {
            // Basic URL validation
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $results[] = [
                    'original_url' => $url,
                    'short_url' => null,
                    'short_code' => null,
                    'status' => 'error',
                    'error' => 'Invalid URL format.',
                ];
                continue;
            }

            // Check if link already exists
            $existingCode = array_search($url, $existingLinks);
            if ($existingCode !== false) {
                $link = Link::where('short_code', $existingCode)->first();
                $results[] = [
                    'original_url' => $url,
                    'short_url' => $link?->short_url,
                    'short_code' => $existingCode,
                    'status' => 'success',
                    'error' => null,
                    'created_at' => $link?->created_at?->toISOString(),
                    'duplicate' => true,
                ];
                continue;
            }

            try {
                $code = $this->generateCode();

                $link = Link::create([
                    'user_id' => $userId,
                    'short_code' => $code,
                    'destination_url' => $url,
                    'is_active' => true,
                ]);

                $createdLinks[$url] = $code;

                $results[] = [
                    'original_url' => $url,
                    'short_url' => $link->short_url,
                    'short_code' => $code,
                    'status' => 'success',
                    'error' => null,
                    'created_at' => $link->created_at->toISOString(),
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'original_url' => $url,
                    'short_url' => null,
                    'short_code' => null,
                    'status' => 'error',
                    'error' => 'Failed to create link.',
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Story 3.8: Export bulk results as CSV download.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $request->validate([
            'results' => ['required', 'array'],
        ]);

        $results = $request->results;

        return response()->streamDownload(function () use ($results) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['original_url', 'short_url', 'short_code', 'status', 'error', 'created_at']);

            foreach ($results as $row) {
                fputcsv($handle, [
                    $row['original_url'] ?? '',
                    $row['short_url'] ?? '',
                    $row['short_code'] ?? '',
                    $row['status'] ?? '',
                    $row['error'] ?? '',
                    $row['created_at'] ?? '',
                ]);
            }

            fclose($handle);
        }, 'bulk-links-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function generateCode(): string
    {
        do {
            $code = Str::random(7);
        } while (Link::where('short_code', $code)->exists());

        return $code;
    }
}
