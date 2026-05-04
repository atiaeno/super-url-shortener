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
                <div class="settings-card">
                    <div class="settings-content">
                        <h2 class="settings-title">Admin Settings</h2>

                        <!-- Tabs -->
                        <div class="settings-tabs">
                            <nav class="tabs-nav">
                                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                                    :class="['tab-btn', { 'tab-btn--active': activeTab === tab.id }]">
                                    {{ tab.label }}
                                </button>
                            </nav>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Branding Tab -->
                            <div v-if="activeTab === 'branding'" class="tab-content">
                                <h3 class="section-title">Branding</h3>
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label class="field-label">App Name</label>
                                        <input v-model="form.app_name" type="text" class="field-input" />
                                    </div>
                                    <div class="form-field">
                                        <label class="field-label">Tagline</label>
                                        <input v-model="form.app_tagline" type="text" class="field-input" />
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label class="field-label">Logo URL</label>
                                        <input v-model="form.logo_url" type="url" class="field-input"
                                            placeholder="https://..." />
                                    </div>
                                    <div class="form-field">
                                        <label class="field-label">Favicon URL</label>
                                        <input v-model="form.favicon_url" type="url" class="field-input"
                                            placeholder="https://..." />
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label class="field-label">Footer Text</label>
                                    <input v-model="form.footer_text" type="text" class="field-input" />
                                </div>
                                <div class="checkbox-row">
                                    <input v-model="form.donation_enabled" type="checkbox" id="donation"
                                        class="field-checkbox" />
                                    <label for="donation" class="checkbox-label">Enable PayPal Donation</label>
                                </div>
                                <div v-if="form.donation_enabled" class="form-field">
                                    <label class="field-label">PayPal Button ID</label>
                                    <input v-model="form.donation_button_id" type="text" class="field-input" />
                                </div>
                                <div class="form-field">
                                    <label class="field-label">robots.txt</label>
                                    <textarea v-model="form.robots_txt" rows="5" class="field-textarea"></textarea>
                                </div>
                                <div class="checkbox-row">
                                    <input v-model="form.sitemap_enabled" type="checkbox" id="sitemap"
                                        class="field-checkbox" />
                                    <label for="sitemap" class="checkbox-label">Enable Sitemap Generation</label>
                                </div>
                            </div>

                            <!-- Features Tab -->
                            <div v-if="activeTab === 'features'" class="tab-content">
                                <h3 class="section-title">Feature Modules</h3>
                                <div class="feature-list">
                                    <div class="feature-item">
                                        <input v-model="form.features_affiliate" type="checkbox" id="affiliate"
                                            class="field-checkbox" />
                                        <div>
                                            <label for="affiliate" class="feature-label">Affiliate Program</label>
                                            <p class="feature-desc">Enable the affiliate program for users to earn from
                                                traffic.</p>
                                        </div>
                                    </div>
                                    <div class="feature-item">
                                        <input v-model="form.features_ads" type="checkbox" id="ads"
                                            class="field-checkbox" />
                                        <div>
                                            <label for="ads" class="feature-label">Advertising System</label>
                                            <p class="feature-desc">Enable banner and interstitial ads on short links.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="feature-item">
                                        <input v-model="form.features_gdpr" type="checkbox" id="gdpr"
                                            class="field-checkbox" />
                                        <div>
                                            <label for="gdpr" class="feature-label">GDPR Compliance Mode</label>
                                            <p class="feature-desc">Enable IP anonymization and data retention controls.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label class="field-label">Auto-Suspend Threshold (reports)</label>
                                    <input v-model="form.auto_suspend_threshold" type="number" min="1"
                                        class="field-input field-input--sm" />
                                    <p class="field-hint">Number of unique reports within 24h to auto-suspend a link.
                                    </p>
                                </div>
                            </div>

                            <!-- Cache Tab -->
                            <div v-if="activeTab === 'cache'" class="tab-content">
                                <h3 class="section-title">Cache Configuration</h3>
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label class="field-label">Redirect Cache TTL (seconds)</label>
                                        <input v-model="form.cache_ttl_redirect" type="number" min="60"
                                            class="field-input" />
                                    </div>
                                    <div class="form-field">
                                        <label class="field-label">Analytics Cache TTL (seconds)</label>
                                        <input v-model="form.cache_ttl_analytics" type="number" min="60"
                                            class="field-input" />
                                    </div>
                                </div>

                                <div class="cache-box">
                                    <h4 class="cache-title">Purge Cache</h4>
                                    <div class="cache-actions">
                                        <select v-model="cacheForm.type" class="field-select">
                                            <option value="redirect">Redirect Cache</option>
                                            <option value="analytics">Analytics Cache</option>
                                            <option value="all">All Cache</option>
                                        </select>
                                        <button type="button" @click="purgeCache" :disabled="cacheForm.processing"
                                            class="btn-danger">
                                            {{ cacheForm.processing ? 'Purging...' : 'Purge' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Tab -->
                            <div v-if="activeTab === 'security'" class="tab-content">
                                <h3 class="section-title">Security Settings</h3>

                                <div class="checkbox-row">
                                    <input v-model="form.captcha_enabled" type="checkbox" id="captcha"
                                        class="field-checkbox" />
                                    <div>
                                        <label for="captcha" class="feature-label">Enable CAPTCHA</label>
                                        <p class="feature-desc">Require CAPTCHA on registration and login.</p>
                                    </div>
                                </div>
                                <div v-if="form.captcha_enabled" class="form-grid" style="padding-left: 24px;">
                                    <div class="form-field">
                                        <label class="field-label">Site Key</label>
                                        <input v-model="form.captcha_site_key" type="text" class="field-input" />
                                    </div>
                                    <div class="form-field">
                                        <label class="field-label">Secret Key</label>
                                        <input v-model="form.captcha_secret_key" type="password" class="field-input" />
                                    </div>
                                </div>

                                <div class="checkbox-row" style="margin-top: 24px;">
                                    <input v-model="form.safe_browsing_enabled" type="checkbox" id="safe_browsing"
                                        class="field-checkbox" />
                                    <div>
                                        <label for="safe_browsing" class="feature-label">Google Safe Browsing</label>
                                        <p class="feature-desc">Check URLs against Google's Safe Browsing API.</p>
                                    </div>
                                </div>
                                <div v-if="form.safe_browsing_enabled" style="padding-left: 24px;">
                                    <label class="field-label">API Key</label>
                                    <input v-model="form.safe_browsing_api_key" type="password" class="field-input" />
                                </div>
                            </div>

                            <!-- Maintenance Tab -->
                            <div v-if="activeTab === 'maintenance'" class="tab-content">
                                <h3 class="section-title">Maintenance Mode</h3>
                                <div class="maintenance-box">
                                    <input v-model="form.maintenance_mode" type="checkbox" id="maintenance"
                                        class="field-checkbox" />
                                    <div>
                                        <label for="maintenance" class="maintenance-label">Enable Maintenance
                                            Mode</label>
                                        <p class="maintenance-desc">When enabled, only admins can access the site.</p>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label class="field-label">Maintenance Message</label>
                                    <textarea v-model="form.maintenance_message" rows="3"
                                        class="field-textarea"></textarea>
                                </div>
                            </div>

                            <!-- Import/Export Tab -->
                            <div v-if="activeTab === 'data'" class="tab-content">
                                <h3 class="section-title">Import / Export</h3>

                                <div class="form-grid">
                                    <div class="data-card">
                                        <h4 class="data-title">Export Settings</h4>
                                        <p class="data-desc">Download all settings as JSON.</p>
                                        <a :href="route('admin.settings.export')" class="btn-primary">Download JSON</a>
                                    </div>
                                    <div class="data-card">
                                        <h4 class="data-title">Import Settings</h4>
                                        <p class="data-desc">Upload a settings JSON file.</p>
                                        <input type="file" accept=".json" @change="handleImport" class="field-file" />
                                    </div>
                                </div>

                                <div class="data-card data-card--full">
                                    <h4 class="data-title">Database Backup</h4>
                                    <p class="data-desc">Download a full database backup (SQL dump).</p>
                                    <a :href="route('admin.settings.backup')" class="btn-success">Download Backup</a>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="form-actions">
                                <button type="submit" :disabled="form.processing" class="btn-primary">
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

<style>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

:root {
    --font-display: 'Oswald', sans-serif;
    --font-body: 'Crimson Pro', serif;
    --red: #e74c3c;
    --red-dark: #c0392b;
    --gold: #d4af37;
    --ink: #1a1a1a;
    --ink-soft: #444;
    --muted: #888;
    --border: #e8e5e0;
    --bg: #fafafa;
    --surface: #fff;
    --surface-2: #f5f3ef;
    --radius: 4px;
    --transition: all 0.2s ease;
}

.settings-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.settings-content {
    padding: 24px;
}

.settings-title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 20px;
}

.settings-tabs {
    border-bottom: 1px solid var(--border);
    margin-bottom: 24px;
}

.tabs-nav {
    display: flex;
    gap: 8px;
}

.tab-btn {
    padding: 10px 16px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--muted);
    cursor: pointer;
    transition: var(--transition);
}

