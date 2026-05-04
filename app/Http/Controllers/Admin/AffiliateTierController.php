<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCountryRate;
use App\Models\AffiliateTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateTierController extends Controller
{
    /**
     * Story 4.1: List all tiers.
     */
    public function index(): Response
    {
        $tiers = AffiliateTier::withCount('affiliates')
            ->with('countryRates')
            ->orderBy('visit_threshold')
            ->get();

        return Inertia::render('Admin/AffiliateTiers/Index', [
            'tiers' => $tiers,
        ]);
    }

    /**
     * Story 4.1: Create tier.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'visit_threshold' => ['required', 'integer', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'view_rate' => ['required', 'numeric', 'min:0'],
            'view_multiplier' => ['required', 'integer', 'min:1'],
        ]);

        AffiliateTier::create($validated);

        return redirect()
            ->route('admin.affiliate-tiers.index')
            ->with('success', 'Tier created.');
    }

    /**
     * Story 4.1: Update tier.
     */
    public function update(Request $request, AffiliateTier $affiliateTier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'visit_threshold' => ['required', 'integer', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'view_rate' => ['required', 'numeric', 'min:0'],
            'view_multiplier' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $affiliateTier->update($validated);

        return back()->with('success', 'Tier updated.');
    }

    /**
     * Story 4.2: Sync country rate overrides for a tier.
     */
    public function syncCountryRates(Request $request, AffiliateTier $affiliateTier): RedirectResponse
    {
        $validated = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*.country_code' => ['required', 'string', 'size:2'],
            'rates.*.commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $affiliateTier->countryRates()->delete();

        foreach ($validated['rates'] as $rate) {
            AffiliateCountryRate::create([
                'affiliate_tier_id' => $affiliateTier->id,
                'country_code' => strtoupper($rate['country_code']),
                'commission_rate' => $rate['commission_rate'],
            ]);
        }

        return back()->with('success', 'Country rates updated.');
    }
}
