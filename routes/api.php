<?php
// © Atia Hegazy — atiaeno.com

use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | API Routes
 * |--------------------------------------------------------------------------
 * |
 * | These routes are loaded by the RouteServiceProvider within the
 * | api route group. All routes use the ApiAuth middleware for token
 * | authentication and have the /api/v1 prefix.
 * |
 */

// Health check (no auth required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health');

// Public API info (no auth required)
Route::get('/', function () {
    return response()->json([
        'name' => 'ShortLink API',
        'version' => '1.0',
        'documentation' => url('/api-docs'),
    ]);
})->name('api.info');

// Authenticated API routes with rate limiting
Route::middleware(['api.auth', 'throttle:api'])->group(function () {
    // Rate limit headers are added automatically
    Route::get('/user', function () {
        return response()->json([
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);
    })->name('api.user');

    /*
     * Links Resource
     * GET    /api/v1/links           - List all links
     * POST   /api/v1/links           - Create new link
     * GET    /api/v1/links/{id}      - Get single link
     * PATCH  /api/v1/links/{id}      - Update link
     * DELETE /api/v1/links/{id}      - Delete link
     */
    Route::apiResource('links', LinkController::class)
        ->names([
            'index' => 'api.links.index',
            'store' => 'api.links.store',
            'show' => 'api.links.show',
            'update' => 'api.links.update',
            'destroy' => 'api.links.destroy',
        ]);

    // PATCH support for partial updates (Laravel only includes PUT by default)
    Route::patch('links/{link}', [LinkController::class, 'update'])
        ->name('api.links.patch');

    /*
     * Analytics
     * GET /api/v1/links/{id}/analytics - Get link analytics
     */
    Route::get('links/{link}/analytics', [AnalyticsController::class, 'show'])
        ->name('api.links.analytics');

    /*
     * API Token Management
     * POST   /api/v1/tokens        - Create new token
     * GET    /api/v1/tokens        - List tokens
     * DELETE /api/v1/tokens/{id}   - Revoke token
     */
    Route::prefix('tokens')->name('api.tokens.')->middleware('throttle:api.tokens')->group(function () {
        Route::get('/', [TokenController::class, 'index'])->name('index');
        Route::post('/', [TokenController::class, 'store'])->name('store');
        Route::delete('{token}', [TokenController::class, 'destroy'])->name('destroy');
    });

    /*
     * Affiliate Program
     * GET    /api/v1/affiliate           - Get affiliate profile & stats
     * POST   /api/v1/affiliate/enroll    - Enroll in affiliate program
     * GET    /api/v1/affiliate/tiers     - List available tiers
     * POST   /api/v1/affiliate/payout    - Request payout
     * GET    /api/v1/affiliate/payouts   - Get payout history
     */
    Route::prefix('affiliate')->name('api.affiliate.')->group(function () {
        Route::get('/', [AffiliateController::class, 'index'])->name('index');
        Route::post('/enroll', [AffiliateController::class, 'enroll'])->name('enroll');
        Route::get('/tiers', [AffiliateController::class, 'tiers'])->name('tiers');
        Route::post('/payout', [AffiliateController::class, 'requestPayout'])->name('payout');
        Route::get('/payouts', [AffiliateController::class, 'payouts'])->name('payouts');
    });
});

// Handle 404 for API routes
Route::fallback(function () {
    return response()->json([
        'error' => 'Not Found',
        'message' => 'The requested API endpoint does not exist.',
    ], 404);
})->name('api.fallback');
