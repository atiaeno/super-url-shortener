<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Closure;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // Clear settings cache to get latest value
        Cache::forget('settings');

        // Check if maintenance mode is enabled in settings
        $maintenanceMode = Setting::get('maintenance_mode', 'false') === 'true';

        if ($maintenanceMode) {
            // Allow access to admin routes during maintenance
            if ($request->is('admin/*') || $request->is('login') || $request->is('logout')) {
                return $next($request);
            }

            // Show maintenance page for all other routes
            $message = Setting::get('maintenance_message', 'We are performing scheduled maintenance. Please check back soon.');

            return response()
                ->view('maintenance', ['message' => $message])
                ->header('Retry-After', 3600);  // Suggest retry after 1 hour
        }

        return $next($request);
    }
}
