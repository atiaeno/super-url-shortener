<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Object,
    cacheStats: Object,
});

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
    redirect_countdown: parseInt(props.settings.redirect_countdown),
    redirect_mode: props.settings.redirect_mode,
    redirect_captcha: props.settings.redirect_captcha === 'true',
});

const cacheForm = useForm({ type: 'all' });
const importForm = useForm({ file: null });

const submit = () => {
    form.post(route('admin.settings.update'), { preserveScroll: true });
};

const purgeCache = () => {
    cacheForm.post(route('admin.settings.purge-cache'), {
        preserveScroll: true,
        onSuccess: () => alert('Cache purged successfully!'),
    });
};

const handleImport = (e) => {
    importForm.file = e.target.files[0];
    importForm.post(route('admin.settings.import'), {
        preserveScroll: true,
        onSuccess: () => { importForm.reset(); e.target.value = ''; alert('Settings imported successfully!'); },
    });
};

const activeTab = ref('branding');

const tabs = [
    { id: 'branding', label: 'Branding', roman: 'I.', icon: `<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>` },
    { id: 'features', label: 'Features', roman: 'II.', icon: `<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>` },
    { id: 'redirect', label: 'Redirect Page', roman: 'III.', icon: `<polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/>` },
    { id: 'security', label: 'Security', roman: 'IV.', icon: `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>` },
    { id: 'cache', label: 'Cache', roman: 'V.', icon: `<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>` },
    { id: 'maintenance', label: 'Maintenance', roman: 'VI.', icon: `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>` },
    { id: 'data', label: 'Import / Export', roman: 'VII.', icon: `<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>` },
];

const ringOffset = computed(() => 226 * (1 - 0.6));

const icons = {
    check: `<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>`,
    zap: `<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>`,
};
</script>

