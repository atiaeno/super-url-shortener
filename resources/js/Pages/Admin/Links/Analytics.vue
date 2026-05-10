<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    link: Object,
    stats: Object,
    range: Number,
    charts: Object,
    recent_clicks: Array,
});

const ranges = [
    { value: 7, label: '7 Days' },
    { value: 30, label: '30 Days' },
    { value: 90, label: '90 Days' },
    { value: 365, label: '1 Year' },
];

const changeRange = (value) => router.get(`/admin/links/${props.link.id}/analytics`, { range: value }, { preserveScroll: true });
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
const getMax = (arr, key) => Math.max(...(arr || []).map(d => d[key] || d.count || d.clicks || 0), 1);
const getBarWidth = (count, data) => Math.max((count / getMax(data, 'count')) * 100, 2);
const getHeight = (val, arr) => Math.max((val / getMax(arr, 'clicks')) * 100, 5);
</script>

<template>
    <Head title="Link Analytics - Admin" />
    <AdminLayout>
        <div class="analytics-page">
            <div class="page-header">
                <div>
                    <span class="page-header__marker">Link Analytics</span>
                    <div class="link-header">
                        <h1 class="page-header__title">/{{ link?.short_code }}</h1>
                    </div>
                    <p class="link-url">{{ link?.url }}</p>
                    <p class="link-created">Created: {{ formatDate(link?.created_at) }}</p>
                </div>
                <div class="range-selector">
                    <button v-for="r in ranges" :key="r.value" @click="changeRange(r.value)" :class="['range-btn', { active: range === r.value }]">
                        {{ r.label }}
                    </button>
                </div>
            </div>

            <div class="section-rule"></div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__icon-wrap stat-card__icon-wrap--links">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.total_clicks || 0 }}</div>
                        <div class="stat-card__label">Total Clicks</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap" style="background:#eff6ff;color:#3b82f6">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.unique_visitors || 0 }}</div>
                        <div class="stat-card__label">Unique Visitors</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap stat-card__icon-wrap--payouts">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.avg_per_day || 0 }}</div>
                        <div class="stat-card__label">Avg/Day</div>
                    </div>
                </div>
            </div>

            <!-- Clicks Over Time -->
            <div class="chart-card">
                <div class="chart-card__header">
                    <span class="chart-card__marker">Trend</span>
                    <h3 class="chart-card__title">Clicks Over Time</h3>
                </div>
                <div class="bar-chart">
                    <div v-for="item in charts?.clicks_over_time" :key="item.date" class="bar-chart__bar" :style="{ height: getHeight(item.clicks, charts?.clicks_over_time) + '%' }"></div>
                </div>
            </div>

            <!-- Two Columns -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Distribution</span>
                        <h3 class="chart-card__title">Hourly</h3>
                    </div>
                    <div class="bar-chart-v">
                        <div v-for="item in charts?.hourly" :key="item.hour" class="bar-v" :style="{ height: getBarWidth(item.count, charts?.hourly) * 1.5 + '%' }">
                            <span class="bar-v-label">{{ item.hour }}h</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Geographic</span>
                        <h3 class="chart-card__title">Countries</h3>
                    </div>
                    <div class="country-list">
                        <div v-for="(item, idx) in charts?.countries" :key="item.country_code" class="country-item">
                            <span class="country-flag">{{ item.flag }}</span>
                            <span class="country-name">{{ item.country_code }}</span>
                            <div class="country-bar-wrap">
                                <div class="country-bar" :style="{ width: getBarWidth(item.count, charts?.countries) + '%' }"></div>
                            </div>
                            <span class="country-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Three Columns -->
            <div class="charts-grid three-col">
                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Devices</span>
                        <h3 class="chart-card__title">Device Types</h3>
                    </div>
                    <div class="device-list">
                        <div v-for="(item, idx) in charts?.devices" :key="item.device_type" class="device-item">
                            <span class="device-name">{{ item.device_type || 'Unknown' }}</span>
                            <div class="device-bar-wrap">
                                <div class="device-bar" :style="{ width: getBarWidth(item.count, charts?.devices) + '%' }"></div>
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
                                <div class="browser-bar" :style="{ width: getBarWidth(item.count, charts?.browsers) + '%' }"></div>
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
                                <div class="referrer-bar" :style="{ width: getBarWidth(item.count, charts?.referrers) + '%' }"></div>
                            </div>
                            <span class="referrer-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-card">
                <div class="table-card__header">
                    <span class="chart-card__marker">Recent</span>
                    <h3 class="chart-card__title">Latest Clicks</h3>
                </div>
                <table class="data-table">
                    <thead><tr><th>Time</th><th>Country</th><th>Device</th><th>Browser</th><th>Referrer</th></tr></thead>
                    <tbody>
                        <tr v-for="click in recent_clicks" :key="click.id">
                            <td>{{ formatDate(click.created_at) }}</td>
                            <td>{{ click.country_code || '-' }}</td>
                            <td>{{ click.device_type || '-' }}</td>
                            <td>{{ click.browser || '-' }}</td>
                            <td>{{ click.referrer || 'Direct' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,500;0,600;1,400&family=Oswald:wght@400;500;600;700&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');

:root {
    --red: #e74c3c;
    --gold: #d4af37;
    --ink: #1a1a1a;
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

* { font-family: var(--font-admin); font-size: 13px; color: var(--ink); }
.analytics-page { padding: 24px; background: var(--bg); min-height: 100vh; }

.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-header__marker { font-family: var(--font-display); font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--red); display: block; margin-bottom: 8px; }
.page-header__title { font-family: var(--font-display); font-size: 24px; font-weight: 600; color: var(--ink); margin: 0; }
.link-header { display: flex; align-items: center; gap: 12px; }
.link-url { color: var(--muted); margin: 8px 0 0; word-break: break-all; font-size: 13px; }
.link-created { color: var(--muted); margin: 4px 0 0; font-size: 12px; }

.range-selector { display: flex; gap: 8px; }
.range-btn { padding: 8px 16px; border: 1px solid var(--border); background: var(--surface); border-radius: var(--radius); cursor: pointer; font-family: var(--font-display); font-size: 11px; font-weight: 500; text-transform: uppercase; }
.range-btn.active { background: var(--red); color: white; border-color: var(--red); }

.section-rule { height: 1px; background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px); margin-bottom: 28px; }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--border); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 28px; }
.stat-card { background: var(--surface); padding: 16px; display: flex; align-items: center; gap: 12px; }
.stat-card__icon-wrap { width: 36px; height: 36px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; }
.stat-card__icon-wrap svg { width: 18px; height: 18px; }
.stat-card__value { font-family: var(--font-display); font-size: 22px; font-weight: 600; color: var(--ink); }
.stat-card__label { font-family: var(--font-display); font-size: 10px; font-weight: 500; text-transform: uppercase; color: var(--muted); }

