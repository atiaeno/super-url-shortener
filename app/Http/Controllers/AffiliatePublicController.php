<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\AffiliateTier;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AffiliatePublicController extends Controller
{
    public function index()
    {
        $affiliateEnabled = (Setting::where('key', 'features_affiliate')->value('value') ?? 'true') === 'true';

        if (!$affiliateEnabled) {
            return redirect()->route('welcome');
        }

        return Inertia::render('Affiliate/Index', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'tiers' => AffiliateTier::active()->with('countryRates')->orderBy('visit_threshold')->get(),
        ]);
    }
}
