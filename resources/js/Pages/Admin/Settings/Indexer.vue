// © Atia Hegazy — atiaeno.com
<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Object,
    stats: Object,
    recentLogs: Array,
});

const activeTab = ref('settings');

const form = useForm({
    enabled: props.settings.enabled || false,
    links_per_batch: props.settings.links_per_batch || 10,
    interval_minutes: props.settings.interval_minutes || 5,
    google_service_account_json: props.settings.google_service_account_json || '',
    indexnow_enabled: props.settings.indexnow_enabled || false,
    xml_ping_enabled: props.settings.xml_ping_enabled || false,
    ping_services: props.settings.ping_services || ['google', 'bing'],
});

const isSaving = ref(false);

const saveSettings = () => {
    isSaving.value = true;
    form.post('/admin/settings/indexer', {
        onFinish: () => {
            isSaving.value = false;
        },
    });
};

const runIndexer = () => {
    window.location.href = '/admin/settings/indexer/run';
};

const clearQueue = () => {
    if (confirm('Clear old completed/failed entries from queue?')) {
        window.location.href = '/admin/settings/indexer/clear';
    }
};

const pingServicesOptions = [
    { value: 'google', label: 'Google' },
    { value: 'bing', label: 'Bing' },
];

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status) => {
    const colors = {
        success: 'text-green-600',
        failed: 'text-red-600',
        error: 'text-red-600',
        deleted: 'text-yellow-600',
    };
    return colors[status] || 'text-gray-600';
};
</script>

<template>
    <Head title="SEO Indexer Settings" />
    <AdminLayout>
        <template #header-icon>
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </template>
        <template #header>SEO Indexer</template>

        <div class="indexer-page">
            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Search Engine Optimization</span>
                    <h1 class="page-header__title">URL Indexer Settings</h1>
                    <p class="page-header__sub">Configure how your public links are submitted to search engines.</p>
                </div>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Tabs -->
            <div class="tabs">
                <button
                    @click="activeTab = 'settings'"
                    class="tab"
                    :class="{ 'tab--active': activeTab === 'settings' }"
                >
                    Settings
                </button>
                <button
                    @click="activeTab = 'queue'"
                    class="tab"
                    :class="{ 'tab--active': activeTab === 'queue' }"
                >
                    Queue & Logs
                </button>
            </div>

            <!-- Settings Tab -->
            <div v-if="activeTab === 'settings'" class="settings-section">
                <form @submit.prevent="saveSettings" class="settings-form">
                    <!-- Google Indexer -->
                    <div class="settings-card">
                        <div class="settings-card__header">
                            <div class="settings-card__title-row">
                                <h3 class="settings-card__title">Google Indexing API</h3>
                                <label class="toggle">
                                    <input type="checkbox" v-model="form.enabled">
                                    <span class="toggle__slider"></span>
                                </label>
                            </div>
                            <p class="settings-card__desc">Submit URLs to Google for indexing</p>
                        </div>

                        <div class="settings-card__body" v-if="form.enabled">
                            <div class="form-group">
                                <label class="form-label">Service Account JSON</label>
                                <textarea
                                    v-model="form.google_service_account_json"
                                    class="form-textarea"
                                    rows="6"
                                    placeholder='{"type": "service_account", ...}'
                                ></textarea>
                                <p class="form-hint">Paste your Google Service Account JSON credentials</p>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Links Per Batch</label>
                                    <input
                                        type="number"
                                        v-model="form.links_per_batch"
                                        class="form-input"
                                        min="1"
                                        max="100"
                                    >
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Interval (minutes)</label>
                                    <input
                                        type="number"
                                        v-model="form.interval_minutes"
                                        class="form-input"
                                        min="1"
                                        max="1440"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- IndexNow -->
                    <div class="settings-card">
                        <div class="settings-card__header">
                            <div class="settings-card__title-row">
                                <h3 class="settings-card__title">IndexNow</h3>
                                <label class="toggle">
                                    <input type="checkbox" v-model="form.indexnow_enabled">
                                    <span class="toggle__slider"></span>
                                </label>
                            </div>
                            <p class="settings-card__desc">Submit URLs to Bing and Yandex via IndexNow protocol</p>
                        </div>
                    </div>

                    <!-- XML Ping -->
                    <div class="settings-card">
                        <div class="settings-card__header">
                            <div class="settings-card__title-row">
                                <h3 class="settings-card__title">XML Sitemap Ping</h3>
                                <label class="toggle">
                                    <input type="checkbox" v-model="form.xml_ping_enabled">
                                    <span class="toggle__slider"></span>
                                </label>
                            </div>
                            <p class="settings-card__desc">Ping search engines when sitemap is updated</p>
                        </div>

                        <div class="settings-card__body" v-if="form.xml_ping_enabled">
                            <div class="form-group">
                                <label class="form-label">Ping Services</label>
                                <div class="checkbox-group">
                                    <label
                                        v-for="option in pingServicesOptions"
                                        :key="option.value"
                                        class="checkbox-label"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="option.value"
                                            v-model="form.ping_services"
                                        >
                                        <span>{{ option.label }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-primary" :disabled="isSaving">
                            {{ isSaving ? 'Saving...' : 'Save Settings' }}
                        </button>
                        <button type="button" @click="runIndexer" class="btn-secondary">
                            Run Indexer Now
                        </button>
                    </div>
                </form>
            </div>

            <!-- Queue & Logs Tab -->
            <div v-if="activeTab === 'queue'" class="queue-section">
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card__value">{{ stats.pending }}</div>
                        <div class="stat-card__label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card__value">{{ stats.processing }}</div>
                        <div class="stat-card__label">Processing</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card__value">{{ stats.completed }}</div>
                        <div class="stat-card__label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card__value">{{ stats.failed }}</div>
                        <div class="stat-card__label">Failed</div>
                    </div>
                </div>

                <!-- Clear Queue -->
                <div class="queue-actions">
                    <button @click="clearQueue" class="btn-ghost">Clear Old Entries</button>
                </div>

                <!-- Logs Table -->
                <div class="logs-card">
                    <h3 class="logs-title">Recent Activity</h3>
                    <div class="table-wrapper">
                        <table class="logs-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Link</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in recentLogs" :key="log.id">
                                    <td>{{ formatDate(log.created_at) }}</td>
                                    <td>
                                        <code class="link-code">{{ log.link?.short_code }}</code>
                                    </td>
                                    <td>
                                        <span class="service-badge">{{ log.service }}</span>
                                    </td>
                                    <td>
                                        <span :class="['status-text', getStatusColor(log.response_status)]">
                                            {{ log.response_status }}
                                        </span>
                                    </td>
                                    <td class="message-cell">{{ log.response_message }}</td>
                                </tr>
                            </tbody>
                        </table>
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
    --primary: #e74c3c;
    --primary-dark: #c0392b;
    --ink: #000000;
    --ink-soft: #333333;
    --muted: #666666;
    --border: #e8e5e0;
    --surface: #fff;
    --surface-2: #f5f3ef;
    --radius: 4px;
    --transition: all 0.2s ease;
}
</style>

