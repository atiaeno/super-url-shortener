<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdController extends Controller
{
    public function index(): Response
    {
        $ads = Ad::all();
        return Inertia::render('Admin/Ads/Index', [
            'ads' => $ads,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'format' => 'required|in:banner,interstitial',
            'content' => 'nullable|string',
            'target_url' => 'nullable|url',
            'target_countries' => 'nullable|array',
            'target_countries.*' => 'string|size:2',
            'countdown_seconds' => 'required_if:format,interstitial|integer|min:1|max:60',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('ads', 'public');
        }

        Ad::create($validated);

        return redirect()->back()->with('success', 'Ad created successfully.');
    }

    public function update(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'target_url' => 'nullable|url',
            'target_countries' => 'nullable|array',
            'target_countries.*' => 'string|size:2',
            'countdown_seconds' => 'integer|min:1|max:60',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('ads', 'public');
        }

        $ad->update($validated);

        return redirect()->back()->with('success', 'Ad updated successfully.');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();

        return redirect()->back()->with('success', 'Ad deleted successfully.');
    }
}
