<?php
// © Atia Hegazy — atiaeno.com

namespace App\Observers;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    public function updated(Setting $setting): void
    {
        // Clear Laravel cache when any setting is updated
        Artisan::call('optimize:clear');

        // Clear specific settings cache
        Cache::forget('settings');
    }

    public function created(Setting $setting): void
    {
        // Clear Laravel cache when new setting is created
        Artisan::call('optimize:clear');
    }

    public function deleted(Setting $setting): void
    {
        // Clear Laravel cache when setting is deleted
        Artisan::call('optimize:clear');
    }
}
