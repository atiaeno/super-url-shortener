<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    link: { type: Object, required: true },
    analytics: {
        type: Object,
        default: () => ({
            total_clicks: 0,
            period: 'all',
            clicks_by_country: [],
            clicks_by_device: [],
            clicks_by_browser: [],
            clicks_by_referrer: [],
            clicks_over_time: [],
        }),
    },
});

const copied = ref(false);
const showEmbed = ref(false);
const embedCopied = ref(false);
const loading = ref({
    qrSvg: false,
    qrPng: false,
    embed: false,
});

const shortUrl = computed(() => props.link.short_url ?? `${window.location.origin}/${props.link.short_code}`);

const embedCode = computed(() => `<a href="${shortUrl.value}" target="_blank" rel="noopener">${shortUrl.value}</a>`);

const copyUrl = async () => {
    await navigator.clipboard.writeText(shortUrl.value);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const copyEmbed = async () => {
    await navigator.clipboard.writeText(embedCode.value);
    embedCopied.value = true;
    setTimeout(() => { embedCopied.value = false; }, 2000);
};

const deleteLink = () => {
    if (!confirm('Delete this link permanently?')) return;
    router.delete(route('links.destroy', props.link.id));
};

const setPeriod = (p) => {
    router.get(route('links.show', props.link.id), { period: p }, { preserveState: true, preserveScroll: true });
};

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

const periods = [
    { key: 'today', label: 'Today' },
    { key: 'week', label: '7 Days' },
    { key: 'month', label: '30 Days' },
    { key: 'all', label: 'All Time' },
];

const activePeriod = computed(() => props.analytics.period ?? 'all');

const topCountries = computed(() => (props.analytics.clicks_by_country || []).slice(0, 5));
const topReferrers = computed(() => (props.analytics.clicks_by_referrer || []).slice(0, 5));

const maxClicks = computed(() => {
    const data = props.analytics.clicks_over_time || [];
    return Math.max(1, ...data.map(d => d.clicks || d.total || 0));
});

const hasTimeData = computed(() => {
    const data = props.analytics.clicks_over_time || [];
    return data.some(d => (d.clicks || d.total || 0) > 0);
});

const qrSvgUrl = computed(() => route('links.qr', { link: props.link.id, format: 'svg' }));
const qrPngUrl = computed(() => route('links.qr', { link: props.link.id, format: 'png' }));

const downloadQr = async (format) => {
    const key = format === 'svg' ? 'qrSvg' : 'qrPng';
    loading.value[key] = true;
    try {
        const response = await fetch(route('links.qr', { link: props.link.id, format }));
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `qr-${props.link.short_code}.${format}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    } catch (e) {
        console.error('Download failed:', e);
    } finally {
        loading.value[key] = false;
    }
};

const toggleEmbed = async () => {
    loading.value.embed = true;
    showEmbed.value = !showEmbed.value;
    loading.value.embed = false;
};
</script>

<template>

    <Head :title="`${link.short_code} — Analytics`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="header-flex">
                <span>/{{ link.short_code }}</span>
                <button @click="copyUrl" class="copy-btn-sm">{{ copied ? 'Copied!' : 'Copy' }}</button>
            </div>
        </template>

        <div class="page-content">

            <!-- Link Info -->
            <div class="link-info-card">
                <div class="link-info-main">
                    <a :href="shortUrl" target="_blank" class="link-url">{{ shortUrl }}</a>
                    <a :href="link.destination_url" target="_blank" class="link-dest">{{ link.destination_url }}</a>
                </div>
                <div class="link-info-meta">
                    <span class="status-badge" :class="link.is_active ? 'active' : 'inactive'">
                        {{ link.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="meta-date">Created {{ formatDate(link.created_at) }}</span>
                    <Link :href="route('links.edit', link.id)" class="action-link">Edit</Link>
                    <button @click="deleteLink" class="action-link danger">Delete</button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-box">
                    <span class="stat-value">{{ (analytics.total_clicks ?? 0).toLocaleString() }}</span>
                    <span class="stat-label">Total Clicks</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">{{ topCountries.length }}</span>
                    <span class="stat-label">Countries</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">{{ topReferrers.length }}</span>
                    <span class="stat-label">Referrers</span>
                </div>
            </div>

            <!-- Period Filter -->
            <div class="period-bar">
                <span class="period-label">Time Period:</span>
                <button v-for="p in periods" :key="p.key" @click="setPeriod(p.key)" class="period-btn"
                    :class="activePeriod === p.key ? 'active' : ''">{{ p.label }}</button>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- Clicks Over Time -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Clicks Over Time</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!hasTimeData" class="no-data">No clicks yet</div>
                        <div v-else class="bar-chart">
                            <div class="bar-chart__bars">
                                <div v-for="point in analytics.clicks_over_time" :key="point.date"
                                    class="bar-chart__bar-wrapper">
                                    <div class="bar-chart__bar"
                                        :style="{ height: ((point.clicks || point.total || 0) / maxClicks * 100) + '%' }">
                                    </div>
                                    <span class="bar-chart__label">{{ point.date?.slice(5) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Devices -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Devices</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!analytics.clicks_by_device?.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in analytics.clicks_by_device" :key="row.device_type" class="data-row">
                                <span class="data-label">{{ row.device_type || 'Unknown' }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Countries -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Top Countries</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!topCountries.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in topCountries" :key="row.country_code" class="data-row">
                                <span class="data-label">{{ row.country_emoji }} {{ row.country_name }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrers -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Traffic Sources</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!topReferrers.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in topReferrers" :key="row.referrer_domain" class="data-row">
                                <span class="data-label">{{ row.referrer_domain || 'Direct' }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="actions-row">
                <button @click="downloadQr('svg')" class="action-btn" :disabled="loading.qrSvg">
                    <span v-if="loading.qrSvg" class="spinner"></span>
                    <span v-else>Download QR (SVG)</span>
                </button>
                <button @click="downloadQr('png')" class="action-btn" :disabled="loading.qrPng">
                    <span v-if="loading.qrPng" class="spinner"></span>
                    <span v-else>Download QR (PNG)</span>
                </button>
                <button @click="toggleEmbed" class="action-btn" :disabled="loading.embed">
                    <span v-if="loading.embed" class="spinner"></span>
                    <span v-else>{{ showEmbed ? 'Hide' : 'Embed Code' }}</span>
                </button>
            </div>

            <div v-if="showEmbed" class="embed-box">
                <code class="embed-code">{{ embedCode }}</code>
                <button @click="copyEmbed" class="copy-btn-sm">{{ embedCopied ? 'Copied!' : 'Copy' }}</button>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');

:root {
    --font-display: 'Oswald', sans-serif;
    --red: #e74c3c;
    --ink: #1a1a1a;
    --muted: #888;
    --border: #e5e5e5;
    --surface: #fff;
    --radius: 4px;
}

.header-flex {
    display: flex;
    align-items: center;
    gap: 12px;
}

.copy-btn-sm {
    font-family: var(--font-display);
    font-size: 10px;
    padding: 4px 10px;
    background: var(--red);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.page-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 24px;
}

/* Link Info Card */
.link-info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 20px;
}

.link-url {
    font-family: var(--font-display);
    font-size: 20px;
    font-weight: 600;
    color: var(--red);
    text-decoration: none;
    display: block;
    margin-bottom: 8px;
}

.link-url:hover {
    text-decoration: underline;
}

.link-dest {
    font-size: 13px;
    color: var(--muted);
    text-decoration: none;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 600px;
    margin-bottom: 12px;
}

.link-dest:hover {
    color: var(--red);
}

.link-info-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.status-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 4px 10px;
    border-radius: var(--radius);
}

.status-badge.active {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #059669;
}

.status-badge.inactive {
    background: #fef2f2;
    color: var(--red);
    border: 1px solid var(--red);
}

.meta-date {
    font-size: 12px;
    color: var(--muted);
}

.action-link {
    font-family: var(--font-display);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
}

.action-link:hover {
    color: var(--ink);
}

.action-link.danger:hover {
    color: var(--red);
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.stat-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    text-align: center;
}

.stat-value {
    font-family: var(--font-display);
    font-size: 32px;
    font-weight: 600;
    color: var(--ink);
    display: block;
    margin-bottom: 4px;
}

.stat-label {
    font-family: var(--font-display);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
}

/* Period Bar */
.period-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.period-label {
    font-family: var(--font-display);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
}

.period-btn {
    font-family: var(--font-display);
    font-size: 11px;
    padding: 6px 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--muted);
    cursor: pointer;
    border-radius: var(--radius);
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.2s;
}

.period-btn:hover {
    border-color: var(--ink);
    color: var(--ink);
}

.period-btn.active {
    background: var(--ink);
    border-color: var(--ink);
    color: #fff;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.chart-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.chart-header h3 {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--ink);
    margin: 0;
}

.chart-body {
    padding: 20px;
    min-height: 180px;
}

.no-data {
    text-align: center;
    color: var(--muted);
    font-size: 14px;
    padding: 40px 0;
}

/* Bar Chart */
.bar-chart__bars {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    height: 140px;
}

.bar-chart__bar-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}

.bar-chart__bar {
    width: 100%;
    background: var(--ink);
    min-height: 4px;
    border-radius: 2px 2px 0 0;
    margin-top: auto;
    transition: background 0.2s;
}

.bar-chart__bar:hover {
    background: var(--red);
}

.bar-chart__label {
    font-size: 9px;
    color: var(--muted);
    margin-top: 6px;
    font-family: var(--font-display);
}

/* Data List */
.data-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.data-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.data-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.data-label {
    font-size: 13px;
    color: var(--ink);
}

.data-value {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--red);
}

/* Actions */
.actions-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.action-btn {
    font-family: var(--font-display);
    font-size: 11px;
    padding: 10px 18px;
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--ink);
    text-decoration: none;
    border-radius: var(--radius);
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: var(--ink);
    color: #fff;
    border-color: var(--ink);
}

.action-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid var(--border);
    border-top-color: var(--ink);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Embed Box */
.embed-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 16px;
}

.embed-code {
    flex: 1;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: var(--muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: 1fr;
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }

    .page-content {
        padding: 16px;
    }
}
</style>