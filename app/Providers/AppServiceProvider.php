<?php
// Atia Hegazy — atiaeno.com

namespace App\Providers;

use App\Models\Setting;
use App\Observers\SettingObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Setting observer
        Setting::observe(SettingObserver::class);

        // API rate limiting: configurable from settings
        RateLimiter::for('api', function (Request $request) {
            $limit = (int) Setting::get('api_rate_limit_per_hour', 100);
            return Limit::perHour($limit)->by($request->user()?->id ?: $request->ip());
        });

        // Token generation limit: configurable from settings
        RateLimiter::for('api.tokens', function (Request $request) {
            $limit = (int) Setting::get('api_token_rate_limit_per_hour', 10);
            return Limit::perHour($limit)->by($request->user()?->id ?: $request->ip());
        });

        Vite::prefetch(concurrency: 3);

        // Share feature settings globally
        Inertia::share('features', function () {
            $settings = Setting::whereIn('key', ['features_affiliate', 'features_ads', 'features_gdpr'])
                ->pluck('value', 'key')
                ->toArray();
            return [
                'affiliate' => ($settings['features_affiliate'] ?? 'true') === 'true',
                'ads' => ($settings['features_ads'] ?? 'true') === 'true',
                'gdpr' => ($settings['features_gdpr'] ?? 'true') === 'true',
            ];
        });
    }
}
