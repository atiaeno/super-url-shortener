<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Object,
    cacheStats: Object,
});

const activeTab = ref('branding');

const form = useForm({
    app_name: props.settings.app_name,
    app_tagline: props.settings.app_tagline,
    logo_url: props.settings.logo_url,
    favicon_url: props.settings.favicon_url,
    footer_text: props.settings.footer_text,
    donation_enabled: props.settings.donation_enabled === 'true',
    donation_button_id: props.settings.donation_button_id,
    features_affiliate: props.settings.features_affiliate === 'true',
    features_ads: props.settings.features_ads === 'true',
    features_gdpr: props.settings.features_gdpr === 'true',
    cache_ttl_redirect: parseInt(props.settings.cache_ttl_redirect),
    cache_ttl_analytics: parseInt(props.settings.cache_ttl_analytics),
    maintenance_mode: props.settings.maintenance_mode === 'true',
    maintenance_message: props.settings.maintenance_message,
    captcha_enabled: props.settings.captcha_enabled === 'true',
    captcha_site_key: props.settings.captcha_site_key,
    captcha_secret_key: props.settings.captcha_secret_key,
    safe_browsing_enabled: props.settings.safe_browsing_enabled === 'true',
    safe_browsing_api_key: props.settings.safe_browsing_api_key,
    auto_suspend_threshold: parseInt(props.settings.auto_suspend_threshold),
    robots_txt: props.settings.robots_txt,
    sitemap_enabled: props.settings.sitemap_enabled === 'true',
});

const cacheForm = useForm({
    type: 'all',
});

const importForm = useForm({
    file: null,
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
};

const purgeCache = () => {
    cacheForm.post(route('admin.settings.purge-cache'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Cache purged successfully!');
        },
    });
};

const handleImport = (e) => {
    importForm.file = e.target.files[0];
    importForm.post(route('admin.settings.import'), {
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            e.target.value = '';
            alert('Settings imported successfully!');
        },
    });
};

const tabs = [
    { id: 'branding', label: 'Branding' },
    { id: 'features', label: 'Features' },
    { id: 'cache', label: 'Cache' },
    { id: 'security', label: 'Security' },
    { id: 'maintenance', label: 'Maintenance' },
    { id: 'data', label: 'Import / Export' },
];
</script>

