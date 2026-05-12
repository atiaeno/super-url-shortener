<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import '@/../css/admin_setting.css';

const props = defineProps({
    settings: Object,
    cacheStats: Object,
});

const form = useForm({
    app_name: props.settings.app_name,
    app_tagline: props.settings.app_tagline,
    logo: null,
    favicon: null,
    logo_url: props.settings.logo_url,
    favicon_url: props.settings.favicon_url,
    footer_text: props.settings.footer_text,
    meta_description: props.settings.meta_description,
    meta_keywords: props.settings.meta_keywords,
    og_image: null,
    og_image_url: props.settings.og_image,
    schema_json: props.settings.schema_json,
    // Per-page SEO
    seo_home_title: props.settings.seo_home_title,
    seo_home_description: props.settings.seo_home_description,
    seo_privacy_title: props.settings.seo_privacy_title,
    seo_privacy_description: props.settings.seo_privacy_description,
    seo_terms_title: props.settings.seo_terms_title,
    seo_terms_description: props.settings.seo_terms_description,
    seo_cookies_title: props.settings.seo_cookies_title,
    seo_cookies_description: props.settings.seo_cookies_description,
    seo_gdpr_title: props.settings.seo_gdpr_title,
    seo_gdpr_description: props.settings.seo_gdpr_description,
    seo_help_title: props.settings.seo_help_title,
    seo_help_description: props.settings.seo_help_description,
    seo_affiliate_title: props.settings.seo_affiliate_title,
    seo_affiliate_description: props.settings.seo_affiliate_description,
    seo_contact_title: props.settings.seo_contact_title,
    seo_contact_description: props.settings.seo_contact_description,
    seo_api_docs_title: props.settings.seo_api_docs_title,
    seo_api_docs_description: props.settings.seo_api_docs_description,
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
    captcha_provider: props.settings.captcha_provider || 'recaptcha',
    captcha_site_key: props.settings.captcha_site_key,
    captcha_secret_key: props.settings.captcha_secret_key,
    turnstile_site_key: props.settings.turnstile_site_key,
    turnstile_secret_key: props.settings.turnstile_secret_key,
    // Per-page CAPTCHA settings
    captcha_login: props.settings.captcha_login === 'true',
    captcha_register: props.settings.captcha_register === 'true',
    captcha_forgot_password: props.settings.captcha_forgot_password === 'true',
    captcha_redirect: props.settings.captcha_redirect === 'true',
    safe_browsing_enabled: props.settings.safe_browsing_enabled === 'true',
    safe_browsing_api_key: props.settings.safe_browsing_api_key,
    auto_suspend_threshold: parseInt(props.settings.auto_suspend_threshold),
    robots_txt: props.settings.robots_txt,
    sitemap_enabled: props.settings.sitemap_enabled === 'true',
    redirect_countdown: parseInt(props.settings.redirect_countdown),
    redirect_mode: props.settings.redirect_mode,
    affiliate_min_payout: parseFloat(props.settings.affiliate_min_payout) || 50,
    affiliate_payout_methods: props.settings.affiliate_payout_methods || 'PayPal',
    api_rate_limit_per_hour: parseInt(props.settings.api_rate_limit_per_hour) || 100,
    api_token_rate_limit_per_hour: parseInt(props.settings.api_token_rate_limit_per_hour) || 10,
});

