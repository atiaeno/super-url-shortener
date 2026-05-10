<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: Object,
    range: Number,
    charts: Object,
    top_links: Array,
    recent_clicks: Array,
});

const ranges = [
    { value: 7, label: '7 Days' },
    { value: 30, label: '30 Days' },
    { value: 90, label: '90 Days' },
    { value: 365, label: '1 Year' },
];

const changeRange = (value) => router.get('/admin/analytics', { range: value }, { preserveScroll: true });

const tooltip = ref({ visible: false, text: '', x: 0, y: 0 });
const showTip = (e, text) => {
    const r = e.currentTarget.getBoundingClientRect();
    tooltip.value = { visible: true, text, x: r.left + r.width / 2, y: r.top };
};
const hideTip = () => { tooltip.value.visible = false; };
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
const getMax = (arr, key) => Math.max(...(arr || []).map(d => d[key] || d.count || d.clicks || 0), 1);
const getBarWidth = (count, data) => Math.max((count / getMax(data, 'count')) * 100, 2);
const getHeight = (val, arr) => Math.max((val / getMax(arr, 'clicks')) * 100, 5);
const getBarPx = (count, data, maxPx = 106) => Math.max((count / getMax(data, 'count')) * maxPx, 4) + 'px';
const getMiniBarPx = (count, data) => getBarPx(count, data, 76);
</script>