.tab-btn:hover {
    color: var(--ink);
}

.tab-btn--active {
    color: var(--red);
    border-bottom-color: var(--red);
}

.tab-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.section-title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ink-soft);
}

.field-input,
.field-textarea,
.field-select {
    padding: 10px 12px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 13px;
    color: var(--ink);
    transition: var(--transition);
}

.field-input:focus,
.field-textarea:focus,
.field-select:focus {
    outline: none;
    border-color: var(--red);
}

.field-input--sm {
    width: 100px;
}

.field-textarea {
    font-family: monospace;
    resize: vertical;
}

.field-hint {
    font-size: 11px;
    color: var(--muted);
    margin-top: 4px;
}

.checkbox-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.field-checkbox {
    width: 16px;
    height: 16px;
    accent-color: var(--red);
    margin-top: 2px;
}

.checkbox-label {
    font-size: 13px;
    color: var(--ink);
}

.feature-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.feature-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    display: block;
}

.feature-desc {
    font-size: 12px;
    color: var(--muted);
    margin-top: 4px;
}

.cache-box {
    padding: 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.cache-title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 12px;
}

.cache-actions {
    display: flex;
    gap: 12px;
}

.field-select {
    min-width: 160px;
}

.maintenance-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: var(--radius);
}

.maintenance-label {
    font-size: 14px;
    font-weight: 600;
    color: #92400e;
    display: block;
}

.maintenance-desc {
    font-size: 12px;
    color: #b45309;
    margin-top: 4px;
}

.data-card {
    padding: 20px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.data-card--full {
    margin-top: 16px;
}

.data-title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 8px;
}

.data-desc {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 16px;
}

.field-file {
    font-size: 12px;
    color: var(--ink-soft);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: var(--red);
    color: var(--surface);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-primary:hover {
    background: var(--red-dark);
}

.btn-danger {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: var(--red);
    color: var(--surface);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
}

.btn-danger:hover {
    background: var(--red-dark);
}

.btn-success {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: #22c55e;
    color: var(--surface);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-success:hover {
    background: #16a34a;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    margin-top: 20px;
}
</style>
