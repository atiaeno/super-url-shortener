<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const admin = computed(() => page.props.auth?.user);

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalUsers: 0,
            totalLinks: 0,
            totalClicks: 0,
            pendingPayouts: 0,
            activeLinks: 0,
            flaggedLinks: 0,
        }),
    },
    charts: {
        type: Object,
        default: () => ({
            clicksOverTime: [],
            topCountries: [],
        }),
    },
    latestUsers: {
        type: Array,
        default: () => [],
    },
    latestPayouts: {
        type: Array,
        default: () => [],
    },
});

const statItems = computed(() => [
    {
        id: 'users',
        label: 'Total Users',
        value: props.stats.totalUsers ?? 0,
        roman: 'I.',
        icon: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    },
    {
        id: 'links',
        label: 'Total Links',
        value: props.stats.totalLinks ?? 0,
        roman: 'II.',
        icon: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    },
    {
        id: 'clicks',
        label: 'Total Clicks',
        value: props.stats.totalClicks ?? 0,
        roman: 'III.',
        icon: `<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>`,
    },
    {
        id: 'payouts',
        label: 'Pending Payouts',
        value: props.stats.pendingPayouts ?? 0,
        roman: 'IV.',
        icon: `<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    },
]);

const icons = {
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
    chart: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    globe: `<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>`,
    users: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    shield: `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>`,
    settings: `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09A1.65 1.65 0 0 0 15.32 4.68l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15z"/>`,
};

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getBarHeight = (count) => {
    const max = Math.max(...props.charts.clicksOverTime.map(d => d.count), 1);
    return Math.max((count / max) * 100, 5);
};

const getCountryBarWidth = (count) => {
    const max = Math.max(...props.charts.topCountries.map(d => d.count), 1);
    return Math.max((count / max) * 100, 5);
};
</script>

<template>

    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header-icon>
            <rect x="3" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="14" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" />
        </template>
        <template #header>Dashboard</template>

        <div class="dashboard">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Overview</span>
                    <h1 class="page-header__title">
                        Welcome back<span v-if="admin?.name">, {{ admin.name.split(' ')[0] }}</span>
                    </h1>
                    <p class="page-header__sub">Here's what's happening with your platform today.</p>
                </div>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div v-for="item in statItems" :key="item.id" class="stat-card" :class="`stat-card--${item.id}`">
                        <div class="stat-card__top">
                            <span class="stat-card__roman">{{ item.roman }}</span>
                            <div class="stat-card__icon-wrap" :class="`stat-card__icon-wrap--${item.id}`">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="item.icon" />
                            </div>
                        </div>
                        <span class="stat-card__value">{{ typeof item.value === 'number' ? item.value.toLocaleString() :
                            item.value }}</span>
                        <span class="stat-card__label">{{ item.label }}</span>
                    </div>
                </div>
            </section>

            <!-- Quick Actions Strip -->
            <section class="actions-strip">
                <Link :href="route('admin.users.index')" class="action-tile">
                    <div class="action-tile__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.users" />
                    </div>
                    <div class="action-tile__text">
                        <strong>Manage Users</strong>
                        <span>View & edit users</span>
                    </div>
                    <svg class="action-tile__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" v-html="icons.arrow" />
                </Link>
                <div class="actions-divider"></div>
                <Link :href="route('admin.payouts.index')" class="action-tile">
                    <div class="action-tile__icon action-tile__icon--gold">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div class="action-tile__text">
                        <strong>Review Payouts</strong>
                        <span>Approve & manage</span>
                    </div>
                    <svg class="action-tile__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" v-html="icons.arrow" />
                </Link>
                <div class="actions-divider"></div>
                <Link :href="route('admin.moderation.index')" class="action-tile">
                    <div class="action-tile__icon action-tile__icon--blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.shield" />
                    </div>
                    <div class="action-tile__text">
                        <strong>Moderation</strong>
                        <span>Review flagged content</span>
                    </div>
                    <svg class="action-tile__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" v-html="icons.arrow" />
                </Link>
                <div class="actions-divider"></div>
                <Link :href="route('admin.settings.index')" class="action-tile">
                    <div class="action-tile__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.settings" />
                    </div>
                    <div class="action-tile__text">
                        <strong>Settings</strong>
                        <span>Configure platform</span>
                    </div>
                    <svg class="action-tile__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" v-html="icons.arrow" />
                </Link>
            </section>

            <section class="charts-section">
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-card__header">
                            <span class="chart-card__marker">V.</span>
                            <span class="chart-card__title">Clicks Over Time (7 Days)</span>
                        </div>
                        <div class="chart-card__body">
                            <div v-if="charts.clicksOverTime.length === 0" class="chart-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                    <polyline points="17 6 23 6 23 12" />
                                </svg>
                                <span>No click data yet</span>
                            </div>
                            <div v-else class="bar-chart">
                                <div v-for="item in charts.clicksOverTime" :key="item.date" class="bar-chart__item">
                                    <div class="bar-chart__bar-wrap">
                                        <div class="bar-chart__bar" :style="{ height: getBarHeight(item.count) + '%' }">
                                        </div>
                                    </div>
                                    <span class="bar-chart__label">{{ formatDate(item.date) }}</span>
                                    <span class="bar-chart__value">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-card__header">
                            <span class="chart-card__marker">VI.</span>
                            <span class="chart-card__title">Top Countries</span>
                        </div>
                        <div class="chart-card__body">
                            <div v-if="charts.topCountries.length === 0" class="chart-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="icons.globe" />
                                <span>No geographic data yet</span>
                            </div>
                            <div v-else class="country-list">
                                <div v-for="(item, index) in charts.topCountries" :key="item.country"
                                    class="country-item">
                                    <span class="country-item__rank">{{ index + 1 }}</span>
                                    <span class="country-item__name">{{ item.country }}</span>
                                    <div class="country-item__bar-wrap">
                                        <div class="country-item__bar"
                                            :style="{ width: getCountryBarWidth(item.count) + '%' }">
                                        </div>
                                    </div>
                                    <span class="country-item__count">{{ item.count.toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Latest Data Tables -->
            <section class="tables-section">
                <div class="tables-grid">
                    <!-- Latest Registered Users -->
                    <div class="table-card">
                        <div class="table-card__header">
                            <span class="table-card__marker">VII.</span>
                            <span class="table-card__title">Latest Registered Users</span>
                            <Link :href="route('admin.users.index')" class="table-card__view-all">View All</Link>
                        </div>
                        <div class="table-card__body">
                            <div v-if="latestUsers.length === 0" class="table-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span>No users registered yet</span>
                            </div>
                            <div v-else class="data-table">
                                <div v-for="(user, index) in latestUsers.slice(0, 5)" :key="user.id" class="data-row">
                                    <span class="data-row__index">{{ index + 1 }}</span>
                                    <div class="data-row__content">
                                        <span class="data-row__primary">{{ user.name }}</span>
                                        <span class="data-row__secondary">{{ user.email }}</span>
                                    </div>
                                    <span class="data-row__meta">{{ formatDate(user.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Latest Payment Requests -->
                    <div class="table-card">
                        <div class="table-card__header">
                            <span class="table-card__marker">VIII.</span>
                            <span class="table-card__title">Latest Payment Requests</span>
                            <Link :href="route('admin.payouts.index')" class="table-card__view-all">View All</Link>
                        </div>
                        <div class="table-card__body">
                            <div v-if="latestPayouts.length === 0" class="table-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <line x1="12" y1="1" x2="12" y2="23" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                <span>No payment requests yet</span>
                            </div>
                            <div v-else class="data-table">
                                <div v-for="(payout, index) in latestPayouts.slice(0, 5)" :key="payout.id"
                                    class="data-row">
                                    <span class="data-row__index">{{ index + 1 }}</span>
                                    <div class="data-row__content">
                                        <span class="data-row__primary">{{ payout.user?.name || 'Unknown User' }}</span>
                                        <span class="data-row__secondary">${{ Number(payout.amount)?.toFixed(2) ||
                                            '0.00'
                                        }}</span>
                                    </div>
                                    <span class="data-row__meta"
                                        :class="`data-row__meta--${payout.status?.toLowerCase()}`">
                                        {{ payout.status || 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Platform Stats -->
            <section class="links-section">
                <div class="section-header">
                    <div>
                        <span class="section-marker">Platform Health</span>
                        <p class="section-sub">Quick overview of system status</p>
                    </div>
                </div>

                <div class="health-grid">
                    <div class="health-card">
                        <div class="health-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                        <div class="health-card__content">
                            <span class="health-card__value">{{ stats.activeLinks ?? 0 }}</span>
                            <span class="health-card__label">Active Links</span>
                        </div>
                    </div>
                    <div class="health-card health-card--warning">
                        <div class="health-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                        </div>
                        <div class="health-card__content">
                            <span class="health-card__value">{{ stats.flaggedLinks ?? 0 }}</span>
                            <span class="health-card__label">Flagged Links</span>
                        </div>
                    </div>
                    <div class="health-card health-card--success">
                        <div class="health-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                        </div>
                        <div class="health-card__content">
                            <span class="health-card__value">99.9%</span>
                            <span class="health-card__label">Uptime</span>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
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

/* ── Stats Grid ───────────────────────────── */
.stats-section {
    margin-bottom: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;

    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.stat-card {
    background: linear-gradient(135deg, #fff9f5 0%, var(--surface) 50%, #fef6f0 100%);
    padding: 20px 18px 16px;
    display: flex;
    flex-direction: column;
    transition: background 0.15s ease;
    position: relative;
}

.stat-card:hover {
    background: #fdf9f5;
}

.stat-card--users {
    background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 50%, #fff 100%);
}

.stat-card--links {
    background: linear-gradient(135deg, #eff6ff 0%, #f0f7ff 50%, #fff 100%);
}

.stat-card--clicks {
    background: linear-gradient(135deg, #f0fdf4 0%, #f5fff0 50%, #fff 100%);
}

.stat-card--payouts {
    background: linear-gradient(135deg, #fef9f0 0%, #fffbf5 50%, #fff 100%);
}

.stat-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.stat-card__roman {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    color: var(--red);
}

.stat-card__icon-wrap {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card__icon-wrap svg {
    width: 14px;
    height: 14px;
}

.stat-card__icon-wrap--users {
    background: #fef2f2;
    color: var(--red);
}

.stat-card__icon-wrap--links {
    background: #eff6ff;
    color: #3b82f6;
}

.stat-card__icon-wrap--clicks {
    background: #f0fdf4;
    color: #16a34a;
}

.stat-card__icon-wrap--payouts {
    background: #fef9f0;
    color: var(--gold);
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 26px;
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

/* ── Actions Strip ────────────────────────── */
.actions-strip {
    display: flex;
    align-items: stretch;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 28px;
}

.action-tile {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    text-decoration: none;
    color: var(--ink);
    transition: background 0.15s ease;
}

.action-tile:hover {
    background: var(--surface-2);
}

.action-tile__icon {
    width: 32px;
    height: 32px;
    background: #fef2f2;
    color: var(--red);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.action-tile__icon--gold {
    background: #fef9f0;
    color: var(--gold);
}

.action-tile__icon--blue {
    background: #eff6ff;
    color: #3b82f6;
}

.action-tile__icon svg {
    width: 15px;
    height: 15px;
}

.action-tile__text {
    display: flex;
    flex-direction: column;
    gap: 1px;
    flex: 1;
    min-width: 0;
}

.action-tile__text strong {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    white-space: nowrap;
}

.action-tile__text span {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--muted);
    white-space: nowrap;
}

.action-tile__arrow {
    width: 14px;
    height: 14px;
    color: var(--muted);
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.action-tile:hover .action-tile__arrow {
    opacity: 1;
    transform: translateX(2px);
}

.actions-divider {
    width: 1px;
    background: var(--border);
    align-self: stretch;
    margin: 10px 0;
}

/* ── Charts Section ─────────────────────────── */
.charts-section {
    margin-bottom: 24px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.chart-card {
    background: var(--surface);
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.chart-card__header {
    display: flex;
    align-items: center;
    gap: 10px;
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
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
}

.chart-card__body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100px;
}

.chart-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    color: var(--muted);
}

.chart-placeholder svg {
    width: 36px;
    height: 36px;
    opacity: 0.4;
}

.chart-placeholder span {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
}

/* ── Bar Chart ───────────────────────────── */
.bar-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 12px;
    width: 100%;
    height: 120px;
    padding: 0 10px;
}

.bar-chart__item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.bar-chart__bar-wrap {
    width: 100%;
    height: 80px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    background: var(--surface-2);
    border-radius: var(--radius);
    padding: 4px;
}

.bar-chart__bar {
    width: 100%;
    background: var(--red);
    border-radius: 2px;
    min-height: 4px;
    transition: height 0.3s ease;
}

.bar-chart__label {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 500;
    color: var(--muted);
    text-transform: uppercase;
}

.bar-chart__value {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--ink);
}

/* ── Country List ────────────────────────── */
.country-list {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.country-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.country-item:last-child {
    border-bottom: none;
}

.country-item__rank {
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
    flex-shrink: 0;
}

.country-item__name {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    color: var(--ink);
    width: 80px;
    flex-shrink: 0;
}

.country-item__bar-wrap {
    flex: 1;
    height: 8px;
    background: var(--surface-2);
    border-radius: 4px;
    overflow: hidden;
}

.country-item__bar {
    height: 100%;
    background: var(--red);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.country-item__count {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--ink);
    width: 50px;
    text-align: right;
    flex-shrink: 0;
}

/* ── Links Section ────────────────────────── */
.links-section {
    margin-bottom: 32px;
}

.section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 14px;
}

.section-marker {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    display: block;
    margin-bottom: 2px;
}

.section-sub {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--muted);
    margin: 0;
}

/* ── Health Grid ─────────────────────────── */
.health-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.health-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: linear-gradient(135deg, #f0fdf4 0%, #f5fff0 50%, #fff 100%);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.health-card--warning {
    background: linear-gradient(135deg, #fef9c3 0%, #fffde7 50%, #fff 100%);
}

.health-card--success {
    background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 50%, #fff 100%);
}

.health-card__icon {
    width: 44px;
    height: 44px;
    background: #f0fdf4;
    color: #16a34a;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.health-card__icon svg {
    width: 22px;
    height: 22px;
}

.health-card--warning .health-card__icon {
    background: #fef9c3;
    color: #ca8a04;
}

.health-card--success .health-card__icon {
    background: #dcfce7;
    color: #16a34a;
}

.health-card__content {
    display: flex;
    flex-direction: column;
}

.health-card__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
}

.health-card__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
}

/* ── Tables Section ───────────────────────── */
.tables-section {
    margin-bottom: 24px;
}

.tables-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.table-card {
    background: var(--surface);
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.table-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 16px;
}

.table-card__marker {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    color: var(--red);
}

.table-card__title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    flex: 1;
}

.table-card__view-all {
    font-family: var(--font-body);
    font-size: 10px;
    font-style: italic;
    color: var(--muted);
    text-decoration: none;
    transition: color 0.15s ease;
}

.table-card__view-all:hover {
    color: var(--red);
}

.table-card__body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 180px;
}

.table-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    color: var(--muted);
}

.table-placeholder svg {
    width: 36px;
    height: 36px;
    opacity: 0.4;
}

.table-placeholder span {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
}

.data-table {
    display: table;
    flex-direction: column;
    gap: 8px;
    width: 100%;
}

.data-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.data-row:last-child {
    border-bottom: none;
}

.data-row__index {
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
    flex-shrink: 0;
}

.data-row__content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.data-row__primary {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.data-row__secondary {
    font-family: var(--font-body);
    font-size: 11px;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.data-row__meta {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
    white-space: nowrap;
    flex-shrink: 0;
}

.data-row__meta--pending {
    color: #f59e0b;
}

.data-row__meta--approved {
    color: #16a34a;
}

.data-row__meta--rejected {
    color: #dc2626;
}

/* ── Responsive ───────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .tables-grid {
        grid-template-columns: 1fr;
    }

    .health-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .actions-strip {
        flex-direction: column;
    }

    .actions-divider {
        width: auto;
        height: 1px;
        margin: 0 10px;
    }
}
</style>