<template>

    <Head title="Analytics - Admin" />
    <AdminLayout>
        <div class="analytics-page">
            <div class="page-header">
                <div>
                    <span class="page-header__marker">Analytics</span>
                    <h1 class="page-header__title">Platform Overview</h1>
                    <p class="page-header__sub">Performance metrics and insights</p>
                </div>
                <div class="range-selector">
                    <button v-for="r in ranges" :key="r.value" @click="changeRange(r.value)"
                        :class="['range-btn', { active: range === r.value }]">
                        {{ r.label }}
                    </button>
                </div>
            </div>

            <div class="section-rule"></div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__icon-wrap stat-card__icon-wrap--links">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.clicks_today || 0 }}</div>
                        <div class="stat-card__label">Clicks Today</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap stat-card__icon-wrap--users">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.clicks_week || 0 }}</div>
                        <div class="stat-card__label">This Week</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap stat-card__icon-wrap--payouts">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.clicks_month || 0 }}</div>
                        <div class="stat-card__label">This Month</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap" style="background:#f0fdf4;color:#16a34a">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                            <polyline points="17 6 23 6 23 12" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.clicks_year || 0 }}</div>
                        <div class="stat-card__label">This Year</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap" style="background:#eff6ff;color:#3b82f6">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.unique_visitors || 0 }}</div>
                        <div class="stat-card__label">Unique Visitors</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap" style="background:#fef9f0;color:#d4af37">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.total_links || 0 }}</div>
                        <div class="stat-card__label">Total Links</div>
                    </div>
                </div>
            </div>

            <!-- Clicks Over Time -->
            <div class="chart-card">
                <div class="chart-card__header">
                    <span class="chart-card__marker">Trend</span>
                    <h3 class="chart-card__title">Clicks Over Time</h3>
                </div>
                <div class="bar-chart-wrap">
                    <div class="bar-chart">
                        <div v-for="item in charts?.clicks_over_time" :key="item.date" class="bar-col"
                            @mouseenter="(e) => showTip(e, `${formatDate(item.date)}: ${item.clicks} clicks`)"
                            @mouseleave="hideTip">
                            <div class="bar-chart__bar"
                                :style="{ height: getHeight(item.clicks, charts?.clicks_over_time) + '%' }"></div>
                            <span class="bar-xlabel">{{ formatDate(item.date) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <Teleport to="body">
                <div v-if="tooltip.visible" class="float-tooltip"
                    :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }">{{ tooltip.text }}</div>
            </Teleport>

            <!-- Two Column Charts -->
            <div class="charts-grid two-col">
                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Distribution</span>
                        <h3 class="chart-card__title">Hourly</h3>
                    </div>
                    <div class="bar-chart-v">
                        <div v-for="item in charts?.hourly_distribution" :key="item.hour" class="bar-col-v">
                            <div class="bar-tooltip">{{ item.hour }}:00 &mdash; {{ item.count }} clicks</div>
                            <div class="bar-v" :style="{ height: getBarPx(item.count, charts?.hourly_distribution) }">
                            </div>
                            <span class="bar-xlabel-v">{{ item.hour }}</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Distribution</span>
                        <h3 class="chart-card__title">Day of Week</h3>
                    </div>
                    <div class="day-list">
                        <div v-for="(item, idx) in charts?.day_of_week" :key="item.day" class="day-item">
                            <span class="day-name">{{ item.day }}</span>
                            <div class="day-bar-wrap">
                                <div class="day-bar"
                                    :style="{ width: getBarWidth(item.count, charts?.day_of_week) + '%' }">
                                </div>
                            </div>
                            <span class="day-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Three Column Charts -->
            <div class="charts-grid three-col">
                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Geographic</span>
                        <h3 class="chart-card__title">Top Countries</h3>
                    </div>
                    <div class="country-list">
                        <div v-for="(item, idx) in charts?.top_countries" :key="item.country_code" class="country-item">
                            <span class="country-rank">{{ idx + 1 }}</span>
                            <span class="country-flag">{{ item.flag }}</span>
                            <span class="country-name">{{ item.country_code }}</span>
                            <div class="country-bar-wrap">
                                <div class="country-bar"
                                    :style="{ width: getBarWidth(item.count, charts?.top_countries) + '%' }"></div>
                            </div>
                            <span class="country-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Devices</span>
                        <h3 class="chart-card__title">Device Types</h3>
                    </div>
                    <div class="device-list">
                        <div v-for="(item, idx) in charts?.devices" :key="item.device_type" class="device-item">
                            <span class="device-name">{{ item.device_type || 'Unknown' }}</span>
                            <div class="device-bar-wrap">
                                <div class="device-bar"
                                    :style="{ width: getBarWidth(item.count, charts?.devices) + '%' }">
                                </div>
                            </div>
                            <span class="device-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Browsers</span>
                        <h3 class="chart-card__title">Top Browsers</h3>
                    </div>
                    <div class="browser-list">
                        <div v-for="(item, idx) in charts?.browsers" :key="item.browser" class="browser-item">
                            <span class="browser-name">{{ item.browser || 'Unknown' }}</span>
                            <div class="browser-bar-wrap">
                                <div class="browser-bar"
                                    :style="{ width: getBarWidth(item.count, charts?.browsers) + '%' }"></div>
                            </div>
                            <span class="browser-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- More Charts -->
            <div class="charts-grid three-col">
                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Systems</span>
                        <h3 class="chart-card__title">Operating Systems</h3>
                    </div>
                    <div class="browser-list">
                        <div v-for="(item, idx) in charts?.os" :key="item.os" class="browser-item">
                            <span class="browser-name">{{ item.os || 'Unknown' }}</span>
                            <div class="browser-bar-wrap">
                                <div class="browser-bar" :style="{ width: getBarWidth(item.count, charts?.os) + '%' }">
                                </div>
                            </div>
                            <span class="browser-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Traffic</span>
                        <h3 class="chart-card__title">Top Referrers</h3>
                    </div>
                    <div class="referrer-list">
                        <div v-for="(item, idx) in charts?.referrers" :key="item.referrer_domain" class="referrer-item">
                            <span class="referrer-name">{{ item.referrer_domain || 'Direct' }}</span>
                            <div class="referrer-bar-wrap">
                                <div class="referrer-bar"
                                    :style="{ width: getBarWidth(item.count, charts?.referrers) + '%' }"></div>
                            </div>
                            <span class="referrer-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Growth</span>
                        <h3 class="chart-card__title">New Links</h3>
                    </div>
                    <div class="bar-chart-v mini">
                        <div v-for="item in charts?.new_links" :key="item.date" class="bar-col-v">
                            <div class="bar-tooltip">{{ formatDate(item.date) }}: {{ item.count }} new links</div>
                            <div class="bar-v mini" :style="{ height: getMiniBarPx(item.count, charts?.new_links) }">
                            </div>
                            <span class="bar-xlabel-v mini">{{ formatDate(item.date) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables -->
            <div class="charts-grid two-col">
                <div class="table-card">
                    <div class="table-card__header">
                        <span class="chart-card__marker">Performance</span>
                        <h3 class="chart-card__title">Top Links</h3>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Link</th>
                                <th>Clicks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="link in top_links" :key="link.id">
                                <td><code class="link-code">/{{ link.short_code }}</code></td>
                                <td><span class="clicks-value">{{ link.total_clicks }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-card">
                    <div class="table-card__header">
                        <span class="chart-card__marker">Recent</span>
                        <h3 class="chart-card__title">Latest Clicks</h3>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Country</th>
                                <th>Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="click in recent_clicks" :key="click.id">
                                <td>{{ formatDate(click.created_at) }}</td>
                                <td>{{ click.country_code || '-' }}</td>
                                <td>{{ click.device_type || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,500;0,600;1,400&family=Oswald:wght@400;500;600;700&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');

:root {
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
    --font-display: 'Oswald', sans-serif;
    --font-body: 'Crimson Pro', serif;
    --font-admin: 'DM Sans', sans-serif;
    --radius: 4px;
}

* {
    font-family: var(--font-admin);
    font-size: 13px;
    color: var(--ink);
}

.analytics-page {
    padding: 24px;
    background: var(--bg);
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
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

.range-selector {
    display: flex;
    gap: 8px;
}

.range-btn {
    padding: 8px 16px;
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: var(--radius);
    cursor: pointer;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    transition: all 0.2s;
}

.range-btn:hover {
    background: var(--surface-2);
}

.range-btn.active {
    background: var(--red);
    color: white;
    border-color: var(--red);
}

.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--surface);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-card__icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-card__icon-wrap svg {
    width: 18px;
    height: 18px;
}

.stat-card__content {
    flex: 1;
    min-width: 0;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-card__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
}

.charts-grid {
    display: grid;
    gap: 16px;
    margin-bottom: 16px;
}

.charts-grid.two-col {
    grid-template-columns: repeat(2, 1fr);
    margin-top: 20px !important;
}

.charts-grid.three-col {
    grid-template-columns: repeat(3, 1fr);
}

.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
}

.chart-card__header {
    margin-bottom: 16px;
}

.chart-card__marker {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    color: var(--red);
}

.chart-card__title {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    margin: 4px 0 0;
}

.bar-chart-wrap {
    overflow-x: auto;
    overflow-y: visible;
    padding-bottom: 28px;
    padding-top: 40px;
}

.bar-chart {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    height: 120px;
    background: transparent;
    border-bottom: 2px solid var(--border);
    padding: 0;
    min-width: 100%;
}

.bar-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    position: relative;
    height: 100%;
    min-width: 0;
}

.bar-col:hover .bar-tooltip {
    opacity: 1;
    pointer-events: auto;
}

.bar-tooltip {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%);
    background: var(--ink);
    color: #fff;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 3px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s;
    z-index: 100;
}

.bar-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: var(--ink);
}

.bar-chart__bar {
    width: 100%;
    background: var(--red);
    border-radius: 2px 2px 0 0;
    min-height: 4px;
    transition: height 0.3s, opacity 0.2s;
}

.bar-col:hover .bar-chart__bar {
    opacity: 0.8;
}

.float-tooltip {
    position: fixed;
    transform: translate(-50%, -100%);
    background: var(--ink);
    color: #fff;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 9999;
    margin-top: -6px;
}

.float-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: var(--ink);
}

.bar-xlabel {
    font-family: var(--font-display);
    font-size: 8px;
    color: var(--muted);
    text-transform: uppercase;
    margin-top: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
    max-width: 28px;
    text-align: center;
    position: absolute;
    bottom: -20px;
}

.bar-chart-v {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    height: 110px;
    background: var(--surface-2);
    border-radius: var(--radius);
    padding: 4px 4px 0;
    overflow: visible;
    margin-bottom: 24px;
}

.bar-chart-v.mini {
    height: 80px;
}

.bar-col-v {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    position: relative;
    height: 100%;
    min-width: 0;
}

.bar-col-v:hover .bar-tooltip {
    opacity: 1;
    pointer-events: auto;
}

.bar-col-v:hover .bar-v {
    opacity: 0.8;
}

.bar-v {
    width: 100%;
    background: var(--red);
    border-radius: 2px 2px 0 0;
    min-height: 4px;
    transition: height 0.3s, opacity 0.2s;
}

.bar-v.mini {
    background: #27ae60;
}

.bar-xlabel-v {
    font-family: var(--font-display);
    font-size: 7px;
    color: var(--muted);
    text-transform: uppercase;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
    max-width: 22px;
    text-align: center;
    position: absolute;
    bottom: -18px;
}

.bar-xlabel-v.mini {
    font-size: 6px;
    max-width: 18px;
}

.bar-v-label {
    display: none;
}

.day-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.day-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.day-name {
    width: 40px;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--ink);
}

