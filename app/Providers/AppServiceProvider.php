<?php
// © Atia Hegazy — atiaeno.com

namespace App\Providers;

use App\Models\Setting;
use App\Observers\SettingObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * Check if settings table exists (for tests/migrations)
     */
    private function settingsTableExists(): bool
    {
        // During testing, table might not exist yet (migrations not run)
        if (app()->environment('testing')) {
            try {
                DB::select('SELECT 1 FROM settings LIMIT 1');
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        try {
            return Schema::hasTable('settings');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Setting observer
        Setting::observe(SettingObserver::class);

     
        Vite::prefetch(concurrency: 3);

        // Share feature settings globally
        Inertia::share('features', function () {
            if (!$this->settingsTableExists()) {
                return ['affiliate' => true, 'ads' => true, 'gdpr' => true];
            }

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