<template>

    <Head title="Admin Settings" />

    <AdminLayout>
        <template #header-icon>
            <circle cx="12" cy="12" r="3" />
            <path
                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
        </template>
        <template #header>Settings</template>

        <div class="settings-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Configuration</span>
                    <h1 class="page-header__title">Platform Settings</h1>
                    <p class="page-header__sub">Manage branding, features, security, and system configuration.</p>
                </div>
            </header>

            <!-- Section Rule -->
            <div class="section-rule"></div>

            <!-- Layout: Sidebar Tabs + Content -->
            <div class="settings-layout">

                <!-- Sidebar -->
                <nav class="settings-sidebar">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        :class="['sidebar-tab', { 'sidebar-tab--active': activeTab === tab.id }]">
                        <div class="sidebar-tab__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="tab.icon" />
                        </div>
                        <div class="sidebar-tab__text">
                            <span class="sidebar-tab__roman">{{ tab.roman }}</span>
                            <span class="sidebar-tab__label">{{ tab.label }}</span>
                        </div>
                    </button>
                </nav>

                <!-- Content -->
                <div class="settings-content">
                    <form @submit.prevent="submit">

                        <!-- ═══ BRANDING ═══ -->
                        <div v-if="activeTab === 'branding'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">I.</span>
                                <h3 class="panel__title">Branding & Identity</h3>
                            </div>
                            <div class="panel__body">
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">App Name</label>
                                        <input v-model="form.app_name" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Tagline</label>
                                        <input v-model="form.app_tagline" type="text" class="field__input" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Logo URL</label>
                                        <input v-model="form.logo_url" type="url" class="field__input"
                                            placeholder="https://..." />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Favicon URL</label>
                                        <input v-model="form.favicon_url" type="url" class="field__input"
                                            placeholder="https://..." />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="field__label">Footer Text</label>
                                    <input v-model="form.footer_text" type="text" class="field__input" />
                                </div>

                                <div class="divider"></div>

                                <div class="toggle-row">
                                    <input v-model="form.donation_enabled" type="checkbox" id="donation"
                                        class="toggle-check" />
                                    <label for="donation" class="toggle-text">Enable PayPal Donation Button</label>
                                </div>
                                <div v-if="form.donation_enabled" class="field" style="padding-left:28px">
                                    <label class="field__label">PayPal Button ID</label>
                                    <input v-model="form.donation_button_id" type="text" class="field__input" />
                                </div>

                                <div class="divider"></div>

                                <div class="field">
                                    <label class="field__label">robots.txt</label>
                                    <textarea v-model="form.robots_txt" rows="5"
                                        class="field__input field__input--mono"></textarea>
                                </div>
                                <div class="toggle-row">
                                    <input v-model="form.sitemap_enabled" type="checkbox" id="sitemap"
                                        class="toggle-check" />
                                    <label for="sitemap" class="toggle-text">Enable Sitemap Generation</label>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ FEATURES ═══ -->
                        <div v-if="activeTab === 'features'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">II.</span>
                                <h3 class="panel__title">Feature Modules</h3>
                            </div>
                            <div class="panel__body">
                                <div class="feature-card" v-for="feat in [
                                    { model: 'features_affiliate', id: 'affiliate', title: 'Affiliate Program', desc: 'Enable the affiliate program for users to earn from traffic.' },
                                    { model: 'features_ads', id: 'ads', title: 'Advertising System', desc: 'Enable banner and interstitial ads on short links.' },
                                    { model: 'features_gdpr', id: 'gdpr', title: 'GDPR Compliance Mode', desc: 'Enable IP anonymization and data retention controls.' },
                                ]" :key="feat.id">
                                    <input v-model="form[feat.model]" type="checkbox" :id="feat.id"
                                        class="toggle-check" />
                                    <div>
                                        <label :for="feat.id" class="feature-card__title">{{ feat.title }}</label>
                                        <p class="feature-card__desc">{{ feat.desc }}</p>
                                    </div>
                                </div>

                                <div class="divider"></div>

                                <div class="field">
                                    <label class="field__label">Auto-Suspend Threshold (reports)</label>
                                    <input v-model="form.auto_suspend_threshold" type="number" min="1"
                                        class="field__input field__input--sm" />
                                    <p class="field__hint">Number of unique reports within 24h to auto-suspend a link.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ REDIRECT PAGE ═══ -->
                        <div v-if="activeTab === 'redirect'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">III.</span>
                                <h3 class="panel__title">Redirect Page</h3>
                            </div>
                            <div class="panel__body">
                                <p class="panel__desc">Configure the interstitial page visitors see before reaching the
                                    destination URL.</p>

                                <!-- Countdown preview -->
                                <div class="redirect-preview">
                                    <div class="redirect-preview__ring">
                                        <svg viewBox="0 0 80 80" width="56" height="56">
                                            <circle cx="40" cy="40" r="36" fill="none" stroke="var(--border)"
                                                stroke-width="3" />
                                            <circle cx="40" cy="40" r="36" fill="none" stroke="var(--red)"
                                                stroke-width="3" stroke-dasharray="226" :stroke-dashoffset="ringOffset"
                                                stroke-linecap="round"
                                                style="transform:rotate(-90deg);transform-origin:center" />
                                        </svg>
                                        <span class="redirect-preview__num">{{ form.redirect_countdown }}</span>
                                    </div>
                                    <div>
                                        <span class="redirect-preview__value">{{ form.redirect_countdown }}s</span>
                                        <span class="redirect-preview__label">Countdown Duration</span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="field__label">Countdown Duration (seconds)</label>
                                    <input v-model="form.redirect_countdown" type="number" min="0" max="60"
                                        class="field__input field__input--sm" />
                                    <p class="field__hint">Set to 0 to skip the timer entirely.</p>
                                </div>

                                <div class="divider"></div>

                                <div class="field">
                                    <label class="field__label">Redirect Mode</label>
                                    <div class="mode-grid">
                                        <label class="mode-card"
                                            :class="{ 'mode-card--active': form.redirect_mode === 'auto' }">
                                            <input v-model="form.redirect_mode" type="radio" value="auto"
                                                class="mode-card__radio" />
                                            <div class="mode-card__icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.5" v-html="icons.zap" />
                                            </div>
                                            <span class="mode-card__title">Auto Redirect</span>
                                            <span class="mode-card__desc">Visitor redirected automatically after
                                                countdown.</span>
                                        </label>
                                        <label class="mode-card"
                                            :class="{ 'mode-card--active': form.redirect_mode === 'click' }">
                                            <input v-model="form.redirect_mode" type="radio" value="click"
                                                class="mode-card__radio" />
                                            <div class="mode-card__icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.5">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                    <line x1="12" y1="8" x2="12" y2="16" />
                                                    <line x1="8" y1="12" x2="16" y2="12" />
                                                </svg>
                                            </div>
                                            <span class="mode-card__title">Button Click</span>
                                            <span class="mode-card__desc">Visitor must click "Continue" button.</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="divider"></div>

                                <div class="feature-card">
                                    <input v-model="form.redirect_captcha" type="checkbox" id="redirect_captcha"
                                        class="toggle-check" />
                                    <div>
                                        <label for="redirect_captcha" class="feature-card__title">Require
                                            CAPTCHA</label>
                                        <p class="feature-card__desc">Show a CAPTCHA challenge on the redirect page
                                            before
                                            allowing access. Helps prevent bots.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ SECURITY ═══ -->
                        <div v-if="activeTab === 'security'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">IV.</span>
                                <h3 class="panel__title">Security</h3>
                            </div>
                            <div class="panel__body">
                                <div class="feature-card">
                                    <input v-model="form.captcha_enabled" type="checkbox" id="captcha"
                                        class="toggle-check" />
                                    <div>
                                        <label for="captcha" class="feature-card__title">Enable CAPTCHA</label>
                                        <p class="feature-card__desc">Require CAPTCHA on registration and login.</p>
                                    </div>
                                </div>
                                <div v-if="form.captcha_enabled" class="field-grid" style="padding-left:28px">
                                    <div class="field">
                                        <label class="field__label">Site Key</label>
                                        <input v-model="form.captcha_site_key" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Secret Key</label>
                                        <input v-model="form.captcha_secret_key" type="password" class="field__input" />
                                    </div>
                                </div>

                                <div class="divider"></div>

                                <div class="feature-card">
                                    <input v-model="form.safe_browsing_enabled" type="checkbox" id="safe_browsing"
                                        class="toggle-check" />
                                    <div>
                                        <label for="safe_browsing" class="feature-card__title">Google Safe
                                            Browsing</label>
                                        <p class="feature-card__desc">Check URLs against Google's Safe Browsing API.</p>
                                    </div>
                                </div>
                                <div v-if="form.safe_browsing_enabled" class="field" style="padding-left:28px">
                                    <label class="field__label">API Key</label>
                                    <input v-model="form.safe_browsing_api_key" type="password" class="field__input" />
                                </div>
                            </div>
                        </div>

                        <!-- ═══ CACHE ═══ -->
                        <div v-if="activeTab === 'cache'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">V.</span>
                                <h3 class="panel__title">Cache Configuration</h3>
                            </div>
                            <div class="panel__body">
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Redirect Cache TTL (seconds)</label>
                                        <input v-model="form.cache_ttl_redirect" type="number" min="60"
                                            class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Analytics Cache TTL (seconds)</label>
                                        <input v-model="form.cache_ttl_analytics" type="number" min="60"
                                            class="field__input" />
                                    </div>
                                </div>

                                <div class="divider"></div>

                                <div class="purge-box">
                                    <div class="purge-box__header">
                                        <svg class="purge-box__icon" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                        <span class="purge-box__title">Purge Cache</span>
                                    </div>
                                    <div class="purge-box__actions">
                                        <select v-model="cacheForm.type" class="field__select">
                                            <option value="redirect">Redirect Cache</option>
                                            <option value="analytics">Analytics Cache</option>
                                            <option value="all">All Cache</option>
                                        </select>
                                        <button type="button" @click="purgeCache" :disabled="cacheForm.processing"
                                            class="btn btn--danger">
                                            {{ cacheForm.processing ? 'Purging...' : 'Purge Now' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ MAINTENANCE ═══ -->
                        <div v-if="activeTab === 'maintenance'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">VI.</span>
                                <h3 class="panel__title">Maintenance Mode</h3>
                            </div>
                            <div class="panel__body">
                                <div class="maintenance-banner"
                                    :class="{ 'maintenance-banner--on': form.maintenance_mode }">
                                    <input v-model="form.maintenance_mode" type="checkbox" id="maintenance"
                                        class="toggle-check" />
                                    <div>
                                        <label for="maintenance" class="maintenance-banner__title">{{
                                            form.maintenance_mode ?
                                            'Maintenance Mode Active' : 'Enable Maintenance Mode' }}</label>
                                        <p class="maintenance-banner__desc">When enabled, only admins can access the
                                            site.</p>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="field__label">Maintenance Message</label>
                                    <textarea v-model="form.maintenance_message" rows="3"
                                        class="field__input"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ IMPORT / EXPORT ═══ -->
                        <div v-if="activeTab === 'data'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">VII.</span>
                                <h3 class="panel__title">Import / Export</h3>
                            </div>
                            <div class="panel__body">
                                <div class="data-grid">
                                    <div class="data-tile">
                                        <div class="data-tile__icon data-tile__icon--blue">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="7 10 12 15 17 10" />
                                                <line x1="12" y1="15" x2="12" y2="3" />
                                            </svg>
                                        </div>
                                        <div class="data-tile__text">
                                            <strong>Export Settings</strong>
                                            <span>Download all settings as JSON</span>
                                        </div>
                                        <a :href="route('admin.settings.export')" class="btn btn--primary">Download</a>
                                    </div>
                                    <div class="data-tile">
                                        <div class="data-tile__icon data-tile__icon--green">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="17 8 12 3 7 8" />
                                                <line x1="12" y1="3" x2="12" y2="15" />
                                            </svg>
                                        </div>
                                        <div class="data-tile__text">
                                            <strong>Import Settings</strong>
                                            <span>Upload a settings JSON file</span>
                                        </div>
                                        <label class="btn btn--ghost file-upload-btn">
                                            Choose File
                                            <input type="file" accept=".json" @change="handleImport" class="sr-only" />
                                        </label>
                                    </div>
                                    <div class="data-tile data-tile--full">
                                        <div class="data-tile__icon data-tile__icon--gold">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <ellipse cx="12" cy="5" rx="9" ry="3" />
                                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                                            </svg>
                                        </div>
                                        <div class="data-tile__text">
                                            <strong>Database Backup</strong>
                                            <span>Download a full SQL dump</span>
                                        </div>
                                        <a :href="route('admin.settings.backup')" class="btn btn--success">Download</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Save Footer -->
                        <div class="save-bar">
                            <div class="save-bar__status" v-if="form.recentlySuccessful">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    v-html="icons.check" />
                                <span>Saved successfully</span>
                            </div>
                            <button type="submit" :disabled="form.processing" class="btn btn--primary btn--lg">
                                {{ form.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
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
    --surface: #fff;
    --surface-2: #f5f3ef;
    --radius: 4px;
    --transition: all 0.2s ease;
}

/* ── Page Header ──────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 20px;
}

.page-header__marker {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--red);
    display: block;
    margin-bottom: 8px;
}

.page-header__title {
    font-family: var(--font-display);
    font-size: 24px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 4px;
}

.page-header__sub {
    font-family: var(--font-body);
    font-size: 15px;
    font-style: italic;
    color: var(--muted);
    margin: 0;
}

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Layout ───────────────────────────────── */
.settings-layout {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    min-height: 500px;
}

/* ── Sidebar ──────────────────────────────── */
.settings-sidebar {
    background: var(--surface);
    display: flex;
    flex-direction: column;
    padding: 8px 0;
}

.sidebar-tab {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    background: none;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    text-align: left;
    border-left: 2px solid transparent;
}

.sidebar-tab:hover {
    background: var(--surface-2);
}

.sidebar-tab--active {
    background: var(--surface-2);
    border-left-color: var(--red);
}

.sidebar-tab__icon {
    width: 20px;
    height: 20px;
    color: var(--muted);
    flex-shrink: 0;
    transition: var(--transition);
}

.sidebar-tab__icon svg {
    width: 100%;
    height: 100%;
}

.sidebar-tab--active .sidebar-tab__icon {
    color: var(--red);
}

.sidebar-tab__text {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.sidebar-tab__roman {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 700;
    color: var(--muted);
    transition: var(--transition);
}

.sidebar-tab--active .sidebar-tab__roman {
    color: var(--red);
}

.sidebar-tab__label {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink-soft);
    transition: var(--transition);
}

.sidebar-tab--active .sidebar-tab__label {
    color: var(--ink);
    font-weight: 600;
}

/* ── Content Area ─────────────────────────── */
.settings-content {
    background: var(--surface);
    padding: 0;
}

/* ── Panel ────────────────────────────────── */
.panel {
    animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.panel__header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 20px 28px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
}

.panel__marker {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    color: var(--red);
}

.panel__title {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--ink);
    margin: 0;
}

.panel__body {
    padding: 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.panel__desc {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--muted);
    font-style: italic;
    margin: -8px 0 0;
}

/* ── Fields ───────────────────────────────── */
.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field__label {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink-soft);
}

.field__input {
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: var(--transition);
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.08);
}