.day-bar-wrap {
    flex: 1;
    height: 12px;
    background: var(--surface-2);
    border-radius: 6px;
    overflow: hidden;
}

.day-bar {
    height: 100%;
    background: var(--red);
    border-radius: 6px;
}

.day-count {
    width: 40px;
    text-align: right;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--ink);
}

.country-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.country-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.country-rank {
    width: 20px;
    height: 20px;
    background: var(--surface-2);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    color: var(--muted);
}

.country-flag {
    width: 20px;
    text-align: center;
}

.country-name {
    width: 50px;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--ink);
}

.country-bar-wrap {
    flex: 1;
    height: 8px;
    background: var(--surface-2);
    border-radius: 4px;
    overflow: hidden;
}

.country-bar {
    height: 100%;
    background: var(--red);
    border-radius: 4px;
}

.country-count {
    width: 40px;
    text-align: right;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--ink);
}

.device-list,
.browser-list,
.referrer-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.device-item,
.browser-item,
.referrer-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.device-name,
.browser-name {
    width: 80px;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--ink);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.referrer-name {
    flex: 1;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--ink);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100px;
}

.device-bar-wrap,
.browser-bar-wrap,
.referrer-bar-wrap {
    flex: 1;
    height: 10px;
    background: var(--surface-2);
    border-radius: 5px;
    overflow: hidden;
}

.device-bar,
.browser-bar,
.referrer-bar {
    height: 100%;
    border-radius: 5px;
}

.device-bar {
    background: #3498db;
}

.browser-bar {
    background: #9b59b6;
}

.referrer-bar {
    background: #e67e22;
}

.device-count,
.browser-count,
.referrer-count {
    width: 40px;
    text-align: right;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--ink);
}

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
}

.table-card__header {
    margin-bottom: 16px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
    text-align: left;
    padding: 8px 12px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
}

.data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    font-size: 12px;
}

.link-code {
    background: var(--surface-2);
    padding: 2px 6px;
    border-radius: 2px;
    font-family: var(--font-admin);
    font-size: 11px;
    color: var(--red);
}

.clicks-value {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: #27ae60;
}
</style>
