<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class AdminLinkController extends Controller
{
    /**
     * Display a listing of all links.
     */
    public function index(): Response
    {
        $links = Link::with(['user' => function ($query) {
            $query->select('id', 'name', 'email');
        }])
            ->withCount('clicks')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Link::count(),
            'active' => Link::where('is_active', true)->count(),
            'inactive' => Link::where('is_active', false)->count(),
            'totalClicks' => Link::sum('clicks_count'),
        ];

        return Inertia::render('Admin/Links/Index', [
            'links' => $links,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing a link (admin version).
     */
    public function edit(Link $link): Response
    {
        $otherLinks = $link->user_id
            ? Link::where('user_id', $link->user_id)
                ->where('id', '!=', $link->id)
                ->latest()
                ->limit(10)
                ->get(['id', 'short_code', 'destination_url', 'is_active', 'clicks_count', 'created_at'])
            : collect();

        $user = $link->user
            ? $link->user->only(['id', 'name', 'email'])
            : ['id' => null, 'name' => 'Guest', 'email' => '-'];

        return Inertia::render('Admin/Links/Edit', [
            'link' => $link,
            'user' => $user,
            'ads' => Ad::active()->get(),
            'otherLinks' => $otherLinks,
        ]);
    }

    /**
     * Update the specified link (admin version).
     */
    public function update(Request $request, Link $link)
    {
        $validated = $request->validate([
            'destination_url' => ['required', 'url', 'max:2048'],
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

        if ($link->user_id) {
            return redirect()
                ->route('admin.users.show', $link->user_id)
                ->with('success', 'Link updated successfully.');
        }

        return redirect()
            ->route('admin.links.index')
            ->with('success', 'Link updated successfully.');
    }
}
