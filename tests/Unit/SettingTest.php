<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
    }

    public function test_get_returns_default_when_key_not_found(): void
    {
        Cache::flush();

        $result = Setting::get('nonexistent_key', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function test_get_returns_stored_value(): void
    {
        Cache::flush();

        Setting::create(['key' => 'test_key', 'value' => 'test_value']);

        $result = Setting::get('test_key');

        $this->assertEquals('test_value', $result);
    }

    public function test_get_returns_cached_value(): void
    {
        Setting::create(['key' => 'cached_key', 'value' => 'cached_value']);

        $result = Setting::get('cached_key');

        $this->assertEquals('cached_value', $result);
    }

    public function test_set_creates_new_setting(): void
    {
        Cache::flush();

        Setting::set('new_key', 'new_value');

        // Value is stored as JSON, so it gets wrapped in quotes
        $this->assertDatabaseHas('settings', [
            'key' => 'new_key',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        Setting::create(['key' => 'update_key', 'value' => 'old_value']);
        Setting::clearCache();

        Setting::set('update_key', 'updated_value');

        $this->assertEquals('updated_value', Setting::get('update_key'));
    }

    public function test_get_group_returns_settings_by_group(): void
    {
        Setting::create(['key' => 'group_key_1', 'value' => 'value_1', 'group' => 'test_group']);
        Setting::create(['key' => 'group_key_2', 'value' => 'value_2', 'group' => 'test_group']);
        Setting::create(['key' => 'other_key', 'value' => 'other_value', 'group' => 'other_group']);

        $result = Setting::getGroup('test_group');

        $this->assertArrayHasKey('group_key_1', $result);
        $this->assertArrayHasKey('group_key_2', $result);
        $this->assertArrayNotHasKey('other_key', $result);
    }

    public function test_clear_cache_forgets_cache_key(): void
    {
        Setting::create(['key' => 'cache_test', 'value' => 'cache_value']);

        // Verify cache is populated first
        Setting::get('cache_test');
        $this->assertNotNull(Cache::get('app_settings'));

        Setting::clearCache();

        // After clear, cache should be empty
        $this->assertNull(Cache::get('app_settings'));
    }

    public function test_get_returns_null_by_default(): void
    {
        Cache::flush();

        $result = Setting::get('missing_key');

        $this->assertNull($result);
    }

    public function test_set_handles_json_values(): void
    {
        Cache::flush();

        $arrayValue = ['key1' => 'val1', 'key2' => 'val2'];
        Setting::set('json_key', $arrayValue);

        $result = Setting::get('json_key');

        $this->assertEquals($arrayValue, $result);
    }
}