.field__input--sm {
    width: 100px;
}

.field__input--mono {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    resize: vertical;
}

.field__select {
    padding: 10px 32px 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    min-width: 160px;
    transition: var(--transition);
}

.field__select:focus {
    outline: none;
    border-color: var(--red);
}

.field__hint {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
    margin-top: 2px;
}

/* ── Toggle / Checkbox ────────────────────── */
.toggle-check {
    width: 16px;
    height: 16px;
    accent-color: var(--red);
    margin-top: 2px;
    flex-shrink: 0;
    cursor: pointer;
}

.toggle-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toggle-text {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
    cursor: pointer;
}

/* ── Feature Card ─────────────────────────── */
.feature-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 18px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
}

.feature-card:hover {
    border-color: #d4d0cb;
}

.feature-card__title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    display: block;
    cursor: pointer;
}

.feature-card__desc {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--muted);
    margin-top: 4px;
    line-height: 1.4;
}

/* ── Divider ──────────────────────────────── */
.divider {
    height: 1px;
    background: var(--border);
}

/* ── Redirect Preview ─────────────────────── */
.redirect-preview {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.redirect-preview__ring {
    position: relative;
    width: 56px;
    height: 56px;
    flex-shrink: 0;
}

.redirect-preview__num {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 700;
    color: var(--ink);
}

.redirect-preview__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    display: block;
    line-height: 1;
}

