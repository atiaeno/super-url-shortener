<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Jobs\FetchOgTagsJob;
use App\Models\Ad;
use App\Models\Link;
use App\Models\LinkAnalyticsDaily;
use App\Models\Setting;
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
    public function index(Request $request): Response
    {
        $query = Link::forUser(Auth::id())->withCount('clicks')->with('domain');

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Search by short_code or destination_url
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('short_code', 'like', "%{$search}%")
                    ->orWhere('destination_url', 'like', "%{$search}%");
            });
        }

        $links = $query->latest()->paginate(20)->appends($request->query());

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
            'domain_id' => ['nullable', 'exists:alias_domains,id'],
            'custom_alias' => ['nullable', 'string', 'min:4', 'max:20', 'alpha_dash', 'unique:links'],
            'campaign_tag' => ['nullable', 'string', 'max:100'],
            'visibility' => ['required', 'in:public,private'],
            'password' => ['required_if:visibility,private', 'nullable', 'string', 'min:6', 'max:255'],
        ]);

        // Prevent shortening alias domains
        $destinationUrl = $validated['destination_url'];
        $parsedUrl = parse_url($destinationUrl);
        $destinationDomain = isset($parsedUrl['host']) ? strtolower($parsedUrl['host']) : null;

        if ($destinationDomain) {
            $aliasDomains = \App\Models\AliasDomain::where('is_active', true)->pluck('domain')->toArray();
            $aliasDomains = array_map('strtolower', $aliasDomains);

            if (in_array($destinationDomain, $aliasDomains)) {
                return back()->withErrors(['destination_url' => 'You cannot shorten alias domains.'])->withInput();
            }

            // Also check for www variant
            if (in_array('www.' . $destinationDomain, $aliasDomains)) {
                return back()->withErrors(['destination_url' => 'You cannot shorten alias domains.'])->withInput();
            }
        }

        $shortCode = $validated['custom_alias'] ?? $this->shortCodeService->generate();

        $link = Link::create([
            'user_id' => Auth::id(),
            'domain_id' => $validated['domain_id'] ?? null,
            'short_code' => $shortCode,
            'destination_url' => $validated['destination_url'],
            'custom_alias' => $validated['custom_alias'] ?? null,
            'campaign_tag' => $validated['campaign_tag'] ?? null,
            'visibility' => $validated['visibility'] ?? 'public',
            'password' => $validated['password'] ?? null,
        ]);

        // Cache the redirect
        Cache::put("redirect:{$link->short_code}", $link->destination_url, now()->addHours(24));

        // Fetch OG tags asynchronously
        FetchOgTagsJob::dispatch($link->id)->onQueue('default');

        // Load domain relationship for short_url accessor
        $link->load('domain');

        return Inertia::render('Links/Create', [
            'createdLink' => [
                'short_url' => $link->short_url,
                'short_code' => $link->short_code,
                'destination_url' => $link->destination_url,
            ],
        ]);
    }

    /**
     * Display the specified resource with time-period filtered analytics.
     */
    public function show(Link $link, Request $request): Response
    {
        $this->authorize('view', $link);
        $link->load('domain');

        $period = $request->query('period', 'all');

        // Build date range from summary table
        $dateRange = match ($period) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'week' => [now()->subDays(7)->toDateString(), now()->toDateString()],
            'month' => [now()->subDays(30)->toDateString(), now()->toDateString()],
            default => [null, null],
        };

        $summaryQuery = LinkAnalyticsDaily::where('link_id', $link->id);

        if ($dateRange[0]) {
            $summaryQuery->whereBetween('date', $dateRange);
        }

        $summaries = $summaryQuery->get();

        // Aggregate from summary table
        $totalClicks = $summaries->sum('total_clicks');
        $byCountry = $summaries->pluck('by_country')->filter()->flatMap(fn($c) => (array) $c)->groupBy(fn($v, $k) => $k)->map(fn($v) => $v->sum());
        $byDevice = $summaries->pluck('by_device')->filter()->flatMap(fn($c) => (array) $c)->groupBy(fn($v, $k) => $k)->map(fn($v) => $v->sum());
        $byBrowser = $summaries->pluck('by_browser')->filter()->flatMap(fn($c) => (array) $c)->groupBy(fn($v, $k) => $k)->map(fn($v) => $v->sum());
        $byReferrer = $summaries->pluck('by_referrer')->filter()->flatMap(fn($c) => (array) $c)->groupBy(fn($v, $k) => $k)->map(fn($v) => $v->sum());
        $clicksOverTime = $summaries->sortBy('date')->map(fn($s) => ['date' => $s->date->toDateString(), 'total' => $s->total_clicks]);

        $analytics = [
            'total_clicks' => $totalClicks,
            'period' => $period,
            'clicks_by_device' => $byDevice->map(fn($total, $name) => ['device_type' => $name, 'total' => $total])->values(),
            'clicks_by_country' => $byCountry->map(fn($total, $code) => ['country_code' => $code, 'total' => $total, 'flag' => $this->countryFlag($code)])->sortByDesc('total')->take(10)->values(),
            'clicks_by_browser' => $byBrowser->map(fn($total, $name) => ['browser' => $name, 'total' => $total])->sortByDesc('total')->take(6)->values(),
            'clicks_by_referrer' => $byReferrer->map(fn($total, $name) => ['referrer_domain' => $name, 'total' => $total])->sortByDesc('total')->take(6)->values(),
            'clicks_over_time' => $clicksOverTime->values(),
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
        $link->load('domain');

        $data = ['link' => $link];

        // Only admins can manage ad overrides
        if (Auth::user()->is_admin) {
            $data['ads'] = Ad::active()->get();
        }

        return Inertia::render('Links/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Link $link)
    {
        $this->authorize('update', $link);

        $rules = [
            'destination_url' => ['required', 'url', 'max:2048'],
            'campaign_tag' => ['nullable', 'string', 'max:100'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];

        // Admin-only fields
        if ($request->user()->is_admin) {
            $rules['ad_override'] = ['required', 'in:inherit,disable,force'];
            $rules['ad_id'] = ['nullable', 'exists:ads,id'];
        }

        $validated = $request->validate($rules);

        // Clear ad_id if not using force override
        if (isset($validated['ad_override']) && $validated['ad_override'] !== 'force') {
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
