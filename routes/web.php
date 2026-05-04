<?php
// © Atia Hegazy — atiaeno.com

use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\AdminLinkController;
use App\Http\Controllers\Admin\AffiliateTierController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\BulkLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestLinkController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


// ── Homepage ─────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'donationEnabled' => (bool) config('app.donation_enabled', false),
        'donationButtonId' => config('app.donation_button_id', ''),
    ]);
})->name('welcome');

// ── Story 1.4: Guest link shortening ─────────────────────────────────────────
Route::post('/guest/shorten', [GuestLinkController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('guest.shorten');

// ── Public QR code for guest links ─────────────────────────────────────────
Route::get('/guest/qr/{shortCode}', [QrCodeController::class, 'guestQr'])
    ->name('guest.qr');

// ── Legal Pages ───────────────────────────────────────────────────────────────
Route::get('/privacy', fn() => Inertia::render('Legal/Privacy'))->name('legal.privacy');
Route::get('/terms', fn() => Inertia::render('Legal/Terms'))->name('legal.terms');
Route::get('/cookies', fn() => Inertia::render('Legal/Cookies'))->name('legal.cookies');
Route::get('/gdpr', fn() => Inertia::render('Legal/Gdpr'))->name('legal.gdpr');

// ── Help & Documentation ───────────────────────────────────────────────────────
Route::get('/help', fn() => Inertia::render('HelpCenter'))->name('help.center');
Route::get('/api-docs', fn() => Inertia::render('ApiDocs'))->name('api.docs');

// ── Story 1.8: Sitemap & robots ───────────────────────────────────────────────
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// ── Story 2.3: OAuth ──────────────────────────────────────────────────────────
Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// ── Auth Routes (login, register, etc) ───────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Dashboard (must be before catch-all) ──────────────────────────────────────
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');

    // Story 2.8: OAuth disconnect
    Route::delete('/auth/{provider}/disconnect', [SocialAuthController::class, 'disconnect'])->name('social.disconnect');

    // Stories 3.7 & 3.8: Bulk shortening — MUST be before resource route
    Route::get('/links/bulk', [BulkLinkController::class, 'index'])->name('links.bulk');
    Route::post('/links/bulk', [BulkLinkController::class, 'store'])->name('links.bulk.store');
    Route::post('/links/bulk/export', [BulkLinkController::class, 'export'])->name('links.bulk.export');

    // Links management
    Route::resource('links', LinkController::class);
    Route::patch('links/{link}/toggle', [LinkController::class, 'toggle'])->name('links.toggle');

    // Story 3.4: QR code download
    Route::get('/links/{link}/qr/{format}', [QrCodeController::class, 'generate'])
        ->where('format', 'svg|png')
        ->name('links.qr');

    // Affiliate program (user-facing)
    Route::prefix('affiliate')->name('affiliate.')->group(function () {
        Route::get('/', [AffiliateController::class, 'index'])->name('index');
        Route::post('/enroll', [AffiliateController::class, 'enroll'])->name('enroll');
        Route::post('/payout', [AffiliateController::class, 'requestPayout'])->name('payout.request');
        Route::get('/payouts', [AffiliateController::class, 'payouts'])->name('payouts');
    });

    // Link reporting (Story 6.1 - public endpoint)
    Route::post('links/{link}/report', [ReportController::class, 'store'])->name('links.report');
});

// ── Admin Panel ─────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Link Management (admin edit)
    Route::get('links/{link}/edit', [AdminLinkController::class, 'edit'])->name('links.edit');
    Route::put('links/{link}', [AdminLinkController::class, 'update'])->name('links.update');

    // User Management (Stories 8.1 - 8.3)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::post('{user}/role', [UserController::class, 'updateRole'])->name('role');
        Route::post('{user}/ban', [UserController::class, 'ban'])->name('ban');
        Route::post('{user}/unban', [UserController::class, 'unban'])->name('unban');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Affiliate Tiers Management (Stories 4.1 & 4.2)
    Route::prefix('affiliate-tiers')->name('affiliate-tiers.')->group(function () {
        Route::get('/', [AffiliateTierController::class, 'index'])->name('index');
        Route::post('/', [AffiliateTierController::class, 'store'])->name('store');
        Route::patch('{affiliateTier}', [AffiliateTierController::class, 'update'])->name('update');
        Route::post('{affiliateTier}/country-rates', [AffiliateTierController::class, 'syncCountryRates'])->name('country-rates');
    });

    // Payouts Management (Stories 4.7 & 4.8)
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [PayoutController::class, 'index'])->name('index');
        Route::post('{payout}/approve', [PayoutController::class, 'approve'])->name('approve');
        Route::post('{payout}/reject', [PayoutController::class, 'reject'])->name('reject');
        Route::post('{payout}/mark-paid', [PayoutController::class, 'markPaid'])->name('mark-paid');
        Route::get('{payout}/audit-log', [PayoutController::class, 'auditLog'])->name('audit-log');
    });

    // Advertising Management (Stories 5.1 - 5.4)
    Route::prefix('ads')->name('ads.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\AdController::class, 'store'])->name('store');
        Route::patch('{ad}', [\App\Http\Controllers\Admin\AdController::class, 'update'])->name('update');
        Route::delete('{ad}', [\App\Http\Controllers\Admin\AdController::class, 'destroy'])->name('destroy');
    });

    // Content Moderation (Stories 6.1 - 6.4)
    Route::prefix('moderation')->name('moderation.')->group(function () {
        Route::get('/', [ModerationController::class, 'index'])->name('index');
        Route::post('reports/{report}/review', [ModerationController::class, 'review'])->name('review');
        Route::post('batch', [ModerationController::class, 'batchReview'])->name('batch');
        Route::get('activity-log', [ModerationController::class, 'activityLog'])->name('activity-log');
    });

    // System Settings (Stories 7.1 - 7.10)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::post('purge-cache', [SettingsController::class, 'purgeCache'])->name('purge-cache');
        Route::get('export', [SettingsController::class, 'export'])->name('export');
        Route::post('import', [SettingsController::class, 'import'])->name('import');
        Route::get('backup', [SettingsController::class, 'backup'])->name('backup');
    });
});

// Public redirect endpoint (short URLs) — MUST BE LAST
Route::get('/{shortCode}', RedirectController::class)
    ->where('shortCode', '[a-zA-Z0-9]+')
    ->middleware(\App\Http\Middleware\RateLimitRedirects::class)
    ->name('redirect');