const cacheForm = useForm({ type: 'all' });
const importForm = useForm({ file: null });

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            // Force page reload to get fresh settings data
            window.location.reload();
        }
    });
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
    { id: 'seo', label: 'SEO', roman: 'II.', icon: `<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>` },
    { id: 'features', label: 'Features', roman: 'III.', icon: `<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>` },
    { id: 'redirect', label: 'Redirect Page', roman: 'IV.', icon: `<polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/>` },
    { id: 'security', label: 'Security', roman: 'V.', icon: `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>` },
    { id: 'cache', label: 'Cache', roman: 'VI.', icon: `<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>` },
    { id: 'maintenance', label: 'Maintenance', roman: 'VII.', icon: `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>` },
    { id: 'data', label: 'Import / Export', roman: 'VIII.', icon: `<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>` },
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
                                        <label class="field__label">Logo (Upload)</label>
                                        <input type="file" @input="form.logo = $event.target.files[0]"
                                            class="field__input" accept="image/*" />
                                        <p class="field__hint">PNG, JPG, SVG, WebP (max 2MB)</p>
                                        <img v-if="form.logo_url" :src="form.logo_url"
                                            style="height:40px;margin-top:8px" alt="Logo" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Favicon (Upload)</label>
                                        <input type="file" @input="form.favicon = $event.target.files[0]"
                                            class="field__input" accept="image/*" />
                                        <p class="field__hint">PNG, ICO, JPG (max 512KB)</p>
                                        <img v-if="form.favicon_url" :src="form.favicon_url"
                                            style="height:32px;margin-top:8px" alt="Favicon" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Logo URL (or enter URL)</label>
                                        <input v-model="form.logo_url" type="text" class="field__input"
                                            placeholder="https://... or leave empty if uploading" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Favicon URL (or enter URL)</label>
                                        <input v-model="form.favicon_url" type="text" class="field__input"
                                            placeholder="https://... or leave empty if uploading" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="field__label">OG Image / Cover (Upload)</label>
                                    <input type="file" @input="form.og_image = $event.target.files[0]"
                                        class="field__input" accept="image/*" />
                                    <p class="field__hint">1200x630px recommended for social sharing</p>
                                    <img v-if="form.og_image_url" :src="form.og_image_url"
                                        style="height:80px;margin-top:8px;border-radius:4px" alt="OG Image" />
                                </div>
                                <div class="field">
                                    <label class="field__label">Footer Text</label>
                                    <input v-model="form.footer_text" type="text" class="field__input" />
                                </div>

                                <div class="divider"></div>

                                <h4 style="margin:16px 0 12px;font-size:14px;color:#444">SEO Settings</h4>
                                <div class="field">
                                    <label class="field__label">Meta Description</label>
                                    <textarea v-model="form.meta_description" rows="2" class="field__input"
                                        placeholder="Page description for search engines"></textarea>
                                </div>
                                <div class="field">
                                    <label class="field__label">Meta Keywords</label>
                                    <input v-model="form.meta_keywords" type="text" class="field__input"
                                        placeholder="url shortener, link tracker, analytics" />
                                </div>
                                <div class="field">
                                    <label class="field__label">Schema.org JSON-LD</label>
                                    <textarea v-model="form.schema_json" rows="3"
                                        class="field__input field__input--mono"
                                        placeholder='{"@context":"https://schema.org",...}'></textarea>
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

                        <!-- ═══ SEO ═══ -->
                        <div v-if="activeTab === 'seo'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">II.</span>
                                <h3 class="panel__title">Page SEO Settings</h3>
                            </div>
                            <div class="panel__body">
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Home — Title</label>
                                        <input v-model="form.seo_home_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Home — Description</label>
                                        <input v-model="form.seo_home_description" type="text" class="field__input" />
                                    </div>
                                </div>
                                <div class="divider"></div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Privacy — Title</label>
                                        <input v-model="form.seo_privacy_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Privacy — Description</label>
                                        <input v-model="form.seo_privacy_description" type="text"
                                            class="field__input" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Terms — Title</label>
                                        <input v-model="form.seo_terms_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Terms — Description</label>
                                        <input v-model="form.seo_terms_description" type="text" class="field__input" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Cookies — Title</label>
                                        <input v-model="form.seo_cookies_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Cookies — Description</label>
                                        <input v-model="form.seo_cookies_description" type="text"
                                            class="field__input" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">GDPR — Title</label>
                                        <input v-model="form.seo_gdpr_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">GDPR — Description</label>
                                        <input v-model="form.seo_gdpr_description" type="text" class="field__input" />
                                    </div>
                                </div>
                                <div class="divider"></div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Help — Title</label>
                                        <input v-model="form.seo_help_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Help — Description</label>
                                        <input v-model="form.seo_help_description" type="text" class="field__input" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Affiliate — Title</label>
                                        <input v-model="form.seo_affiliate_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Affiliate — Description</label>
                                        <input v-model="form.seo_affiliate_description" type="text"
                                            class="field__input" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">Contact — Title</label>
                                        <input v-model="form.seo_contact_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Contact — Description</label>
                                        <input v-model="form.seo_contact_description" type="text"
                                            class="field__input" />
                                    </div>
                                </div>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">API Docs — Title</label>
                                        <input v-model="form.seo_api_docs_title" type="text" class="field__input" />
                                    </div>
                                    <div class="field">
                                        <label class="field__label">API Docs — Description</label>
                                        <input v-model="form.seo_api_docs_description" type="text"
                                            class="field__input" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ FEATURES ═══ -->
                        <div v-if="activeTab === 'features'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">III.</span>
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
                                    <div class="feature-card__content">
                                        <label :for="feat.id" class="feature-card__title">{{ feat.title }}</label>
                                        <p class="feature-card__desc">{{ feat.desc }}</p>
                                    </div>
                                </div>

                                <div class="divider"></div>

                                <div v-if="form.features_affiliate" class="field-grid" style="padding-left:28px">
                                    <div class="field">
                                        <label class="field__label">Affiliate Payout Methods</label>
                                        <input v-model="form.affiliate_payout_methods" type="text" class="field__input"
                                            placeholder="PayPal,Bank Transfer,Crypto" />
                                        <p class="field__hint">Comma-separated list of available payout methods for
                                            affiliates.
                                        </p>
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Minimum Payout ($)</label>
                                        <input v-model="form.affiliate_min_payout" type="number" min="1" step="0.01"
                                            class="field__input" />
                                        <p class="field__hint">Minimum amount required before affiliates can request
                                            payout.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ REDIRECT PAGE ═══ -->
                        <div v-if="activeTab === 'redirect'" class="panel">
                            <div class="panel__header">
                                <span class="panel__marker">IV.</span>
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
                                <div v-if="form.captcha_enabled" style="padding-left:28px">
                                    <div class="field">
                                        <label class="field__label">CAPTCHA Provider</label>
                                        <select v-model="form.captcha_provider" class="field__input">
                                            <option value="recaptcha">Google reCAPTCHA</option>
                                            <option value="turnstile">Cloudflare Turnstile</option>
                                            <option value="disabled">Disabled</option>
                                        </select>
                                    </div>

                                    <!-- Google reCAPTCHA Settings -->
                                    <div v-if="form.captcha_provider === 'recaptcha'" class="field-grid">
                                        <div class="field">
                                            <label class="field__label">reCAPTCHA Site Key</label>
                                            <input v-model="form.captcha_site_key" type="text" class="field__input"
                                                placeholder="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" />
                                        </div>
                                        <div class="field">
                                            <label class="field__label">reCAPTCHA Secret Key</label>
                                            <input v-model="form.captcha_secret_key" type="password"
                                                class="field__input"
                                                placeholder="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" />
                                        </div>
                                    </div>

                                    <!-- Cloudflare Turnstile Settings -->
                                    <div v-if="form.captcha_provider === 'turnstile'" class="field-grid">
                                        <div class="field">
                                            <label class="field__label">Turnstile Site Key</label>
                                            <input v-model="form.turnstile_site_key" type="text" class="field__input"
                                                placeholder="0x4AAAAAAABkMYinukE8nzP" />
                                        </div>
                                        <div class="field">
                                            <label class="field__label">Turnstile Secret Key</label>
                                            <input v-model="form.turnstile_secret_key" type="password"
                                                class="field__input" placeholder="0x4AAAAAAABkMYinuC2iX" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Per-Page CAPTCHA Settings -->
                                <div class="divider"></div>
                                <div style="padding-left:28px">
                                    <h4
                                        style="margin: 20px 0 15px 0; color: #64748b; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Per-Page CAPTCHA Control</h4>
                                    <p style="margin-bottom: 15px; color: #64748b; font-size: 13px;">Select which pages
                                        should
                                        show CAPTCHA verification:</p>

                                    <div class="feature-card" style="margin-bottom: 12px;">
                                        <input v-model="form.captcha_login" type="checkbox" id="captcha_login"
                                            class="toggle-check" />
                                        <div>
                                            <label for="captcha_login" class="feature-card__title">Login Page</label>
                                            <p class="feature-card__desc">Show CAPTCHA on user login form</p>
                                        </div>
                                    </div>

                                    <div class="feature-card" style="margin-bottom: 12px;">
                                        <input v-model="form.captcha_register" type="checkbox" id="captcha_register"
                                            class="toggle-check" />
                                        <div>
                                            <label for="captcha_register" class="feature-card__title">Registration
                                                Page</label>
                                            <p class="feature-card__desc">Show CAPTCHA on user registration form</p>
                                        </div>
                                    </div>

                                    <div class="feature-card" style="margin-bottom: 12px;">
                                        <input v-model="form.captcha_forgot_password" type="checkbox"
                                            id="captcha_forgot_password" class="toggle-check" />
                                        <div>
                                            <label for="captcha_forgot_password" class="feature-card__title">Forgot
                                                Password</label>
                                            <p class="feature-card__desc">Show CAPTCHA on password reset request form
                                            </p>
                                        </div>
                                    </div>

                                    <div class="feature-card" style="margin-bottom: 12px;">
                                        <input v-model="form.captcha_redirect" type="checkbox" id="captcha_redirect"
                                            class="toggle-check" />
                                        <div>
                                            <label for="captcha_redirect" class="feature-card__title">URL Redirect
                                                Pages</label>
                                            <p class="feature-card__desc">Show CAPTCHA on short URL redirect pages</p>
                                        </div>
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

                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field__label">API Rate Limit (requests/hour)</label>
                                        <input v-model="form.api_rate_limit_per_hour" type="number" min="1" max="10000"
                                            class="field__input" />
                                        <p class="field__hint">Maximum API requests per hour per user/IP.</p>
                                    </div>
                                    <div class="field">
                                        <label class="field__label">Token Generation Limit (requests/hour)</label>
                                        <input v-model="form.api_token_rate_limit_per_hour" type="number" min="1"
                                            max="1000" class="field__input" />
                                        <p class="field__hint">Token generation requests per hour per user/IP.</p>
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
