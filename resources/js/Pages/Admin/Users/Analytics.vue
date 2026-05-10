<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
    stats: Object,
    range: Number,
    charts: Object,
    links: Array,
});

const ranges = [
    { value: 7, label: '7 Days' },
    { value: 30, label: '30 Days' },
    { value: 90, label: '90 Days' },
    { value: 365, label: '1 Year' },
];

const changeRange = (value) => router.get(`/admin/users/${props.user.id}/analytics`, { range: value }, { preserveScroll: true });
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const getMax = (arr, key) => Math.max(...(arr || []).map(d => d[key] || d.count || d.clicks || 0), 1);
const getBarWidth = (count, data) => Math.max((count / getMax(data, 'count')) * 100, 2);
const getHeight = (val, arr) => Math.max((val / getMax(arr, 'clicks')) * 100, 5);
</script>

<template>

    <Head title="User Analytics - Admin" />
    <AdminLayout>
        <div class="analytics-page">
            <div class="page-header">
                <div>
                    <span class="page-header__marker">User Analytics</span>
                    <h1 class="page-header__title">{{ user?.name }}</h1>
                    <p class="user-email">{{ user?.email }}</p>
                    <p class="user-created">Member since: {{ formatDate(user?.created_at) }}</p>
                </div>
                <div class="range-selector">
                    <button v-for="r in ranges" :key="r.value" @click="changeRange(r.value)"
                        :class="['range-btn', { active: range === r.value }]">
                        {{ r.label }}
                    </button>
                </div>
            </div>

            <div class="section-rule"></div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__icon-wrap stat-card__icon-wrap--links">
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
                <div class="stat-card">
                    <div class="stat-card__icon-wrap stat-card__icon-wrap--users">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.total_clicks || 0 }}</div>
                        <div class="stat-card__label">Total Clicks</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon-wrap" style="background:#f0fdf4;color:#16a34a">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <div class="stat-card__value">{{ stats?.active_links || 0 }}</div>
                        <div class="stat-card__label">Active Links</div>
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
                        <div class="stat-card__value">{{ stats?.avg_clicks_per_link || 0 }}</div>
                        <div class="stat-card__label">Avg Clicks/Link</div>
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
                    <div v-for="item in charts?.clicks_over_time" :key="item.date" class="bar-chart__bar"
                        :style="{ height: getHeight(item.clicks, charts?.clicks_over_time) + '%' }"></div>
                </div>
            </div>

            <!-- Two Columns -->
            <div class="charts-grid two-col">
                <div class="chart-card">
                    <div class="chart-card__header">
                        <span class="chart-card__marker">Geographic</span>
                        <h3 class="chart-card__title">Top Countries</h3>
                    </div>
                    <div class="country-list">
                        <div v-for="(item, idx) in charts?.countries" :key="item.country_code" class="country-item">
                            <span class="country-flag">{{ item.flag }}</span>
                            <span class="country-name">{{ item.country_code }}</span>
                            <div class="country-bar-wrap">
                                <div class="country-bar"
                                    :style="{ width: getBarWidth(item.count, charts?.countries) + '%' }"></div>
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
                                    :style="{ width: getBarWidth(item.count, charts?.devices) + '%' }"></div>
                            </div>
                            <span class="device-count">{{ item.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Links Table -->
            <div class="table-card">
                <div class="table-card__header">
                    <span class="chart-card__marker">User Links</span>
                    <h3 class="chart-card__title">All Links</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Link</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Clicks</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="link in links" :key="link.id">
                            <td><code class="link-code">/{{ link.short_code }}</code></td>
                            <td class="url-cell">{{ link.url }}</td>
                            <td><span :class="['status-badge', link.is_active ? 'active' : 'inactive']">{{
                                link.is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td><span class="clicks-value">{{ link.total_clicks }}</span></td>
                            <td>{{ formatDate(link.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
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
    align-items: flex-start;
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

.user-email {
    color: var(--muted);
    margin: 4px 0 0;
    font-size: 14px;
}

.user-created {
    color: var(--muted);
    margin: 4px 0 0;
    font-size: 12px;
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
    grid-template-columns: repeat(4, 1fr);
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
}

.stat-card__icon-wrap svg {
    width: 18px;
    height: 18px;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
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

.bar-chart {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    height: 120px;
    background: var(--surface-2);
    border-radius: var(--radius);
    padding: 4px;
}

.bar-chart__bar {
    flex: 1;
    background: var(--red);
    border-radius: 2px;
    min-height: 4px;
}

.country-list,
.device-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.country-item,
.device-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.country-flag {
    width: 24px;
}

.country-name,
.device-name {
    width: 60px;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--ink);
}

.country-bar-wrap,
.device-bar-wrap {
    flex: 1;
    height: 10px;
    background: var(--surface-2);
    border-radius: 5px;
    overflow: hidden;
}

.country-bar,
.device-bar {
    height: 100%;
    border-radius: 5px;
}

.country-bar {
    background: var(--red);
}

.device-bar {
    background: #3498db;
}

.country-count,
.device-count {
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

.url-cell {
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.status-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.active {
    background: #dcfce7;
    color: #16a34a;
}

.status-badge.inactive {
    background: #fef2f2;
    color: #dc2626;
}

.clicks-value {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: #27ae60;
}
</style>