<template>

    <Head title="Admin Settings" />
    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Admin Settings</h2>

                        <!-- Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex gap-6">
                                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                                    :class="activeTab === tab.id ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                    class="py-2 px-1 border-b-2 font-medium text-sm">
                                    {{ tab.label }}
                                </button>
                            </nav>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Branding Tab -->
                            <div v-if="activeTab === 'branding'" class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Branding</h3>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">App
                                            Name</label>
                                        <input v-model="form.app_name" type="text"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tagline</label>
                                        <input v-model="form.app_tagline" type="text"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo
                                            URL</label>
                                        <input v-model="form.logo_url" type="url"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                            placeholder="https://..." />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Favicon
                                            URL</label>
                                        <input v-model="form.favicon_url" type="url"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                            placeholder="https://..." />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Footer
                                        Text</label>
                                    <input v-model="form.footer_text" type="text"
                                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                </div>
                                <div class="flex items-center gap-4">
                                    <input v-model="form.donation_enabled" type="checkbox" id="donation"
                                        class="rounded" />
                                    <label for="donation" class="text-sm text-gray-700 dark:text-gray-300">Enable PayPal
                                        Donation</label>
                                </div>
                                <div v-if="form.donation_enabled">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">PayPal
                                        Button ID</label>
                                    <input v-model="form.donation_button_id" type="text"
                                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">robots.txt</label>
                                    <textarea v-model="form.robots_txt" rows="5"
                                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 font-mono text-sm"></textarea>
                                </div>
                                <div class="flex items-center gap-4">
                                    <input v-model="form.sitemap_enabled" type="checkbox" id="sitemap"
                                        class="rounded" />
                                    <label for="sitemap" class="text-sm text-gray-700 dark:text-gray-300">Enable Sitemap
                                        Generation</label>
                                </div>
                            </div>

                            <!-- Features Tab -->
                            <div v-if="activeTab === 'features'" class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Feature Modules</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-4 p-4 border rounded dark:border-gray-700">
                                        <input v-model="form.features_affiliate" type="checkbox" id="affiliate"
                                            class="rounded" />
                                        <div>
                                            <label for="affiliate"
                                                class="font-medium text-gray-900 dark:text-white">Affiliate
                                                Program</label>
                                            <p class="text-sm text-gray-500">Enable the affiliate program for users to
                                                earn from traffic.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 p-4 border rounded dark:border-gray-700">
                                        <input v-model="form.features_ads" type="checkbox" id="ads" class="rounded" />
                                        <div>
                                            <label for="ads"
                                                class="font-medium text-gray-900 dark:text-white">Advertising
                                                System</label>
                                            <p class="text-sm text-gray-500">Enable banner and interstitial ads on short
                                                links.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 p-4 border rounded dark:border-gray-700">
                                        <input v-model="form.features_gdpr" type="checkbox" id="gdpr" class="rounded" />
                                        <div>
                                            <label for="gdpr" class="font-medium text-gray-900 dark:text-white">GDPR
                                                Compliance Mode</label>
                                            <p class="text-sm text-gray-500">Enable IP anonymization and data retention
                                                controls.</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Auto-Suspend
                                        Threshold (reports)</label>
                                    <input v-model="form.auto_suspend_threshold" type="number" min="1"
                                        class="mt-1 block w-32 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                    <p class="text-sm text-gray-500 mt-1">Number of unique reports within 24h to
                                        auto-suspend a link.</p>
                                </div>
                            </div>

                            <!-- Cache Tab -->
                            <div v-if="activeTab === 'cache'" class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Cache Configuration</h3>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Redirect
                                            Cache TTL (seconds)</label>
                                        <input v-model="form.cache_ttl_redirect" type="number" min="60"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Analytics
                                            Cache TTL (seconds)</label>
                                        <input v-model="form.cache_ttl_analytics" type="number" min="60"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                    </div>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Purge Cache</h4>
                                    <div class="flex gap-3">
                                        <select v-model="cacheForm.type"
                                            class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                            <option value="redirect">Redirect Cache</option>
                                            <option value="analytics">Analytics Cache</option>
                                            <option value="all">All Cache</option>
                                        </select>
                                        <button type="button" @click="purgeCache" :disabled="cacheForm.processing"
                                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50">
                                            {{ cacheForm.processing ? 'Purging...' : 'Purge' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Tab -->
                            <div v-if="activeTab === 'security'" class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Security Settings</h3>

                                <div class="flex items-center gap-4">
                                    <input v-model="form.captcha_enabled" type="checkbox" id="captcha"
                                        class="rounded" />
                                    <div>
                                        <label for="captcha" class="font-medium text-gray-900 dark:text-white">Enable
                                            CAPTCHA</label>
                                        <p class="text-sm text-gray-500">Require CAPTCHA on registration and login.</p>
                                    </div>
                                </div>
                                <div v-if="form.captcha_enabled" class="grid grid-cols-2 gap-4 pl-8">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site
                                            Key</label>
                                        <input v-model="form.captcha_site_key" type="text"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secret
                                            Key</label>
                                        <input v-model="form.captcha_secret_key" type="password"
                                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 mt-6">
                                    <input v-model="form.safe_browsing_enabled" type="checkbox" id="safe_browsing"
                                        class="rounded" />
                                    <div>
                                        <label for="safe_browsing"
                                            class="font-medium text-gray-900 dark:text-white">Google Safe
                                            Browsing</label>
                                        <p class="text-sm text-gray-500">Check URLs against Google's Safe Browsing API.
                                        </p>
                                    </div>
                                </div>
                                <div v-if="form.safe_browsing_enabled" class="pl-8">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API
                                        Key</label>
                                    <input v-model="form.safe_browsing_api_key" type="password"
                                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                                </div>
                            </div>

                            <!-- Maintenance Tab -->
                            <div v-if="activeTab === 'maintenance'" class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Maintenance Mode</h3>
                                <div
                                    class="flex items-center gap-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                                    <input v-model="form.maintenance_mode" type="checkbox" id="maintenance"
                                        class="rounded" />
                                    <div>
                                        <label for="maintenance"
                                            class="font-medium text-yellow-800 dark:text-yellow-200">Enable Maintenance
                                            Mode</label>
                                        <p class="text-sm text-yellow-600 dark:text-yellow-400">When enabled, only
                                            admins can access the site.</p>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Maintenance
                                        Message</label>
                                    <textarea v-model="form.maintenance_message" rows="3"
                                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"></textarea>
                                </div>
                            </div>

                            <!-- Import/Export Tab -->
                            <div v-if="activeTab === 'data'" class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Import / Export</h3>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="p-4 border rounded dark:border-gray-700">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Export Settings</h4>
                                        <p class="text-sm text-gray-500 mb-3">Download all settings as JSON.</p>
                                        <a :href="route('admin.settings.export')"
                                            class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Download
                                            JSON</a>
                                    </div>
                                    <div class="p-4 border rounded dark:border-gray-700">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Import Settings</h4>
                                        <p class="text-sm text-gray-500 mb-3">Upload a settings JSON file.</p>
                                        <input type="file" accept=".json" @change="handleImport"
                                            class="block w-full text-sm" />
                                    </div>
                                </div>

                                <div class="p-4 border rounded dark:border-gray-700 mt-6">
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">Database Backup</h4>
                                    <p class="text-sm text-gray-500 mb-3">Download a full database backup (SQL dump).
                                    </p>
                                    <a :href="route('admin.settings.backup')"
                                        class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Download
                                        Backup</a>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="flex justify-end pt-6 border-t dark:border-gray-700 mt-6">
                                <button type="submit" :disabled="form.processing"
                                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                                    {{ form.processing ? 'Saving...' : 'Save Settings' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
