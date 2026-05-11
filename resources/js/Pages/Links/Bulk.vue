<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const raw = ref('');
const results = ref([]);
const loading = ref(false);
const error = ref(null);

// Bulk options
const domainId = ref(null);
const campaignTag = ref('');
const visibility = ref('public');
const password = ref('');
const showAdvanced = ref(false);

const urls = computed(() =>
    raw.value.split('\n').map(u => u.trim()).filter(Boolean)
);

const successCount = computed(() => results.value.filter(r => r.status === 'success').length);
const errorCount = computed(() => results.value.filter(r => r.status === 'error').length);

const submit = async () => {
    if (!urls.value.length) return;
    error.value = null;
    results.value = [];
    loading.value = true;

    const payload = {
        urls: urls.value,
        domain_id: domainId.value,
        campaign_tag: campaignTag.value || null,
        visibility: visibility.value,
        password: visibility.value === 'private' ? password.value : null,
    };

    try {
        const res = await axios.post(route('links.bulk.store'), payload);
        results.value = res.data.results;
    } catch (e) {
        error.value = e.response?.data?.error ?? e.response?.data?.message ?? 'Something went wrong.';
    } finally {
        loading.value = false;
    }
};

const exportCsv = async () => {
    const res = await axios.post(route('links.bulk.export'), { results: results.value }, {
        responseType: 'blob',
    });
    const url = URL.createObjectURL(res.data);
    const a = document.createElement('a');
    a.href = url;
    a.download = `bulk-links-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
};

const copyAll = async () => {
    const links = results.value.filter(r => r.short_url).map(r => r.short_url).join('\n');
    await navigator.clipboard.writeText(links);
};

const exportText = () => {
    const links = results.value.filter(r => r.short_url).map(r => r.short_url).join('\n');
    const blob = new Blob([links], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `bulk-links-${new Date().toISOString().slice(0, 10)}.txt`;
    a.click();
    URL.revokeObjectURL(url);
};

const handleFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => { raw.value = ev.target.result; };
    reader.readAsText(file);
};

const copyUrl = async (url) => {
    await navigator.clipboard.writeText(url);
};
</script>

<template>

    <Head title="Bulk Create Links" />

    <AuthenticatedLayout>
        <div class="bulk-page">
            <div class="page-header">
                <h1 class="page-title">Bulk Create Links</h1>
                <p class="page-subtitle">Shorten multiple URLs at once</p>
            </div>

            <div class="page-content">
                <div class="main-form">
                    <!-- Input Panel -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-number">01</span>
                            <h2 class="section-title">Paste URLs</h2>
                        </div>
                        <div class="form-card">
                            <div class="field">
                                <div class="textarea-wrapper">
                                    <textarea v-model="raw" class="textarea"
                                        placeholder="Paste one URL per line (max 500)&#10;https://example.com/very-long-url-1&#10;https://example.com/very-long-url-2"
                                        rows="12" />
                                </div>
                                <div class="field-footer">
                                    <span class="count-badge">{{ urls.length }} / 500 URLs</span>
                                    <label class="upload-btn">
                                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="17 8 12 3 7 8" />
                                            <line x1="12" y1="3" x2="12" y2="15" />
                                        </svg>
                                        Upload CSV
                                        <input type="file" accept=".csv,.txt" class="sr-only" @change="handleFile" />
                                    </label>
                                </div>
                            </div>
                            <p v-if="error" class="error">{{ error }}</p>
                        </div>
                    </div>

                    <!-- Bulk Options -->
                    <div class="form-section">
                        <button type="button" class="advanced-toggle" @click="showAdvanced = !showAdvanced">
                            <div class="toggle-header">
                                <svg class="toggle-chevron" :class="{ 'toggle-chevron--open': showAdvanced }"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                                <span class="toggle-text">Bulk Options</span>
                            </div>
                            <span class="toggle-hint">Domain, Campaign, Visibility</span>
                        </button>

                        <div v-if="showAdvanced" class="advanced-fields">
                            <div class="form-card">
                                <div class="field-row">
                                    <div class="field" v-if="$page.props.domains">
                                        <label class="label">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <circle cx="12" cy="12" r="10" />
                                                <line x1="2" y1="12" x2="22" y2="12" />
                                                <path
                                                    d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                            </svg>
                                            Domain
                                        </label>
                                        <select v-model="domainId" class="select">
                                            <option :value="null">Default Domain</option>
                                            <option v-for="domain in $page.props.domains" :key="domain.id"
                                                :value="domain.id">
                                                {{ domain.domain }} {{ domain.is_default ? '(Default)' : '' }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="field">
                                        <label class="label">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path
                                                    d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                                                <line x1="7" y1="7" x2="7.01" y2="7" />
                                            </svg>
                                            Campaign Tag
                                        </label>
                                        <input v-model="campaignTag" type="text" placeholder="summer-promo"
                                            class="input" maxlength="100" />
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        Visibility
                                    </label>
                                    <div class="visibility-options">
                                        <label class="visibility-option"
                                            :class="{ 'visibility-option--active': visibility === 'public' }">
                                            <input type="radio" v-model="visibility" value="public" class="sr-only" />
                                            <div class="option-content">
                                                <span class="option-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.5">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <line x1="2" y1="12" x2="22" y2="12" />
                                                        <path
                                                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                                    </svg>
                                                </span>
                                                <div class="option-text">
                                                    <span class="option-title">Public</span>
                                                    <span class="option-desc">Anyone with the link</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="visibility-option"
                                            :class="{ 'visibility-option--active': visibility === 'private' }">
                                            <input type="radio" v-model="visibility" value="private" class="sr-only" />
                                            <div class="option-content">
                                                <span class="option-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.5">
                                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                                    </svg>
                                                </span>
                                                <div class="option-text">
                                                    <span class="option-title">Private</span>
                                                    <span class="option-desc">Password required</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="visibility === 'private'" class="field field--animate">
                                    <label class="label">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        Password <span class="required">*</span>
                                    </label>
                                    <input v-model="password" type="password"
                                        placeholder="Enter password (min 6 characters)" class="input" minlength="6" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button @click="submit" class="btn btn--primary"
                            :disabled="loading || !urls.length || urls.length > 500">
                            <svg v-if="loading" class="btn-icon spin" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                            </svg>
                            <svg v-else class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            {{ loading ? 'Processing...' : 'Shorten All' }}
                        </button>
                    </div>

                    <!-- Results Panel -->
                    <div v-if="results.length" class="form-section">
                        <div class="section-header">
                            <span class="section-number">02</span>
                            <h2 class="section-title">
                                Results
                                <span class="badge badge--green">{{ successCount }} ok</span>
                                <span v-if="errorCount" class="badge badge--red">{{ errorCount }} failed</span>
                            </h2>
                            <div class="results-actions">
                                <button @click="copyAll" class="btn btn--secondary">
                                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.5">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                    </svg>
                                    Copy All
                                </button>
                                <button @click="exportText" class="btn btn--secondary">
                                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                    </svg>
                                    Text
                                </button>
                                <button @click="exportCsv" class="btn btn--secondary">
                                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.5">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    CSV
                                </button>
                            </div>
                        </div>
                        <div class="form-card">
                            <div class="results-table">
                                <div class="results-head">
                                    <span>Original URL</span>
                                    <span>Short URL</span>
                                    <span></span>
                                    <span>Status</span>
                                </div>
                                <div v-for="(row, i) in results" :key="i" class="results-row"
                                    :class="row.status === 'error' ? 'results-row--error' : ''">
                                    <span class="cell-url cell-url--original" :title="row.original_url">{{
                                        row.original_url }}</span>
                                    <a v-if="row.short_url" :href="row.short_url" target="_blank"
                                        class="cell-url cell-url--short">{{ row.short_url }}</a>
                                    <span v-else class="cell-url cell-url--err">{{ row.error }}</span>
                                    <button v-if="row.short_url" @click="copyUrl(row.short_url)" class="copy-btn"
                                        title="Copy">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                        </svg>
                                    </button>
                                    <span v-else></span>
                                    <span :class="row.status === 'success' ? 'status-ok' : 'status-err'">
                                        <svg v-if="row.status === 'success'" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-header">
                            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <h3 class="sidebar-title">Quick Tips</h3>
                        </div>
                        <ul class="sidebar-list">
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Paste one URL per line</span>
                            </li>
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Maximum 500 URLs at once</span>
                            </li>
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Upload CSV or TXT file</span>
                            </li>
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Export results to CSV</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Uses global theme vars: --font-display (Oswald), --font-body (Crimson Pro), --font-sidebar (DM Sans) */
/* Colors: --red, --red-dark, --ink, --ink-soft, --muted, --border, --bg, --surface, --surface-2 */

.bulk-page {
    max-width: 1100px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.page-title {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0 0 4px;
}

.page-subtitle {
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--muted);
    margin: 0;
}

.page-content {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 28px;
    align-items: start;
}

.main-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-header {
    display: flex;
    align-items: baseline;
    gap: 12px;
    flex-wrap: wrap;
}

.section-number {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 2px;
    color: var(--red);
    text-transform: uppercase;
}

.section-title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink-soft);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.results-actions {
    display: flex;
    gap: 8px;
    margin-left: auto;
}

.form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.textarea-wrapper {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    transition: var(--transition);
}

.textarea-wrapper:focus-within {
    border-color: var(--ink);
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.07);
}

.textarea {
    width: 100%;
    padding: 14px 16px;
    border: none;
    outline: none;
    font-family: var(--font-sidebar);
    font-size: 13px;
    color: var(--ink);
    background: var(--surface);
    line-height: 1.6;
    resize: vertical;
    min-height: 180px;
    box-sizing: border-box;
}

.textarea::placeholder {
    color: var(--muted);
}

.field-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid var(--border);
}

.count-badge {
    font-family: var(--font-sidebar);
    font-size: 12px;
    color: var(--muted);
}

.upload-btn {
    font-family: var(--font-sidebar);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3px;
    color: var(--ink-soft);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 7px 14px;
    cursor: pointer;
    transition: var(--transition);
    background: var(--surface);
    display: flex;
    align-items: center;
    gap: 6px;
}

.upload-btn:hover {
    border-color: var(--ink-soft);
    color: var(--ink);
}

.upload-btn .btn-icon {
    width: 12px;
    height: 12px;
}

.error {
    font-family: var(--font-sidebar);
    font-size: 12px;
    color: var(--red);
    padding: 8px 0 0;
}

/* Advanced Toggle */
.advanced-toggle {
    width: 100%;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 16px;
    cursor: pointer;
    transition: var(--transition);
    text-align: left;
}

.advanced-toggle:hover {
    border-color: var(--ink-soft);
}

.toggle-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toggle-chevron {
    width: 16px;
    height: 16px;
    color: var(--muted);
    transition: transform 0.2s ease;
}

.toggle-chevron--open {
    transform: rotate(90deg);
}

.toggle-text {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--ink);
}

.toggle-hint {
    display: block;
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--muted);
    margin-top: 4px;
    padding-left: 26px;
}

.advanced-fields {
    margin-top: 12px;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Form Elements */
.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field--animate {
    animation: slideDown 0.2s ease;
}

.label {
    font-family: var(--font-sidebar);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 6px;
}

.label .icon {
    width: 12px;
    height: 12px;
    color: var(--muted);
}

.required {
    color: var(--red);
}

.input,
.select {
    font-family: var(--font-sidebar);
    font-size: 13px;
    color: var(--ink);
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 10px 12px;
    outline: none;
    transition: var(--transition);
}

.input:focus,
.select:focus {
    border-color: var(--ink);
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.07);
}

.input::placeholder {
    color: var(--muted);
}

.select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 32px;
}

/* Visibility Options */
.visibility-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.visibility-option {
    cursor: pointer;
}

.option-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
}

.visibility-option:hover .option-content {
    border-color: var(--ink-soft);
}

.visibility-option--active .option-content {
    border-color: var(--ink);
    background: var(--surface-2);
}

.option-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    border-radius: var(--radius);
    flex-shrink: 0;
}

.option-icon svg {
    width: 16px;
    height: 16px;
    color: var(--muted);
}

.visibility-option--active .option-icon {
    background: var(--ink);
}

.visibility-option--active .option-icon svg {
    color: #fff;
}

.option-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.option-title {
    font-family: var(--font-sidebar);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
}

.option-desc {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
}

.form-actions {
    display: flex;
    justify-content: flex-start;
    gap: 12px;
    padding-top: 8px;
}

.btn {
    font-family: var(--font-sidebar);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    padding: 10px 22px;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.btn-icon {
    width: 13px;
    height: 13px;
    flex-shrink: 0;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.spin {
    animation: spin 0.8s linear infinite;
}

.btn--primary {
    background: var(--red);
    color: #fff;
    border: none;
}

.btn--primary:hover {
    background: var(--red-dark);
}

.btn--primary:disabled {
    background: var(--muted);
    cursor: not-allowed;
}

.btn--secondary {
    background: var(--surface);
    color: var(--ink-soft);
    border: 1px solid var(--border);
}

.btn--secondary:hover {
    border-color: var(--ink-soft);
    color: var(--ink);
}

/* Badges */
.badge {
    font-family: var(--font-sidebar);
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: var(--radius);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge--green {
    background: #dcfce7;
    color: #16a34a;
}

.badge--red {
    background: #fee2e2;
    color: #dc2626;
}

/* Results table */
.results-table {
    max-height: 400px;
    overflow-y: auto;
}

.results-head {
    display: grid;
    grid-template-columns: 1fr 180px 50px 50px;
    padding: 10px 16px;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
}

.results-row {
    display: grid;
    grid-template-columns: 1fr 180px 50px 50px;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    transition: background 150ms;
}

.results-row:hover {
    background: var(--surface-2);
}

.results-row--error {
    background: #fef2f2;
}

.results-row--error:hover {
    background: #fef2f2;
}

.cell-url {
    font-family: var(--font-sidebar);
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cell-url--original {
    color: var(--ink-soft);
}

.cell-url--short {
    color: var(--red);
    text-decoration: none;
}

.cell-url--short:hover {
    text-decoration: underline;
}

.cell-url--err {
    color: var(--red);
    font-size: 11px;
}

.copy-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    color: var(--muted);
    transition: var(--transition);
}

.copy-btn svg {
    width: 12px;
    height: 12px;
}

.copy-btn:hover {
    background: var(--ink);
    border-color: var(--ink);
    color: #fff;
}

.status-ok {
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-ok svg {
    width: 14px;
    height: 14px;
    color: #16a34a;
}

.status-err {
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-err svg {
    width: 14px;
    height: 14px;
    color: var(--red);
}

/* Sidebar */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 66px;
}

.sidebar-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px;
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.sidebar-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    color: var(--muted);
}

.sidebar-title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
}

.sidebar-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-list li {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink-soft);
    line-height: 1.5;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.tip-icon {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
    margin-top: 2px;
    color: var(--red);
}

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

@media (max-width: 900px) {
    .page-content {
        grid-template-columns: 1fr;
    }

    .sidebar {
        position: static;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .sidebar-card {
        flex: 1;
        min-width: 240px;
    }
}

@media (max-width: 640px) {
    .section-header {
        flex-direction: column;
        gap: 8px;
    }

    .btn--secondary {
        margin-left: 0;
    }

    .results-head {
        grid-template-columns: 1fr 60px 40px;
        font-size: 9px;
        padding: 8px 12px;
    }

    .results-row {
        grid-template-columns: 1fr 60px 40px;
        padding: 8px 12px;
    }

    .cell-url--original {
        max-width: 100px;
    }

    .cell-url--short {
        max-width: 60px;
    }
}
</style>