<style scoped>
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
    font-weight: 500;
    text-transform: uppercase;
    color: var(--primary);
    display: block;
    margin-bottom: 8px;
}

.page-header__title {
    font-family: var(--font-display);
    font-size: 24px;
    font-weight: 500;
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

.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--primary) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

.tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border);
}

.tab {
    padding: 12px 20px;
    background: none;
    border: none;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    cursor: pointer;
    position: relative;
    transition: color 0.2s;
}

.tab:hover {
    color: var(--ink);
}

.tab--active {
    color: var(--primary);
}

.tab--active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary);
}

.settings-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    margin-bottom: 16px;
}

.settings-card__header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
}

.settings-card__title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}

.settings-card__title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 500;
    color: var(--ink);
    margin: 0;
}

.settings-card__desc {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--muted);
    margin: 0;
}

.settings-card__body {
    padding: 20px;
    background: var(--surface-2);
}

.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 6px;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink);
    background: var(--surface);
    transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
}

.form-textarea {
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 12px;
}

.form-hint {
    font-family: var(--font-body);
    font-size: 11px;
    color: var(--muted);
    margin-top: 4px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle__slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 24px;
}

.toggle__slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle input:checked + .toggle__slider {
    background-color: var(--primary);
}

.toggle input:checked + .toggle__slider:before {
    transform: translateX(20px);
}

.checkbox-group {
    display: flex;
    gap: 16px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink);
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-primary {
    padding: 12px 24px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    padding: 12px 24px;
    background: transparent;
    color: var(--ink);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: var(--surface-2);
    border-color: var(--ink);
}

.btn-ghost {
    padding: 10px 16px;
    background: transparent;
    color: var(--muted);
    border: none;
    font-family: var(--font-body);
    font-size: 13px;
    cursor: pointer;
    text-decoration: underline;
}

.btn-ghost:hover {
    color: var(--ink);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    text-align: center;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 500;
    color: var(--ink);
    margin-bottom: 4px;
}

.stat-card__label {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--muted);
}

.queue-actions {
    margin-bottom: 24px;
}

.logs-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.logs-title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 500;
    color: var(--ink);
    padding: 16px 20px;
    margin: 0;
    border-bottom: 1px solid var(--border);
}

.table-wrapper {
    overflow-x: auto;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
}

.logs-table th {
    background: var(--surface-2);
    padding: 12px 16px;
    text-align: left;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
}

.logs-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink-soft);
}

.logs-table tr:hover td {
    background: var(--surface-2);
}

.link-code {
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 11px;
    color: var(--primary);
    background: var(--surface-2);
    padding: 2px 6px;
    border-radius: 2px;
}

.service-badge {
    display: inline-block;
    padding: 2px 8px;
    background: var(--surface-2);
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-text {
    font-weight: 600;
}

.text-green-600 {
    color: #16a34a;
}

.text-red-600 {
    color: #dc2626;
}

.text-yellow-600 {
    color: #ca8a04;
}

.text-gray-600 {
    color: #6b7280;
}

.message-cell {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
