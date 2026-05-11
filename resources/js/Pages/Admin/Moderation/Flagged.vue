// © Atia Hegazy — atiaeno.com
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    flaggedLinks: Object,
    stats: Object,
    filters: Object,
});

const icons = {
    flag: `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="4" y1="20" x2="20" y2="20"/><line x1="4" y1="12" x2="20" y2="12"/>`,
    check: `<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>`,
    eye: `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    trash: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>`,
    link: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
};

const statItems = [
    { id: 'flagged', label: 'Flagged Links', value: 0, roman: 'I.', icon: icons.flag, color: 'red' },
    { id: 'auto_suspended', label: 'Auto Suspended', value: 0, roman: 'II.', icon: icons.flag, color: 'yellow' },
    { id: 'high_priority', label: 'High Priority', value: 0, roman: 'III.', icon: icons.flag, color: 'orange' },
];

// Filter form data
const filterForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || 'all',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

// Apply filters
const applyFilters = () => {
    const params = new URLSearchParams();
    Object.entries(filterForm.value).forEach(([key, value]) => {
        if (value && value !== 'all') {
            params.append(key, value);
        }
    });
    window.location.href = `/admin/moderation/flagged${params.toString() ? '?' + params.toString() : ''}`;
};

// Clear filters
const clearFilters = () => {
    filterForm.value = {
        search: '',
        status: 'all',
        date_from: '',
        date_to: '',
    };
    window.location.href = '/admin/moderation/flagged';
};

// Filter options
const statusOptions = [
    { value: 'all', label: 'All Status' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const deleteLink = (linkId) => {
    if (confirm('Are you sure you want to delete this link permanently?')) {
        window.location.href = `/admin/links/${linkId}`;
    }
};
</script>

<template>

    <Head title="Flagged Links" />
    <AdminLayout>
        <template #header-icon>
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="4" y1="20" x2="20" y2="20" />
            <line x1="4" y1="12" x2="20" y2="12" />
        </template>
        <template #header>Flagged Links</template>

        <div class="flagged-page">
            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Content Moderation</span>
                    <h1 class="page-header__title">Flagged Links</h1>
                    <p class="page-header__sub">Review and manage links with automatic flags.</p>
                </div>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div v-for="(item, index) in statItems" :key="item.id" class="stat-card"
                        :class="`stat-card--${item.color}`">
                        <div class="stat-card__top">
                            <span class="stat-card__roman">{{ item.roman }}</span>
                            <div class="stat-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="item.icon" />
                            </div>
                        </div>
                        <div class="stat-card__value">{{ stats[item.id] || 0 }}</div>
                        <div class="stat-card__label">{{ item.label }}</div>
                    </div>
                </div>
            </section>

            <!-- Filters Section -->
            <section class="filters-section">
                <form @submit.prevent="applyFilters" class="filter-bar">
                    <div class="filter-bar__search">
                        <svg class="filter-bar__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input v-model="filterForm.search" type="text" class="filter-bar__input"
                            placeholder="Search link code or URL…">
                    </div>

                    <div class="filter-bar__controls">
                        <div class="filter-chip">
                            <span class="filter-chip__label">Status</span>
                            <select v-model="filterForm.status" class="filter-chip__select">
                                <option v-for="o in statusOptions" :key="o.value" :value="o.value">{{ o.label }}
                                </option>
                            </select>
                        </div>

                        <div class="filter-chip filter-chip--date">
                            <span class="filter-chip__label">From</span>
                            <input v-model="filterForm.date_from" type="date" class="filter-chip__date">
                        </div>

                        <div class="filter-chip filter-chip--date">
                            <span class="filter-chip__label">To</span>
                            <input v-model="filterForm.date_to" type="date" class="filter-chip__date">
                        </div>
                    </div>

                    <div class="filter-bar__actions">
                        <button type="button" @click="clearFilters" class="filter-bar__clear">Reset</button>
                        <button type="submit" class="filter-bar__apply">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                            </svg>
                            Apply
                        </button>
                    </div>
                </form>
            </section>

            <!-- Table Section -->
            <section class="table-section">
                <div class="table-card">

                    <div v-if="!flaggedLinks.data?.length" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.check" />
                        </div>
                        <p class="empty-state__title">No flagged links found</p>
                        <p class="empty-state__text">All links are clean.</p>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="flagged-table">
                            <thead>
                                <tr>
                                    <th>Link</th>
                                    <th class="col-center">Reports</th>
                                    <th class="col-center">Status</th>
                                    <th class="col-center">Created</th>
                                    <th class="col-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="link in flaggedLinks.data" :key="link.id">
                                    <td>
                                        <div class="link-cell">
                                            <div class="link-info">
                                                <Link :href="`/admin/links/${link.id}/edit`" class="link-code">
                                                    {{ link.short_code }}
                                                </Link>
                                                <a :href="link.url" target="_blank" class="link-url">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.5" class="link-icon">
                                                        <path
                                                            d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                                        <path
                                                            d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <div class="link-target">{{ link.url }}</div>
                                        </div>
                                    </td>
                                    <td class="col-center">
                                        <span class="report-count">{{ link.reports_count }}</span>
                                    </td>
                                    <td class="col-center">
                                        <span class="status-badge"
                                            :class="link.is_active ? 'status-badge--active' : 'status-badge--inactive'">
                                            {{ link.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span v-if="link.auto_suspended_at" class="auto-suspended-badge">
                                            Auto Suspended
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <span class="date-cell">{{ formatDate(link.created_at) }}</span>
                                    </td>
                                    <td class="col-center">
                                        <div class="actions-cell">
                                            <Link :href="`/admin/links/${link.id}/edit`" class="btn-icon btn-icon--view"
                                                title="View Reports">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </Link>
                                            <Link :href="`/admin/links/${link.id}/edit`" class="btn-icon btn-icon--edit"
                                                title="Edit Link">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path
                                                        d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </Link>
                                            <button @click="deleteLink(link.id)" class="btn-icon btn-icon--delete"
                                                title="Delete Link">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="flaggedLinks.last_page > 1" class="pagination">
                            <template v-for="(link, i) in flaggedLinks.links" :key="i">
                                <span v-if="!link.url" class="pagination__link pagination__link--disabled"
                                    v-html="link.label" />
                                <Link v-else :href="link.url" v-html="link.label" class="pagination__link"
                                    :class="{ 'pagination__link--active': link.active }" />
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
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

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--primary) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Stats Grid ───────────────────────────── */
.stats-section {
    margin-bottom: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    transition: background 0.2s ease;
}

.stat-card--red {
    background: var(--surface);
}

.stat-card--yellow {
    background: var(--surface);
}

.stat-card--orange {
    background: var(--surface);
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
    font-weight: 500;
    color: var(--primary);
}

.stat-card__icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    border-radius: var(--radius);
    color: var(--primary);
}

.stat-card__icon svg {
    width: 18px;
    height: 18px;
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

/* ── Filter Bar ──────────────────────────── */
.filters-section {
    margin-bottom: 24px;
}

.filter-bar {
    display: flex;
    align-items: center;
    gap: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    flex-wrap: wrap;
}

.filter-bar__search {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 14px;
    min-width: 220px;
    flex: 1;
    border-right: 1px solid var(--border);
}

.filter-bar__search-icon {
    width: 15px;
    height: 15px;
    color: var(--muted);
    flex-shrink: 0;
}

.filter-bar__input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink);
    padding: 11px 0;
    min-width: 0;
}

.filter-bar__input::placeholder {
    color: var(--muted);
    opacity: 0.65;
}

.filter-bar__controls {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    border-right: 1px solid var(--border);
}

.filter-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 0 12px;
    height: 42px;
    border-right: 1px solid var(--border);
}