.redirect-preview__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    display: block;
    margin-top: 4px;
}

/* ── Mode Cards ───────────────────────────── */
.mode-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 4px;
}

.mode-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    text-align: center;
}

.mode-card:hover {
    border-color: var(--ink-soft);
}

.mode-card--active {
    border-color: var(--red);
    background: #fef2f2;
}

.mode-card__radio {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.mode-card__icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius);
    background: var(--surface-2);
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.mode-card--active .mode-card__icon {
    background: var(--red);
    color: var(--surface);
}

.mode-card__icon svg {
    width: 16px;
    height: 16px;
}

.mode-card__title {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink);
}

.mode-card__desc {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
    line-height: 1.4;
}

/* ── Purge Box ────────────────────────────── */
.purge-box {
    padding: 20px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.purge-box__header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.purge-box__icon {
    width: 18px;
    height: 18px;
    color: var(--red);
}

.purge-box__title {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--ink);
}

.purge-box__actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* ── Maintenance Banner ───────────────────── */
.maintenance-banner {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 18px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
}

.maintenance-banner--on {
    background: #fffbeb;
    border-color: #fcd34d;
}

.maintenance-banner__title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    display: block;
    cursor: pointer;
}

.maintenance-banner--on .maintenance-banner__title {
    color: #92400e;
}

