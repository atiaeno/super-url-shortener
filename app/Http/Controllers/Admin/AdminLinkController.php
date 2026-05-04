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
     * Show the form for editing a link (admin version).
     */
    public function edit(Link $link): Response
    {
        $otherLinks = Link::where('user_id', $link->user_id)
            ->where('id', '!=', $link->id)
            ->latest()
            ->limit(10)
            ->get(['id', 'short_code', 'destination_url', 'is_active', 'clicks_count', 'created_at']);

        return Inertia::render('Admin/Links/Edit', [
            'link' => $link,
            'user' => $link->user->only(['id', 'name', 'email']),
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
            ->route('admin.users.show', $link->user_id)
            ->with('success', 'Link updated successfully.');
    }
}
