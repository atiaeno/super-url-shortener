<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Jobs\FetchOgTagsJob;
use App\Models\Ad;
use App\Models\Link;
use App\Services\ShortCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class LinkController extends Controller
{
    public function __construct(
        private ShortCodeService $shortCodeService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $links = Link::forUser(Auth::id())
            ->withCount('clicks')
            ->latest()
            ->paginate(20);

        return Inertia::render('Links/Index', [
            'links' => $links,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Links/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_url' => ['required', 'url', 'max:2048'],
            'custom_alias' => ['nullable', 'string', 'min:4', 'max:20', 'alpha_dash', 'unique:links'],
            'campaign_tag' => ['nullable', 'string', 'max:100'],
            'visibility' => ['required', 'in:public,private'],
            'password' => ['required_if:visibility,private', 'nullable', 'string', 'min:6', 'max:255'],
        ]);

        $shortCode = $validated['custom_alias'] ?? $this->shortCodeService->generate();

        $link = Link::create([
            'user_id' => Auth::id(),
            'short_code' => $shortCode,
            'destination_url' => $validated['destination_url'],
            'custom_alias' => $validated['custom_alias'] ?? null,
            'campaign_tag' => $validated['campaign_tag'] ?? null,
            'visibility' => $validated['visibility'] ?? 'public',
            'password' => $validated['password'] ?? null,
        ]);

        // Cache the redirect
        Cache::put("redirect:{$link->short_code}", $link->destination_url, now()->addHours(24));

        // Story 1.7: Fetch OG tags asynchronously
        FetchOgTagsJob::dispatch($link->id)->onQueue('default');

        return redirect()
            ->route('links.index')
            ->with('success', 'Link created successfully.');
    }

    /**
     * Display the specified resource with time-period filtered analytics.
     * Stories 3.9b (time filter), 3.10 (country flags), 3.11 (device/referrer)
     */
    public function show(Link $link, Request $request): Response
    {
        $this->authorize('view', $link);

        $period = $request->query('period', 'all');

        $clicksQuery = $link->clicks();

        $clicksQuery = match ($period) {
            'today' => $clicksQuery->whereDate('created_at', today()),
            'week' => $clicksQuery->where('created_at', '>=', now()->subDays(7)),
            'month' => $clicksQuery->where('created_at', '>=', now()->subDays(30)),
            default => $clicksQuery,
        };

        // Clone the base filtered query for each aggregate
        $base = clone $clicksQuery;

        $analytics = [
            'total_clicks' => (clone $base)->count(),
            'period' => $period,
            'clicks_by_device' => (clone $base)
                ->selectRaw('device_type, COUNT(*) as total')
                ->groupBy('device_type')
                ->orderByDesc('total')
                ->get(),
            'clicks_by_country' => (clone $base)
                ->selectRaw('country_code, COUNT(*) as total')
                ->groupBy('country_code')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn($row) => array_merge($row->toArray(), [
                    'flag' => $this->countryFlag($row->country_code),
                ])),
            'clicks_by_browser' => (clone $base)
                ->selectRaw('browser, COUNT(*) as total')
                ->groupBy('browser')
                ->orderByDesc('total')
                ->limit(6)
                ->get(),
            'clicks_by_referrer' => (clone $base)
                ->selectRaw('referrer_domain, COUNT(*) as total')
                ->groupBy('referrer_domain')
                ->orderByDesc('total')
                ->limit(6)
                ->get(),
            'clicks_over_time' => (clone $base)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return Inertia::render('Links/Show', [
            'link' => array_merge($link->toArray(), [
                'short_url' => $link->short_url,
            ]),
            'analytics' => $analytics,
        ]);
    }

    /**
     * Convert ISO 3166-1 alpha-2 country code to flag emoji.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Link $link): Response
    {
        $this->authorize('update', $link);

        return Inertia::render('Links/Edit', [
            'link' => $link,
            'ads' => Ad::active()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Link $link)
    {
        $this->authorize('update', $link);

        $validated = $request->validate([
            'destination_url' => ['required', 'url', 'max:2048'],
            'campaign_tag' => ['nullable', 'string', 'max:100'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'ad_override' => ['required', 'in:inherit,disable,force'],
            'ad_id' => ['nullable', 'exists:ads,id'],
            'visibility' => ['required', 'in:public,private'],
            'password' => ['required_if:visibility,private', 'nullable', 'string', 'min:6', 'max:255'],
        ]);

        // Clear ad_id if not using force override
        if ($validated['ad_override'] !== 'force') {
            $validated['ad_id'] = null;
        }

        $link->update($validated);

        // Update cache
        Cache::put("redirect:{$link->short_code}", $link->destination_url, now()->addHours(24));

        return redirect()
            ->route('links.index')
            ->with('success', 'Link updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Link $link)
    {
        $this->authorize('delete', $link);

        // Clear cache
        Cache::forget("redirect:{$link->short_code}");

        $link->delete();

        return redirect()
            ->route('links.index')
            ->with('success', 'Link deleted successfully.');
    }

    /**
     * Toggle link active status.
     */
    public function toggle(Link $link)
    {
        $this->authorize('update', $link);

        $link->update(['is_active' => !$link->is_active]);

        // Clear cache if deactivating
        if (!$link->is_active) {
            Cache::forget("redirect:{$link->short_code}");
        } else {
            Cache::put("redirect:{$link->short_code}", $link->destination_url, now()->addHours(24));
        }

        $status = $link->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Link {$status} successfully.");
    }
}