.maintenance-banner__desc {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--muted);
    margin-top: 4px;
}

.maintenance-banner--on .maintenance-banner__desc {
    color: #b45309;
}

/* ── Data Grid ────────────────────────────── */
.data-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.data-tile {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
}

.data-tile:hover {
    border-color: #d4d0cb;
}

.data-tile--full {
    grid-column: 1 / -1;
}

.data-tile__icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.data-tile__icon svg {
    width: 18px;
    height: 18px;
}

.data-tile__icon--blue {
    background: #eff6ff;
    color: #3b82f6;
}

.data-tile__icon--green {
    background: #f0fdf4;
    color: #16a34a;
}

.data-tile__icon--gold {
    background: #fef9f0;
    color: var(--gold);
}

.data-tile__text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.data-tile__text strong {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
}

.data-tile__text span {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
}

/* ── Buttons ──────────────────────────────── */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn--primary {
    background: var(--red);
    border-color: var(--red);
    color: var(--surface);
}

.btn--primary:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.btn--primary:disabled {
    opacity: 0.5;
    pointer-events: none;
}

.btn--danger {
    background: var(--red);
    border-color: var(--red);
    color: var(--surface);
}

.btn--danger:hover {
    background: var(--red-dark);
}

.btn--success {
    background: #22c55e;
    border-color: #22c55e;
    color: var(--surface);
}

.btn--success:hover {
    background: #16a34a;
}

.btn--ghost {
    background: var(--surface);
    border-color: var(--border);
    color: var(--ink);
}

.btn--ghost:hover {
    background: var(--surface-2);
    border-color: var(--ink-soft);
}

.btn--lg {
    padding: 11px 28px;
    font-size: 12px;
}

/* ── Save Bar ─────────────────────────────── */
.save-bar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 16px;
    padding: 18px 28px;
    background: var(--surface);
    border-top: 1px solid var(--border);
}

.save-bar__status {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #16a34a;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.save-bar__status svg {
    width: 16px;
    height: 16px;
}

/* ── Utilities ────────────────────────────── */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.file-upload-btn {
    cursor: pointer;
}

/* ── Responsive ───────────────────────────── */
@media (max-width: 768px) {
    .settings-layout {
        grid-template-columns: 1fr;
    }

    .settings-sidebar {
        flex-direction: row;
        overflow-x: auto;
        padding: 0;
        border-bottom: 1px solid var(--border);
    }

    .sidebar-tab {
        border-left: none;
        border-bottom: 2px solid transparent;
        padding: 10px 14px;
        white-space: nowrap;
    }

    .sidebar-tab--active {
        border-left-color: transparent;
        border-bottom-color: var(--red);
    }

    .sidebar-tab__roman {
        display: none;
    }

    .field-grid,
    .mode-grid,
    .data-grid {
        grid-template-columns: 1fr;
    }

    .panel__body {
        padding: 20px 18px;
    }

    .panel__header {
        padding: 16px 18px;
    }

    .save-bar {
        padding: 14px 18px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
