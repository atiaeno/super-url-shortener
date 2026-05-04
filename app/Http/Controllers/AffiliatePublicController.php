<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\AffiliateTier;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AffiliatePublicController extends Controller
{
    public function index()
    {
        return Inertia::render('Affiliate/Index', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'tiers' => AffiliateTier::active()->orderBy('visit_threshold')->get(),
        ]);
    }
}
