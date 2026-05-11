<?php
// © Atia Hegazy — atiaeno.com

use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\AdminLinkController;
use App\Http\Controllers\Admin\AffiliateTierController;
use App\Http\Controllers\Admin\AliasDomainController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AffiliatePublicController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApiTokenController;
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
use App\Http\Controllers\TrackController;
use App\Models\AffiliateTier;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Homepage ─────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'donationEnabled' => (bool) config('app.donation_enabled', false),
        'donationButtonId' => config('app.donation_button_id', ''),
        'featuresAffiliate' => (bool) config('app.features_affiliate', true),
        'appName' => config('app.name', 'ShortLink'),
    ]);
})->name('welcome');

// ── Guest link shortening ───────────────────────────────────────────────────
Route::post('/guest/shorten', [GuestLinkController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('guest.shorten');

// ── Public QR code for guest links ─────────────────────────────────────────
Route::get('/guest/qr/{shortCode}', [QrCodeController::class, 'guestQr'])
    ->name('guest.qr');

// ── Public QR code for any link (API & web) ─────────────────────────────────
Route::get('/qr/{shortCode}/{format?}', [QrCodeController::class, 'generate'])
    ->where('format', 'svg|png')
    ->name('qr.generate');

// ── Legal Pages ───────────────────────────────────────────────────────────────
Route::get('/privacy', fn() => Inertia::render('Legal/Privacy'))->name('legal.privacy');
Route::get('/terms', fn() => Inertia::render('Legal/Terms'))->name('legal.terms');
Route::get('/cookies', fn() => Inertia::render('Legal/Cookies'))->name('legal.cookies');
Route::get('/gdpr', fn() => Inertia::render('Legal/Gdpr'))->name('legal.gdpr');

// ── Public Affiliate Page ─────────────────────────────────────────────────────
Route::get('/partners', [AffiliatePublicController::class, 'index'])->name('affiliate.public');

// ── Help & Documentation ───────────────────────────────────────────────────────
Route::get('/help', fn() => Inertia::render('HelpCenter'))->name('help.center');
Route::get('/api-docs', fn() => Inertia::render('ApiDocs'))->name('api-docs');

// ── Sitemap & robots ──────────────────────────────────────────────────────────
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// ── Public Settings API ───────────────────────────────────────────────────────
Route::get('/api/settings', [SettingsController::class, 'publicSettings'])->name('settings.public');

// ── OAuth ───────────────────────────────────────────────────────────────────
Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// ── Auth Routes (login, register, etc) ───────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Dashboard (must be before catch-all) ──────────────────────────────────────
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ── Analytics ─────────────────────────────────────────────────────────────────
Route::get('/analytics', AnalyticsController::class)
    ->middleware(['auth', 'verified'])
    ->name('analytics');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');

    // API Tokens
    Route::get('/profile/api-tokens', [ApiTokenController::class, 'index'])->name('profile.api-tokens');
    Route::post('/profile/api-tokens', [ApiTokenController::class, 'store'])->name('profile.api-tokens.store');
    Route::delete('/profile/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('profile.api-tokens.destroy');

    // OAuth disconnect
    Route::delete('/auth/{provider}/disconnect', [SocialAuthController::class, 'disconnect'])->name('social.disconnect');

    // Bulk shortening — MUST be before resource route
    Route::get('/links/bulk', [BulkLinkController::class, 'index'])->name('links.bulk');
    Route::post('/links/bulk', [BulkLinkController::class, 'store'])->name('links.bulk.store');
    Route::post('/links/bulk/export', [BulkLinkController::class, 'export'])->name('links.bulk.export');

    // Links management
    Route::resource('links', LinkController::class);
    Route::patch('links/{link}/toggle', [LinkController::class, 'toggle'])->name('links.toggle');

    // QR code download
    Route::get('/links/{link}/qr/{format}', [QrCodeController::class, 'generate'])
        ->where('format', 'svg|png')
        ->name('links.qr');

    // Affiliate program (user-facing)
    Route::prefix('affiliate')->name('affiliate.')->group(function () {
        Route::get('/', [AffiliateController::class, 'index'])->name('index');
        Route::post('/enroll', [AffiliateController::class, 'enroll'])->name('enroll');
        Route::post('/payout', [AffiliateController::class, 'requestPayout'])->name('payout.request');
        Route::post('/sync', [AffiliateController::class, 'sync'])->name('sync');
        Route::get('/payouts', [AffiliateController::class, 'payouts'])->name('payouts');
    });

    // Link reporting (public endpoint)
    Route::post('links/{link}/report', [ReportController::class, 'store'])->name('links.report');
});

// ── Admin Panel ─────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Analytics
    Route::get('analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'overview'])->name('analytics');
    Route::get('links/{id}/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'linkAnalytics'])->name('links.analytics');
    Route::get('users/{id}/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'userAnalytics'])->name('users.analytics');

    // Link Management (admin)
    Route::get('links', [AdminLinkController::class, 'index'])->name('links.index');
    Route::get('links/{link}/edit', [AdminLinkController::class, 'edit'])->name('links.edit');
    Route::put('links/{link}', [AdminLinkController::class, 'update'])->name('links.update');

    // Alias Domains
    Route::prefix('alias-domains')->name('alias-domains.')->group(function () {
        Route::get('/', [AliasDomainController::class, 'index'])->name('index');
        Route::post('/', [AliasDomainController::class, 'store'])->name('store');
        Route::put('{aliasDomain}', [AliasDomainController::class, 'update'])->name('update');
        Route::delete('{aliasDomain}', [AliasDomainController::class, 'destroy'])->name('destroy');
        Route::post('{aliasDomain}/toggle', [AliasDomainController::class, 'toggleStatus'])->name('toggle');
        Route::post('{aliasDomain}/default', [AliasDomainController::class, 'setDefault'])->name('default');
    });

    // User Management
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

    // Affiliate Tiers Management
    Route::prefix('affiliate-tiers')->name('affiliate-tiers.')->group(function () {
        Route::get('/', [AffiliateTierController::class, 'index'])->name('index');
        Route::post('/', [AffiliateTierController::class, 'store'])->name('store');
        Route::patch('{affiliateTier}', [AffiliateTierController::class, 'update'])->name('update');
        Route::post('{affiliateTier}/country-rates', [AffiliateTierController::class, 'syncCountryRates'])->name('country-rates');
    });

    // Payouts Management
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [PayoutController::class, 'index'])->name('index');
        Route::post('{payout}/approve', [PayoutController::class, 'approve'])->name('approve');
        Route::post('{payout}/reject', [PayoutController::class, 'reject'])->name('reject');
        Route::post('{payout}/mark-paid', [PayoutController::class, 'markPaid'])->name('mark-paid');
        Route::get('{payout}/audit-log', [PayoutController::class, 'auditLog'])->name('audit-log');
    });

    // Advertising Management
    Route::prefix('advertising')->name('advertising.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\AdController::class, 'store'])->name('store');
        Route::delete('bulk-delete', [\App\Http\Controllers\Admin\AdController::class, 'bulkDelete'])->name('bulkDelete');
        Route::patch('{ad}', [\App\Http\Controllers\Admin\AdController::class, 'update'])
            ->name('update')
            ->where('ad', '[0-9]+');
        Route::delete('{ad}', [\App\Http\Controllers\Admin\AdController::class, 'destroy'])
            ->name('destroy')
            ->where('ad', '[0-9]+');
    });

    // Content Moderation
    Route::prefix('moderation')->name('moderation.')->group(function () {
        Route::get('/', [ModerationController::class, 'index'])->name('index');
        Route::get('reports', [ModerationController::class, 'reports'])->name('reports');
        Route::get('flagged', [ModerationController::class, 'flagged'])->name('flagged');
        Route::post('reports/{report}/review', [ModerationController::class, 'review'])->name('review');
        Route::post('batch', [ModerationController::class, 'batchReview'])->name('batch');
        Route::get('activity-log', [ModerationController::class, 'activityLog'])->name('activity-log');
    });

    // System Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::post('purge-cache', [SettingsController::class, 'purgeCache'])->name('purge-cache');
        Route::get('export', [SettingsController::class, 'export'])->name('export');
        Route::post('import', [SettingsController::class, 'import'])->name('import');
        Route::get('backup', [SettingsController::class, 'backup'])->name('backup');
    });

    // SEO Indexer Settings
    Route::prefix('settings/indexer')->name('settings.indexer.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\IndexerController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\IndexerController::class, 'update'])->name('update');
        Route::post('index-now', [\App\Http\Controllers\Admin\IndexerController::class, 'indexNow'])->name('index-now');
        Route::get('run', [\App\Http\Controllers\Admin\IndexerController::class, 'runNow'])->name('run');
        Route::get('ping-sitemap', [\App\Http\Controllers\Admin\IndexerController::class, 'pingSitemap'])->name('ping-sitemap');
        Route::get('clear', [\App\Http\Controllers\Admin\IndexerController::class, 'clearQueue'])->name('clear');
        Route::get('queue-all', [\App\Http\Controllers\Admin\IndexerController::class, 'queueAll'])->name('queue-all');
    });
});

// Tracking endpoint (client-side beacon for cached pages)
Route::post('/track/{shortCode}', [TrackController::class, 'track'])
    ->where('shortCode', '[a-zA-Z0-9]+')
    ->name('track');

// Public redirect endpoint (short URLs) — MUST BE LAST
Route::match(['get', 'post'], '/{shortCode}', RedirectController::class)
    ->where('shortCode', '[a-zA-Z0-9]+')
    ->middleware(\App\Http\Middleware\RateLimitRedirects::class)
    ->name('redirect');
