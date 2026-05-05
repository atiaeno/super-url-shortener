<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Services\ShortCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class LinkController extends ApiController
{
    public function __construct(
        private ShortCodeService $shortCodeService
    ) {}

    /**
     * List all links for the authenticated user.
     * GET /api/v1/links
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = min($request->query('per_page', 20), 100);

        $links = Link::forUser(auth()->id())
            ->withCount('clicks')
            ->latest()
            ->paginate($perPage);

        $transformed = $links->map(fn(Link $link) => $this->transformLink($link));

        return $this->paginated(new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed,
            $links->total(),
            $links->perPage(),
            $links->currentPage(),
            ['path' => $request->url()]
        ));
    }

    /**
     * Create a new short link.
     * POST /api/v1/links
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => ['required', 'url', 'max:2048'],
            'alias' => ['nullable', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:links,short_code', 'unique:links,custom_alias'],
            'campaign_tag' => ['nullable', 'string', 'max:100'],
            'visibility' => ['nullable', 'in:public,private'],
            'password' => ['required_if:visibility,private', 'nullable', 'string', 'min:6', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'og_title' => ['nullable', 'string', 'max:200'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();
        $shortCode = $validated['alias'] ?? $this->shortCodeService->generate();

        $link = Link::create([
            'user_id' => auth()->id(),
            'short_code' => $shortCode,
            'destination_url' => $validated['url'],
            'custom_alias' => $validated['alias'] ?? null,
            'campaign_tag' => $validated['campaign_tag'] ?? null,
            'visibility' => $validated['visibility'] ?? 'public',
            'password' => $validated['password'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'og_title' => $validated['og_title'] ?? null,
            'og_description' => $validated['og_description'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ]);

        // Cache the redirect
        Cache::put("redirect:{$link->short_code}", $link->destination_url, now()->addHours(24));

        return $this->success(
            $this->transformLink($link, true),
            'Link created successfully',
            201
        );
    }

    /**
     * Get a single link.
     * GET /api/v1/links/{id}
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $link = Link::forUser(auth()->id())
            ->withCount('clicks')
            ->where(function ($q) use ($id) {
                $q
                    ->where('id', $id)
                    ->orWhere('short_code', $id);
            })
            ->first();

        if (!$link) {
            return $this->error('Link not found', 404);
        }

        return $this->success($this->transformLink($link, true));
    }

    /**
     * Update a link.
     * PATCH /api/v1/links/{id}
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $link = Link::forUser(auth()->id())
            ->where(function ($q) use ($id) {
                $q
                    ->where('id', $id)
                    ->orWhere('short_code', $id);
            })
            ->first();

        if (!$link) {
            return $this->error('Link not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'url' => ['required', 'url', 'max:2048'],
            'alias' => ['nullable', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:links,short_code,' . $link->id, 'unique:links,custom_alias,' . $link->id],
            'campaign_tag' => ['nullable', 'string', 'max:100'],
            'visibility' => ['nullable', 'in:public,private'],
            'password' => ['required_if:visibility,private', 'nullable', 'string', 'min:6', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'og_title' => ['nullable', 'string', 'max:200'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        // Handle alias change
        if (isset($validated['alias']) && $validated['alias'] !== ($link->custom_alias ?? $link->short_code)) {
            // Clear old cache
            Cache::forget("redirect:{$link->short_code}");

            $link->short_code = $validated['alias'];
            $link->custom_alias = $validated['alias'];
        }

        $link->destination_url = $validated['url'];
        $link->campaign_tag = $validated['campaign_tag'] ?? $link->campaign_tag;
        $link->visibility = $validated['visibility'] ?? $link->visibility;
        if (isset($validated['password'])) {
            $link->password = $validated['password'];
        }
        $link->expires_at = $validated['expires_at'] ?? $link->expires_at;
        $link->og_title = $validated['og_title'] ?? $link->og_title;
        $link->og_description = $validated['og_description'] ?? $link->og_description;
        $link->og_image = $validated['og_image'] ?? $link->og_image;
        $link->save();

        // Update cache
        Cache::put("redirect:{$link->short_code}", $link->destination_url, now()->addHours(24));

        return $this->success(
            $this->transformLink($link, true),
            'Link updated successfully'
        );
    }

    /**
     * Delete a link.
     * DELETE /api/v1/links/{id}
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $link = Link::forUser(auth()->id())
            ->where(function ($q) use ($id) {
                $q
                    ->where('id', $id)
                    ->orWhere('short_code', $id);
            })
            ->first();

        if (!$link) {
            return $this->error('Link not found', 404);
        }

        // Clear cache
        Cache::forget("redirect:{$link->short_code}");

        $link->delete();

        return $this->success([
            'deleted_at' => now()->toIso8601String(),
        ], 'Link deleted successfully');
    }

    /**
     * Transform link model to API response format.
     */
    private function transformLink(Link $link, bool $includeQr = false): array
    {
        $data = [
            'short_code' => $link->short_code,
            'short_url' => $link->short_url,
            'original_url' => $link->destination_url,
            'alias' => $link->custom_alias,
            'campaign_tag' => $link->campaign_tag,
            'is_active' => $link->is_active ?? true,
            'visibility' => $link->visibility,
            'expires_at' => $link->expires_at?->toIso8601String(),
            'og_title' => $link->og_title,
            'og_description' => $link->og_description,
            'og_image' => $link->og_image,
            'clicks' => $link->clicks_count ?? 0,
            'created_at' => $link->created_at?->toIso8601String(),
            'updated_at' => $link->updated_at?->toIso8601String(),
        ];

        if ($includeQr) {
            $data['qr_code'] = config('app.url') . '/qr/' . $link->short_code . '/svg';
        }

        return $data;
    }
}