.filter-chip:last-child {
    border-right: none;
}

.filter-chip__label {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--muted);
    white-space: nowrap;
}

.filter-chip__select,
.filter-chip__date {
    border: none;
    outline: none;
    background: transparent;
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink);
    cursor: pointer;
    padding: 0;
    max-width: 110px;
}

.filter-chip__date {
    max-width: 120px;
    font-size: 12px;
}

.filter-bar__actions {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 12px;
}

.filter-bar__clear {
    background: #f5f0eb;
    border: 1px solid #e2d9ce;
    color: #7a6e64;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    padding: 7px 14px;
    border-radius: var(--radius);
    transition: var(--transition);
    white-space: nowrap;
}

.filter-bar__clear:hover {
    background: #ede6dc;
    border-color: #c9bfb4;
    color: #4a3f35;
}

.filter-bar__apply {
    display: flex;
    align-items: center;
    gap: 5px;
    background: var(--red);
    border: 1px solid var(--red);
    color: #fff;
    padding: 7px 14px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
}

.filter-bar__apply svg {
    width: 12px;
    height: 12px;
}

.filter-bar__apply:hover {
    background: var(--primary-dark, #c0392b);
    border-color: var(--primary-dark, #c0392b);
}

/* ── Table Section ─────────────────────────── */
.table-section {
    margin-bottom: 24px;
}

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
}

.empty-state__icon {
    margin-bottom: 16px;
    color: #22c55e;
}

.empty-state__icon svg {
    width: 48px;
    height: 48px;
}

.empty-state__title {
    font-family: var(--font-display);
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.empty-state__text {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--muted);
}

.table-wrapper {
    overflow-x: auto;
}

.flagged-table {
    width: 100%;
    border-collapse: collapse;
}

.flagged-table th {
    background: var(--surface-2);
    padding: 14px 16px;
    text-align: left;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
}

.flagged-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.flagged-table tr:hover td {
    background: var(--surface-2);
}

.col-center {
    text-align: center;
}

.link-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.link-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.link-code {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    color: var(--primary);
    text-decoration: none;
}

.link-code:hover {
    text-decoration: underline;
}

.link-url {
    display: flex;
    align-items: center;
    color: var(--muted);
    text-decoration: none;
}

.link-url:hover {
    color: var(--ink);
}

.link-icon {
    width: 14px;
    height: 14px;
}

.link-target {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
    word-break: break-all;
    margin-left: 22px;
}

.report-count {
    display: inline-block;
    padding: 4px 8px;
    background: #fef2f2;
    color: #dc2626;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    margin-right: 4px;
}

.status-badge--active {
    background: #dcfce7;
    color: #166534;
}

.status-badge--inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.auto-suspended-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #fef3c7;
    color: #92400e;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.date-cell {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink-soft);
}

.actions-cell {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-icon:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.btn-icon svg {
    width: 16px;
    height: 16px;
}

.btn-icon--view:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #3b82f6;
}

.btn-icon--edit:hover {
    background: #f0fdf4;
    border-color: #22c55e;
    color: #22c55e;
}

.btn-icon--delete:hover {
    background: #fef2f2;
    border-color: #ef4444;
    color: #ef4444;
}

/* ── Pagination ───────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 16px;
    border-top: 1px solid var(--border);
}

.pagination__link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink-soft);
    text-decoration: none;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: all 0.2s;
}

.pagination__link:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.pagination__link--active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.pagination__link--active:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
    color: white;
}

.pagination__link--disabled {
    color: var(--muted);
    cursor: not-allowed;
    opacity: 0.5;
}

.pagination__link--disabled:hover {
    background: transparent;
    color: var(--muted);
}
</style>
