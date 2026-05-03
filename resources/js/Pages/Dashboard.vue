<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

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
    { id: 'links', label: 'Total Links', value: props.stats.total_links, icon: 'link' },
    { id: 'clicks', label: 'Total Clicks', value: props.stats.total_clicks, icon: 'activity' },
    { id: 'today', label: 'Clicks Today', value: props.stats.clicks_today, icon: 'clock' },
    { id: 'active', label: 'Active Links', value: props.stats.active_links, icon: 'check' },
]);

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const icons = {
    link: `<path d="M9.5 14.5a4.5 4.5 0 0 1 6.36-6.36l3.5 3.5a4.5 4.5 0 0 1-6.36 6.36l-1.5-1.5"/><path d="M14.5 9.5a4.5 4.5 0 0 0-6.36 6.36l3.5 3.5a4.5 4.5 0 0 0 6.36-6.36l-1.5-1.5"/>`,
    activity: `<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>`,
    clock: `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
    check: `<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>`,
    plus: `<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>`,
    external: `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>`,
    chart: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>Dashboard</template>

        <div class="dashboard">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h1 class="welcome-title">Welcome back</h1>
                <p class="welcome-subtitle">Here's what's happening with your links today.</p>
            </section>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div
                        v-for="item in statItems"
                        :key="item.id"
                        class="stat-card"
                    >
                        <div class="stat-card__icon" :class="`stat-card__icon--${item.id}`">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="icons[item.icon]" />
                        </div>
                        <div class="stat-card__content">
                            <span class="stat-card__value">{{ item.value.toLocaleString() }}</span>
                            <span class="stat-card__label">{{ item.label }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Create CTA -->
            <section class="cta-section">
                <div class="cta-card">
                    <div class="cta-card__content">
                        <div class="cta-card__icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="icons.plus" />
                        </div>
                        <div class="cta-card__text">
                            <h3 class="cta-card__title">Create a new short link</h3>
                            <p class="cta-card__desc">Transform long URLs into memorable, trackable links.</p>
                        </div>
                    </div>
                    <Link :href="route('links.create')" class="cta-card__btn">
                        Get Started
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.arrow" />
                    </Link>
                </div>
            </section>

            <!-- Recent Links -->
            <section class="links-section">
                <div class="section-header">
                    <div class="section-title-wrap">
                        <h2 class="section-title">Recent Links</h2>
                        <p class="section-subtitle">Your latest created short URLs</p>
                    </div>
                    <Link :href="route('links.index')" class="section-link">
                        View all links
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.arrow" />
                    </Link>
                </div>

                <div v-if="recentLinks.length === 0" class="empty-state">
                    <div class="empty-state__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="icons.link" />
                    </div>
                    <h3 class="empty-state__title">No links yet</h3>
                    <p class="empty-state__text">Create your first short link and start tracking clicks.</p>
                    <Link :href="route('links.create')" class="empty-state__btn">
                        Create your first link
                    </Link>
                </div>

                <div v-else class="links-table">
                    <div class="links-table__header">
                        <span class="links-table__col links-table__col--url">Short URL</span>
                        <span class="links-table__col links-table__col--dest">Destination</span>
                        <span class="links-table__col links-table__col--clicks">Clicks</span>
                        <span class="links-table__col links-table__col--date">Created</span>
                        <span class="links-table__col links-table__col--actions"></span>
                    </div>
                    <div class="links-table__body">
                        <div
                            v-for="link in recentLinks"
                            :key="link.id"
                            class="link-row"
                        >
                            <div class="link-row__col link-row__col--url">
                                <a :href="`/${link.short_code}`" target="_blank" class="short-url">
                                    <span class="short-url__domain">short.link</span>
                                    <span class="short-url__slash">/</span>
                                    <span class="short-url__code">{{ link.short_code }}</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.external" />
                                </a>
                            </div>
                            <div class="link-row__col link-row__col--dest">
                                <span class="dest-url" :title="link.destination_url">{{ link.destination_url }}</span>
                            </div>
                            <div class="link-row__col link-row__col--clicks">
                                <span class="click-count">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.chart" />
                                    {{ link.clicks_count.toLocaleString() }}
                                </span>
                            </div>
                            <div class="link-row__col link-row__col--date">
                                <span class="created-date">{{ formatDate(link.created_at) }}</span>
                            </div>
                            <div class="link-row__col link-row__col--actions">
                                <div class="action-btns">
                                    <Link :href="route('links.show', link.id)" class="action-btn action-btn--primary" title="Analytics">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.chart" />
                                    </Link>
                                    <Link :href="route('links.edit', link.id)" class="action-btn" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.edit" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ── Dashboard: Swiss Sophistication ──────────────── */

.dashboard {
    max-width: 960px;
}

/* ── Welcome Section ─────────────────────────────── */
.welcome-section {
    margin-bottom: 32px;
}

.welcome-title {
    font-family: var(--font-display, 'Sora', sans-serif);
    font-size: 32px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
}

.welcome-subtitle {
    font-family: var(--font-body, 'Newsreader', serif);
    font-size: 17px;
    font-style: italic;
    color: #6b7280;
    margin: 0;
}

/* ── Stats Section ──────────────────────────────── */
.stats-section {
    margin-bottom: 32px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    border-color: #d1d5db;
    box-shadow: 0 4px 20px rgba(10, 22, 40, 0.06);
    transform: translateY(-2px);
}

.stat-card__icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-card__icon svg {
    width: 24px;
    height: 24px;
}

.stat-card__icon--links {
    background: linear-gradient(135deg, #fef3f2 0%, #fee4e2 100%);
    color: #e85d4e;
}

.stat-card__icon--clicks {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #3b82f6;
}

.stat-card__icon--today {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    color: #22c55e;
}

.stat-card__icon--active {
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
    color: #a855f7;
}

.stat-card__content {
    display: flex;
    flex-direction: column;
}

.stat-card__value {
    font-family: var(--font-display, 'Sora', sans-serif);
    font-size: 28px;
    font-weight: 600;
    color: #111827;
    line-height: 1.2;
}

.stat-card__label {
    font-size: 13px;
    color: #9ca3af;
    margin-top: 4px;
}

/* ── CTA Section ──────────────────────────────── */
.cta-section {
    margin-bottom: 40px;
}

.cta-card {
    background: linear-gradient(135deg, #0a1628 0%, #111d32 100%);
    border-radius: 20px;
    padding: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    position: relative;
    overflow: hidden;
}

.cta-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(ellipse at center, rgba(232, 93, 78, 0.2) 0%, transparent 70%);
    pointer-events: none;
}

.cta-card__content {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    z-index: 1;
}

.cta-card__icon-wrap {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #e85d4e 0%, #f07062 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(232, 93, 78, 0.3);
}

.cta-card__icon-wrap svg {
    width: 28px;
    height: 28px;
    color: #fff;
}

.cta-card__text {
    display: flex;
    flex-direction: column;
}

.cta-card__title {
    font-family: var(--font-display, 'Sora', sans-serif);
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 4px 0;
}

.cta-card__desc {
    font-size: 14px;
    color: #9ca3af;
    margin: 0;
}

.cta-card__btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 24px;
    background: #fff;
    color: #0a1628;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
    flex-shrink: 0;
}

.cta-card__btn:hover {
    background: #f5f5f5;
    transform: translateX(4px);
}

.cta-card__btn svg {
    width: 18px;
    height: 18px;
    transition: transform 0.3s ease;
}

.cta-card__btn:hover svg {
    transform: translateX(2px);
}

/* ── Links Section ──────────────────────────────── */
.links-section {
    margin-bottom: 32px;
}

.section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 20px;
}

.section-title-wrap {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.section-title {
    font-family: var(--font-display, 'Sora', sans-serif);
    font-size: 20px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.section-subtitle {
    font-family: var(--font-body, 'Newsreader', serif);
    font-size: 14px;
    font-style: italic;
    color: #9ca3af;
    margin: 0;
}

.section-link {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #e85d4e;
    text-decoration: none;
    transition: all 0.2s ease;
}

.section-link:hover {
    color: #f07062;
    gap: 10px;
}

.section-link svg {
    width: 16px;
    height: 16px;
}

/* ── Links Table ──────────────────────────────── */
.links-table {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
}

.links-table__header {
    display: grid;
    grid-template-columns: 140px 1fr 100px 100px 100px;
    gap: 16px;
    padding: 16px 24px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.links-table__col {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #9ca3af;
}

.links-table__body {
    display: flex;
    flex-direction: column;
}

.link-row {
    display: grid;
    grid-template-columns: 140px 1fr 100px 100px 100px;
    gap: 16px;
    padding: 16px 24px;
    border-bottom: 1px solid #f3f4f6;
    align-items: center;
    transition: background 0.2s ease;
}

.link-row:last-child {
    border-bottom: none;
}

.link-row:hover {
    background: #fafafa;
}

.link-row__col {
    min-width: 0;
}

.short-url {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.short-url__domain {
    color: #9ca3af;
}

.short-url__slash {
    color: #d1d5db;
}

.short-url__code {
    color: #e85d4e;
}

.short-url:hover .short-url__code {
    text-decoration: underline;
}

.short-url svg {
    width: 14px;
    height: 14px;
    color: #d1d5db;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.short-url:hover svg {
    opacity: 1;
}

.dest-url {
    font-size: 14px;
    color: #6b7280;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

.click-count {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #111827;
}

.click-count svg {
    width: 16px;
    height: 16px;
    color: #22c55e;
}

.created-date {
    font-size: 14px;
    color: #9ca3af;
}

.action-btns {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    color: #6b7280;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: #e5e7eb;
    color: #111827;
}

.action-btn--primary {
    background: #eff6ff;
    color: #3b82f6;
}

.action-btn--primary:hover {
    background: #dbeafe;
    color: #2563eb;
}

.action-btn svg {
    width: 18px;
    height: 18px;
}

/* ── Empty State ──────────────────────────────── */
.empty-state {
    background: #fff;
    border: 1px dashed #d1d5db;
    border-radius: 16px;
    padding: 64px 32px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-state__icon {
    width: 64px;
    height: 64px;
    background: #f3f4f6;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.empty-state__icon svg {
    width: 32px;
    height: 32px;
    color: #9ca3af;
}

.empty-state__title {
    font-family: var(--font-display, 'Sora', sans-serif);
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
}

.empty-state__text {
    font-size: 14px;
    color: #9ca3af;
    margin: 0 0 24px 0;
}

.empty-state__btn {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    background: #e85d4e;
    color: #fff;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.empty-state__btn:hover {
    background: #f07062;
    transform: translateY(-2px);
}

/* ── Responsive ────────────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .links-table__header,
    .link-row {
        grid-template-columns: 120px 1fr 80px 80px 90px;
    }
}

@media (max-width: 768px) {
    .welcome-title {
        font-size: 24px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .stat-card {
        padding: 20px;
    }

    .stat-card__value {
        font-size: 24px;
    }

    .cta-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
        padding: 24px;
    }

    .cta-card__btn {
        width: 100%;
        justify-content: center;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .links-table {
        border-radius: 12px;
    }

    .links-table__header {
        display: none;
    }

    .link-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 20px;
    }

    .link-row__col--url,
    .link-row__col--dest {
        width: 100%;
    }

    .link-row__col--clicks,
    .link-row__col--date,
    .link-row__col--actions {
        display: flex;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .link-row__col--clicks::before,
    .link-row__col--date::before {
        content: attr(data-label);
        font-size: 12px;
        color: #9ca3af;
        font-weight: 500;
    }
}
</style>
