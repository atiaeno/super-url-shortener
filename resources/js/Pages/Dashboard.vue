<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_links: 0,
            total_clicks: 0,
            clicks_today: 0,
            active_links: 0,
        }),
    },
    recentLinks: {
        type: Array,
        default: () => [],
    },
});

const statItems = computed(() => [
    {
        id: 'links',
        label: 'Total Links',
        value: props.stats.total_links,
        roman: 'I.',
        icon: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    },
    {
        id: 'clicks',
        label: 'Total Clicks',
        value: props.stats.total_clicks,
        roman: 'II.',
        icon: `<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>`,
    },
    {
        id: 'today',
        label: 'Clicks Today',
        value: props.stats.clicks_today,
        roman: 'III.',
        icon: `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
    },
    {
        id: 'active',
        label: 'Active Links',
        value: props.stats.active_links,
        roman: 'IV.',
        icon: `<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>`,
    },
]);

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const icons = {
    external: `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>`,
    chart: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
    plus: `<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>`,
    link: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    bolt: `<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>`,
};
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>Dashboard</template>

        <div class="dashboard">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Overview</span>
                    <h1 class="page-header__title">
                        Welcome back<span v-if="user?.name">, {{ user.name.split(' ')[0] }}</span>
                    </h1>
                    <p class="page-header__sub">Here's what's happening with your links today.</p>
                </div>
                <Link :href="route('links.create')" class="create-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.plus" />
                    New Link
                </Link>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div v-for="item in statItems" :key="item.id" class="stat-card">
                        <div class="stat-card__top">
                            <span class="stat-card__roman">{{ item.roman }}</span>
                            <div class="stat-card__icon-wrap" :class="`stat-card__icon-wrap--${item.id}`">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="item.icon" />
                            </div>
                        </div>
                        <span class="stat-card__value">{{ item.value.toLocaleString() }}</span>
                        <span class="stat-card__label">{{ item.label }}</span>
                    </div>
                </div>
            </section>

            <!-- Quick Actions Strip -->
            <section class="actions-strip">
                <Link :href="route('links.create')" class="action-tile">
                    <div class="action-tile__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.plus" />
                    </div>
                    <div class="action-tile__text">
                        <strong>Create Link</strong>
                        <span>Shorten a URL</span>
                    </div>
                    <svg class="action-tile__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" v-html="icons.arrow" />
                </Link>
                <div class="actions-divider"></div>
                <Link :href="route('links.bulk')" class="action-tile">
                    <div class="action-tile__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.bolt" />
                    </div>
                    <div class="action-tile__text">
                        <strong>Bulk Shorten</strong>
                        <span>Multiple URLs at once</span>
                    </div>
                    <svg class="action-tile__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" v-html="icons.arrow" />
                </Link>
                <div class="actions-divider"></div>
                <Link :href="route('affiliate.index')" class="action-tile">
                    <div class="action-tile__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div class="action-tile__text">
                        <strong>Affiliate</strong>
                        <span>Earn commissions</span>
                    </div>
                    <svg class="action-tile__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" v-html="icons.arrow" />
                </Link>
            </section>

            <!-- Recent Links -->
            <section class="links-section">
                <div class="section-header">
                    <div>
                        <span class="section-marker">Recent Links</span>
                        <p class="section-sub">Your 5 latest short URLs</p>
                    </div>
                    <Link :href="route('links.index')" class="view-all-btn">
                        View all
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            v-html="icons.arrow" />
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-if="recentLinks.length === 0" class="empty-state">
                    <div class="empty-state__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.link" />
                    </div>
                    <p class="empty-state__title">No links yet</p>
                    <p class="empty-state__text">Create your first short link and start tracking clicks.</p>
                    <Link :href="route('links.create')" class="create-btn create-btn--sm">
                        Create your first link
                    </Link>
                </div>

                <!-- Table -->
                <div v-else class="links-table">
                    <div class="links-table__head">
                        <span>Short URL</span>
                        <span>Destination</span>
                        <span class="col-center">Clicks</span>
                        <span class="col-center">Created</span>
                        <span></span>
                    </div>
                    <div v-for="link in recentLinks" :key="link.id" class="link-row">
                        <div class="link-row__short">
                            <a :href="`/${link.short_code}`" target="_blank" class="short-link">
                                <span class="short-link__slash">/</span>{{ link.short_code }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    class="short-link__ext" v-html="icons.external" />
                            </a>
                        </div>
                        <div class="link-row__dest">
                            <span :title="link.destination_url">{{ link.destination_url }}</span>
                        </div>
                        <div class="link-row__clicks col-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.chart" />
                            {{ link.clicks_count.toLocaleString() }}
                        </div>
                        <div class="link-row__date col-center">{{ formatDate(link.created_at) }}</div>
                        <div class="link-row__actions">
                            <Link :href="route('links.show', link.id)" class="icon-btn icon-btn--chart"
                                title="Analytics">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    v-html="icons.chart" />
                            </Link>
                            <Link :href="route('links.edit', link.id)" class="icon-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    v-html="icons.edit" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ── Dashboard: Editorial Light ──────────────── */
.dashboard {
    max-width: 1000px;
    animation: fadeUp 0.4s ease both;
}

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
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--red);
    display: block;
    margin-bottom: 8px;
}

.page-header__title {
    font-family: var(--font-display);
    font-size: 24px;
    font-weight: 600;
    letter-spacing: -0.3px;
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

.create-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    background: var(--red);
    color: #fff;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 1px;
    text-transform: uppercase;
    text-decoration: none;
    transition: var(--transition);
    flex-shrink: 0;
}

.create-btn svg {
    width: 14px;
    height: 14px;
}

.create-btn:hover {
    background: var(--red-dark);
    transform: translateY(-1px);
}

.create-btn--sm {
    padding: 8px 16px;
    font-size: 11px;
    margin-top: 12px;
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
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.stat-card {
    background: var(--surface);
    padding: 20px 18px 16px;
    display: flex;
    flex-direction: column;
    transition: background 0.15s ease;
    position: relative;
}

.stat-card:hover {
    background: #fdf9f5;
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
    letter-spacing: 1px;
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

.stat-card__icon-wrap--links {
    background: #fef2f2;
    color: var(--red);
}

.stat-card__icon-wrap--clicks {
    background: #eff6ff;
    color: #3b82f6;
}

.stat-card__icon-wrap--today {
    background: #f0fdf4;
    color: #16a34a;
}

.stat-card__icon-wrap--active {
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
    letter-spacing: 1px;
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
    letter-spacing: 0.3px;
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
    letter-spacing: 1.5px;
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

.view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--red);
    text-decoration: none;
    transition: var(--transition);
}

.view-all-btn svg {
    width: 13px;
    height: 13px;
    transition: transform 0.15s ease;
}

.view-all-btn:hover {
    color: var(--red-dark);
}

.view-all-btn:hover svg {
    transform: translateX(2px);
}

/* ── Empty State ──────────────────────────── */
.empty-state {
    background: var(--surface);
    border: 1px dashed var(--border);
    border-radius: var(--radius);
    padding: 48px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-state__icon {
    width: 44px;
    height: 44px;
    background: var(--surface-2);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}

.empty-state__icon svg {
    width: 20px;
    height: 20px;
    color: var(--muted);
}

.empty-state__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0 0 6px;
}

.empty-state__text {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

/* ── Links Table ──────────────────────────── */
.links-table {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.links-table__head {
    display: grid;
    grid-template-columns: 130px 1fr 90px 90px 72px;
    gap: 12px;
    padding: 10px 16px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
}

.links-table__head span {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--muted);
}

.col-center {
    text-align: center;
    justify-content: center;
}

.link-row {
    display: grid;
    grid-template-columns: 130px 1fr 90px 90px 72px;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    align-items: center;
    transition: background 0.15s ease;
}

.link-row:last-child {
    border-bottom: none;
}

.link-row:hover {
    background: #fdf9f5;
}

.link-row>div {
    min-width: 0;
}

.short-link {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--red);
    text-decoration: none;
    letter-spacing: 0.3px;
    transition: color 0.15s;
}

.short-link__slash {
    color: var(--border);
    font-weight: 400;
    margin-right: 1px;
}

.short-link__ext {
    width: 11px;
    height: 11px;
    opacity: 0;
    margin-left: 3px;
    transition: opacity 0.15s;
}

.short-link:hover .short-link__ext {
    opacity: 1;
}

.short-link:hover {
    color: var(--red-dark);
}

.link-row__dest span {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

.link-row__clicks {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
}

.link-row__clicks svg {
    width: 13px;
    height: 13px;
    color: #16a34a;
}

.link-row__date {
    font-family: var(--font-display);
    font-size: 11px;
    color: var(--muted);
    letter-spacing: 0.3px;
    text-align: center;
}

.link-row__actions {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

.icon-btn {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    color: var(--muted);
    transition: var(--transition);
}

.icon-btn svg {
    width: 13px;
    height: 13px;
}

.icon-btn:hover {
    background: var(--border);
    color: var(--ink);
}

.icon-btn--chart {
    background: #eff6ff;
    color: #3b82f6;
}

.icon-btn--chart:hover {
    background: #dbeafe;
    color: #1d4ed8;
}

/* ── Responsive ───────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .links-table__head,
    .link-row {
        grid-template-columns: 110px 1fr 80px 80px 64px;
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

    .links-table__head {
        display: none;
    }

    .link-row {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: auto auto auto;
        gap: 8px;
        padding: 14px;
    }

    .link-row__short {
        grid-column: 1 / -1;
    }

    .link-row__dest {
        grid-column: 1 / -1;
    }

    .link-row__date {
        text-align: left;
        justify-content: flex-start;
    }

    .link-row__actions {
        justify-content: flex-start;
    }
}
</style>
