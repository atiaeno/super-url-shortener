<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    protected static string $cacheKey = 'app_settings';

    protected static int $cacheTTL = 43200;  // 30 days in minutes

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            Cache::forget(self::$cacheKey);
        });

        static::updated(function () {
            Cache::forget(self::$cacheKey);
        });

        static::deleted(function () {
            Cache::forget(self::$cacheKey);
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // During testing, table might not exist yet
        if (app()->environment('testing')) {
            try {
                \DB::select('SELECT 1 FROM settings LIMIT 1');
            } catch (\Exception $e) {
                return $default;
            }
        }

        $settings = Cache::remember(self::$cacheKey, self::$cacheTTL, function () {
            return self::all()->keyBy('key')->toArray();
        });

        return $settings[$key]['value'] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(self::$cacheKey);
    }

    public static function getGroup(string $group): array
    {
        return self::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function clearCache(): void
    {
        Cache::forget(self::$cacheKey);
    }
}
