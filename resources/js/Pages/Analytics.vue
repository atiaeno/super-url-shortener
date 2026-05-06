<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Object, required: true },
    devices: { type: Array, default: () => [] },
    os: { type: Array, default: () => [] },
    browsers: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    referrers: { type: Array, default: () => [] },
    clicks_over_time: { type: Array, default: () => [] },
});

const maxClicks = computed(() => {
    const data = props.clicks_over_time || [];
    return Math.max(1, ...data.map(d => d.clicks || 0));
});

const hasTimeData = computed(() => {
    const data = props.clicks_over_time || [];
    return data.some(d => (d.clicks || 0) > 0);
});

const countryName = (code) => {
    const names = {
        US: 'United States', GB: 'United Kingdom', DE: 'Germany', FR: 'France', CA: 'Canada',
        AU: 'Australia', JP: 'Japan', BR: 'Brazil', IN: 'India', MX: 'Mexico', ES: 'Spain',
        IT: 'Italy', NL: 'Netherlands', RU: 'Russia', CN: 'China', KR: 'South Korea',
        SG: 'Singapore', AE: 'UAE', SA: 'Saudi Arabia', EG: 'Egypt', ZA: 'South Africa',
        NG: 'Nigeria', KE: 'Kenya', AR: 'Argentina', CO: 'Colombia', CL: 'Chile',
        PE: 'Peru', VE: 'Venezuela', PL: 'Poland', SE: 'Sweden', NO: 'Norway',
        DK: 'Denmark', FI: 'Finland', CH: 'Switzerland', AT: 'Austria', BE: 'Belgium',
        IE: 'Ireland', PT: 'Portugal', CZ: 'Czech Republic', RO: 'Romania', HU: 'Hungary',
    };
    return names[code?.toUpperCase()] || code || 'Unknown';
};

const capitalize = (str) => {
    if (!str) return 'Unknown';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const deviceIcon = (type) => {
    const icons = { desktop: '💻', mobile: '📱', tablet: '📱', bot: '🤖' };
    return icons[type?.toLowerCase()] || '📱';
};

const browserIcon = (name) => {
    const b = name?.toLowerCase();
    if (b?.includes('chrome')) return '🌐';
    if (b?.includes('firefox')) return '🦊';
    if (b?.includes('safari')) return '🧭';
    if (b?.includes('edge')) return '🔷';
    if (b?.includes('opera')) return '🔴';
    return '🌐';
};

const icons = {
    chart: '📊',
    devices: '💻',
    os: '💻',
    browser: '🌐',
    countries: '🌍',
    referrers: '🔗',
};

const statCards = computed(() => [
    { label: 'Today', value: props.stats.clicks_today || 0 },
    { label: 'This Week', value: props.stats.clicks_week || 0 },
    { label: 'This Month', value: props.stats.clicks_month || 0 },
    { label: 'This Year', value: props.stats.clicks_year || 0 },
]);
</script>

<template>

    <Head title="Analytics" />

    <AuthenticatedLayout>
        <template #header>📊 Analytics</template>

        <div class="page-content">

            <!-- Stats Row -->
            <div class="stats-row">
                <div v-for="stat in statCards" :key="stat.label" class="stat-box">
                    <span class="stat-value">{{ stat.value.toLocaleString() }}</span>
                    <span class="stat-label">{{ stat.label }}</span>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">

                <!-- Clicks Over Time -->
                <div class="chart-card chart-card--full">
                    <div class="chart-header">
                        <span class="header-icon">{{ icons.chart }}</span>
                        <h3>Clicks Over Time</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!hasTimeData" class="no-data">No clicks yet</div>
                        <div v-else class="bar-chart">
                            <div class="bar-chart__bars">
                                <div v-for="point in clicks_over_time" :key="point.date" class="bar-chart__bar-wrapper">
                                    <div class="bar-chart__bar"
                                        :style="{ height: ((point.clicks || 0) / maxClicks * 100) + '%' }">
                                        <span class="bar-tooltip">{{ point.clicks }} clicks</span>
                                    </div>
                                    <span class="bar-chart__label">{{ point.date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Devices -->
                <div class="chart-card">
                    <div class="chart-header">
                        <span class="header-icon">{{ icons.devices }}</span>
                        <h3>Devices</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!devices?.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in devices" :key="row.device_type" class="data-row">
                                <span class="data-label">{{ deviceIcon(row.device_type) }} {{
                                    capitalize(row.device_type)
                                    }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OS -->
                <div class="chart-card">
                    <div class="chart-header">
                        <span class="header-icon">{{ icons.os }}</span>
                        <h3>Operating Systems</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!os?.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in os" :key="row.os" class="data-row">
                                <span class="data-label">{{ capitalize(row.os) }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Browsers -->
                <div class="chart-card">
                    <div class="chart-header">
                        <span class="header-icon">{{ icons.browser }}</span>
                        <h3>Browsers</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!browsers?.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in browsers" :key="row.browser" class="data-row">
                                <span class="data-label">{{ browserIcon(row.browser) }} {{ capitalize(row.browser)
                                }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Countries -->
                <div class="chart-card">
                    <div class="chart-header">
                        <span class="header-icon">{{ icons.countries }}</span>
                        <h3>Top Countries</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!countries?.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in countries" :key="row.country_code" class="data-row">
                                <span class="data-label"><span
                                        :class="['fi', 'fi-' + row.country_code?.toLowerCase()]"></span>
                                    {{ countryName(row.country_code) }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrers -->
                <div class="chart-card">
                    <div class="chart-header">
                        <span class="header-icon">{{ icons.referrers }}</span>
                        <h3>Traffic Sources</h3>
                    </div>
                    <div class="chart-body">
                        <div v-if="!referrers?.length" class="no-data">No data</div>
                        <div v-else class="data-list">
                            <div v-for="row in referrers" :key="row.referrer_domain" class="data-row">
                                <span class="data-label">{{ row.referrer_domain ? capitalize(row.referrer_domain) :
                                    'Direct'
                                }}</span>
                                <span class="data-value">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.3.2/css/flag-icons.min.css');

:root {
    --font-display: 'Oswald', sans-serif;
    --red: #e74c3c;
    --ink: #000000;
    --muted: #333333;
    --border: #e5e5e5;
    --surface: #fff;
    --radius: 4px;
}

.page-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 24px;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
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
    font-size: 28px;
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

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.chart-card--full {
    grid-column: 1 / -1;
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
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-icon {
    display: flex;
    align-items: center;
    color: var(--red);
}

.device-icon {
    display: inline-flex;
    align-items: center;
    margin-right: 6px;
    color: var(--ink);
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
    gap: 4px;
    height: 160px;
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
    background: var(--red);
    min-height: 4px;
    border-radius: 2px 2px 0 0;
    margin-top: auto;
    transition: background 0.2s;
    position: relative;
}

.bar-chart__bar:hover {
    background: #c0392b;
}

.bar-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--ink);
    color: #fff;
    font-family: var(--font-display);
    font-size: 10px;
    padding: 4px 8px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    margin-bottom: 4px;
}

.bar-chart__bar:hover .bar-tooltip {
    opacity: 1;
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

.data-label  {
    font-size: 12px;
    color: var(--ink);
     
    font-family: 'DM Sans';
}

.data-value {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--red);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }

    .page-content {
        padding: 16px;
    }
}
</style>