.charts-grid { display: grid; gap: 16px; margin-bottom: 16px; }
.charts-grid.two-col { grid-template-columns: repeat(2, 1fr); }
.charts-grid.three-col { grid-template-columns: repeat(3, 1fr); }

.chart-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; }
.chart-card__header { margin-bottom: 16px; }
.chart-card__marker { font-family: var(--font-display); font-size: 10px; font-weight: 700; color: var(--red); }
.chart-card__title { font-family: var(--font-display); font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--ink); margin: 4px 0 0; }

.bar-chart { display: flex; align-items: flex-end; gap: 2px; height: 120px; background: var(--surface-2); border-radius: var(--radius); padding: 4px; }
.bar-chart__bar { flex: 1; background: var(--red); border-radius: 2px; min-height: 4px; }

.bar-chart-v { display: flex; align-items: flex-end; gap: 3px; height: 120px; }
.bar-v { flex: 1; background: #3498db; border-radius: 2px 2px 0 0; min-height: 4px; display: flex; align-items: flex-end; justify-content: center; }
.bar-v-label { font-family: var(--font-display); font-size: 8px; color: var(--muted); transform: rotate(-45deg); margin-bottom: 4px; }

.country-list, .device-list, .browser-list, .referrer-list { display: flex; flex-direction: column; gap: 8px; }
.country-item, .device-item, .browser-item, .referrer-item { display: flex; align-items: center; gap: 8px; }
.country-flag { width: 24px; }
.country-name, .device-name, .browser-name { width: 70px; font-family: var(--font-display); font-size: 11px; font-weight: 500; color: var(--ink); }
.referrer-name { flex: 1; font-family: var(--font-display); font-size: 11px; font-weight: 500; color: var(--ink); max-width: 100px; overflow: hidden; text-overflow: ellipsis; }
.country-bar-wrap, .device-bar-wrap, .browser-bar-wrap, .referrer-bar-wrap { flex: 1; height: 10px; background: var(--surface-2); border-radius: 5px; overflow: hidden; }
.country-bar, .device-bar, .browser-bar, .referrer-bar { height: 100%; border-radius: 5px; }
.country-bar { background: var(--red); }
.device-bar { background: #3498db; }
.browser-bar { background: #9b59b6; }
.referrer-bar { background: #e67e22; }
.country-count, .device-count, .browser-count, .referrer-count { width: 40px; text-align: right; font-family: var(--font-display); font-size: 11px; font-weight: 600; color: var(--ink); }

.table-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; margin-top: 16px; }
.table-card__header { margin-bottom: 16px; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { font-family: var(--font-display); font-size: 10px; font-weight: 600; text-transform: uppercase; color: var(--muted); text-align: left; padding: 8px 12px; background: var(--surface-2); border-bottom: 1px solid var(--border); }
.data-table td { padding: 10px 12px; border-bottom: 1px solid var(--border); font-size: 12px; }
</style>
