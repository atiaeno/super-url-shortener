// © Atia Hegazy — atiaeno.com
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    reports: Object,
    stats: Object,
    filters: Object,
    reviewers: Array,
});

const showReportModal = ref(false);
const selectedReport = ref(null);
const reviewForm = ref({
    action: '',
    notes: ''
});
const isSubmitting = ref(false);

const icons = {
    report: `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>`,
    check: `<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>`,
    eye: `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`,
    link: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
};

const openReportModal = (report) => {
    selectedReport.value = report;
    showReportModal.value = true;
};

const closeReportModal = () => {
    showReportModal.value = false;
    selectedReport.value = null;
    reviewForm.value = {
        action: '',
        notes: ''
    };
};

const submitReview = async () => {
    if (!selectedReport.value || !reviewForm.value.action) {
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.post(`/admin/moderation/review/${selectedReport.value.id}`, {
            action: reviewForm.value.action,
            notes: reviewForm.value.notes
        });

        // Close modal and refresh page
        closeReportModal();
        window.location.reload();
    } catch (error) {
        console.error('Error submitting review:', error);
        alert('Error submitting review. Please try again.');
    } finally {
        isSubmitting.value = false;
    }
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const reasonLabels = {
    spam: 'Spam',
    phishing: 'Phishing',
    malware: 'Malware',
    violence: 'Violence',
    other: 'Other',
};

const statusColors = {
    pending: 'status-badge--pending',
    reviewed: 'status-badge--reviewed',
    actioned: 'status-badge--actioned',
    dismissed: 'status-badge--dismissed',
};

const statItems = [
    { id: 'pending', label: 'Pending Reports', value: 0, roman: 'I.', icon: icons.report, color: 'yellow' },
    { id: 'reviewed', label: 'Reviewed', value: 0, roman: 'II.', icon: icons.check, color: 'green' },
    { id: 'today', label: 'Today', value: 0, roman: 'III.', icon: icons.report, color: 'blue' },
];

// Filter form data
const filterForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || 'all',
    reason: props.filters?.reason || 'all',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    reviewer_id: props.filters?.reviewer_id || 'all',
});

// Apply filters
const applyFilters = () => {
    const params = new URLSearchParams();

    Object.entries(filterForm.value).forEach(([key, value]) => {
        if (value && value !== 'all') {
            params.append(key, value);
        }
    });

    window.location.href = `/admin/moderation/reports${params.toString() ? '?' + params.toString() : ''}`;
};

// Clear filters
const clearFilters = () => {
    filterForm.value = {
        search: '',
        status: 'all',
        reason: 'all',
        date_from: '',
        date_to: '',
        reviewer_id: 'all',
    };
    window.location.href = '/admin/moderation/reports';
};

// Filter options
const statusOptions = [
    { value: 'all', label: 'All Status' },
    { value: 'pending', label: 'Pending' },
    { value: 'reviewed', label: 'Reviewed' },
    { value: 'actioned', label: 'Actioned' },
    { value: 'dismissed', label: 'Dismissed' },
];

const reasonOptions = [
    { value: 'all', label: 'All Reasons' },
    { value: 'spam', label: 'Spam' },
    { value: 'phishing', label: 'Phishing' },
    { value: 'malware', label: 'Malware' },
    { value: 'violence', label: 'Violence' },
    { value: 'other', label: 'Other' },
];
</script>

<template>

    <Head title="Report Queue" />
    <AdminLayout>
        <template #header-icon>
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <polyline points="10 9 9 9 8 9" />
        </template>
        <template #header>Report Queue</template>

        <div class="reports-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Content Moderation</span>
                    <h1 class="page-header__title">All Reports</h1>
                    <p class="page-header__sub">Review and manage user-reported content.</p>
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

                    <div class="filter-bar__divider"></div>

                    <div class="filter-bar__controls">
                        <div class="filter-chip">
                            <span class="filter-chip__label">Status</span>
                            <select v-model="filterForm.status" class="filter-chip__select">
                                <option v-for="o in statusOptions" :key="o.value" :value="o.value">{{ o.label }}
                                </option>
                            </select>
                        </div>

                        <div class="filter-chip">
                            <span class="filter-chip__label">Reason</span>
                            <select v-model="filterForm.reason" class="filter-chip__select">
                                <option v-for="o in reasonOptions" :key="o.value" :value="o.value">{{ o.label }}
                                </option>
                            </select>
                        </div>

                        <div class="filter-chip">
                            <span class="filter-chip__label">Reviewer</span>
                            <select v-model="filterForm.reviewer_id" class="filter-chip__select">
                                <option value="all">All</option>
                                <option v-for="r in reviewers" :key="r.id" :value="r.id">{{ r.name }}</option>
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

            <!-- Reports Table -->
            <section class="table-section">
                <div class="table-card">

                    <div v-if="!reports.data?.length" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.check" />
                        </div>
                        <p class="empty-state__title">No reports found</p>
                        <p class="empty-state__text">All reports have been reviewed.</p>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>Link</th>
                                    <th>Reason</th>
                                    <th class="col-center">Status</th>
                                    <th class="col-center">Date</th>
                                    <th class="col-center">Reviewed By</th>
                                    <th class="col-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="report in reports.data" :key="report.id">
                                    <td>
                                        <div class="link-cell">
                                            <div class="link-info">
                                                <Link :href="`/admin/links/${report.link?.id}/edit`" class="link-code">
                                                    {{ report.link?.short_code }}
                                                </Link>
                                                <a :href="report.link?.url" target="_blank" class="link-url">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.5" class="link-icon">
                                                        <path
                                                            d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                                        <path
                                                            d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="reason-badge">{{ reasonLabels[report.reason] || report.reason
                                            }}</span>
                                    </td>
                                    <td class="col-center">
                                        <span class="status-badge" :class="statusColors[report.status]">
                                            {{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <span class="date-cell">{{ formatDate(report.created_at) }}</span>
                                    </td>
                                    <td class="col-center">
                                        <span class="reviewer-cell">{{ report.reviewer?.name || '-' }}</span>
                                    </td>
                                    <td class="col-center">
                                        <div class="actions-cell">
                                            <button @click="openReportModal(report)" class="btn-icon btn-icon--view"
                                                title="Review Report">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="reports.last_page > 1" class="pagination">
                            <template v-for="(link, i) in reports.links" :key="i">
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

        <!-- Report Modal -->
        <div v-if="showReportModal" class="modal-backdrop" @click="closeReportModal">
            <div class="modal" @click.stop>
                <div class="modal__header">
                    <h3 class="modal__title">Report Details</h3>
                    <button @click="closeReportModal" class="modal__close">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <div class="modal__body" v-if="selectedReport">
                    <div class="report-details">
                        <div class="detail-row">
                            <span class="detail-label">Report ID:</span>
                            <span class="detail-value">#{{ selectedReport.id }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Link Code:</span>
                            <span class="detail-value">
                                <a :href="selectedReport.link?.url" target="_blank" class="detail-link">
                                    {{ selectedReport.link?.short_code }}
                                </a>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason:</span>
                            <span class="detail-value">{{ reasonLabels[selectedReport.reason] || selectedReport.reason
                                }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="status-badge" :class="statusColors[selectedReport.status]">
                                {{ selectedReport.status.charAt(0).toUpperCase() + selectedReport.status.slice(1) }}
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reported Date:</span>
                            <span class="detail-value">{{ formatDate(selectedReport.created_at) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reviewed By:</span>
                            <span class="detail-value">{{ selectedReport.reviewer?.name || 'Not reviewed' }}</span>
                        </div>
                        <div class="detail-row" v-if="selectedReport.notes">
                            <span class="detail-label">Notes:</span>
                            <span class="detail-value">{{ selectedReport.notes }}</span>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="submitReview">
                    <div class="review-actions">
                        <div class="review-actions__label">Action:</div>
                        <div class="review-actions__options">
                            <label class="radio-option">
                                <input type="radio" v-model="reviewForm.action" value="dismiss" required>
                                <span class="radio-option__label">Dismiss Report</span>
                                <span class="radio-option__desc">Report is invalid</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" v-model="reviewForm.action" value="deactivate" required>
                                <span class="radio-option__label">Deactivate Link</span>
                                <span class="radio-option__desc">Disable the link</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" v-model="reviewForm.action" value="delete" required>
                                <span class="radio-option__label">Delete Link</span>
                                <span class="radio-option__desc">Remove permanently</span>
                            </label>
                        </div>
                    </div>

                    <div class="review-notes">
                        <label class="review-notes__label">Notes (optional):</label>
                        <textarea v-model="reviewForm.notes" class="review-notes__textarea"
                            placeholder="Add notes about this review..." rows="3"></textarea>
                    </div>

                    <div class="modal__footer">
                        <button type="button" @click="closeReportModal" class="btn-ghost">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="!reviewForm.action || isSubmitting">
                            {{ isSubmitting ? 'Submitting...' : 'Submit Review' }}
                        </button>
                    </div>
                </form>
            </div>
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

.stat-card--blue {
    background: var(--surface);
}

.stat-card--yellow {
    background: var(--surface);
}

.stat-card--green {
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
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card--blue .stat-card__icon {
    background: var(--surface-2);
    color: var(--muted);
}

.stat-card--yellow .stat-card__icon {
    background: #fef3c7;
    color: #92400e;
}

.stat-card--green .stat-card__icon {
    background: #dcfce7;
    color: #166534;
}

.stat-card__icon svg {
    width: 14px;
    height: 14px;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 400;
    color: var(--ink);
    margin-bottom: 2px;
}

.stat-card__label {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
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

.filter-bar__divider {
    display: none;
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

/* ── Table Section ───────────────────────── */
.table-section {
    margin-bottom: 32px;
}

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state__icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 20px;
    color: var(--muted);
    opacity: 0.5;
}

.empty-state__icon svg {
    width: 100%;
    height: 100%;
}

.empty-state__title {
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 500;
    color: var(--ink);
    margin: 0 0 8px;
}

.empty-state__text {
    font-family: var(--font-body);
    font-size: 14px;
    font-style: italic;
    color: var(--muted);
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.reports-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-body);
    font-size: 14px;
}

.reports-table th {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    padding: 14px 16px;
    text-align: left;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

.reports-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.reports-table tr:last-child td {
    border-bottom: none;
}

.reports-table tr:hover td {
    background: var(--surface-2);
}

.col-center {
    text-align: center;
}

.link-cell {
    display: flex;
    align-items: center;
}

.link-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.link-code {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 13px;
    color: #000000;
    text-decoration: none;
    transition: color 0.2s;
}

.link-code:hover {
    color: var(--primary);
}

.link-url {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    color: var(--muted);
    text-decoration: none;
    transition: color 0.2s;
}

.link-url:hover {
    color: var(--primary);
}

.link-icon {
    width: 16px;
    height: 16px;
}

.reason-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 400;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: var(--radius);
    background: var(--surface-2);
    color: #000000;
    white-space: nowrap;
}

.status-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 400;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: var(--radius);
    white-space: nowrap;
}

.status-badge--pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge--reviewed {
    background: #dcfce7;
    color: #166534;
}

.status-badge--actioned {
    background: #fee2e2;
    color: var(--primary);
}

.status-badge--dismissed {
    background: var(--surface-2);
    color: var(--muted);
}

.date-cell {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
}

.reviewer-cell {
    font-family: var(--font-body);
    font-size: 12px;
    color: #333333;
}

.actions-cell {
    display: flex;
    gap: 6px;
    justify-content: center;
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: #333333;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-icon:hover {
    background: var(--surface-2);
    color: #000000;
}

.btn-icon svg {
    width: 14px;
    height: 14px;
}

.btn-icon--view {
    background: var(--surface-2);
    color: #333333;
    border-color: var(--border);
}

.btn-icon--view:hover {
    background: var(--border);
    color: #000000;
}

/* ── Pagination ────────────────────────── */
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    padding: 16px;
    border-top: 1px solid var(--border);
    background: var(--surface-2);
}

.pagination__link {
    min-width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: #333333;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 400;
    text-decoration: none;
    transition: var(--transition);
}

.pagination__link:hover {
    background: var(--surface-2);
    color: #000000;
}

.pagination__link--active {
    background: var(--primary);
    color: var(--surface);
    border-color: var(--primary);
}

.pagination__link--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Modal Styles ───────────────────────── */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--surface);
    border-radius: var(--radius);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.modal__header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal__title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 500;
    color: var(--ink);
    margin: 0;
}

.modal__close {
    background: none;
    border: none;
    color: var(--muted);
    cursor: pointer;
    padding: 4px;
    border-radius: var(--radius);
    transition: color 0.2s;
}

.modal__close:hover {
    color: var(--ink);
}

.modal__body {
    padding: 24px;
    max-height: 60vh;
    overflow-y: auto;
}

.report-details {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-label {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
}

.detail-value {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink);
    text-align: right;
}

.detail-link {
    color: var(--primary);
    text-decoration: none;
    transition: color 0.2s;
}

.detail-link:hover {
    color: var(--primary-dark);
}

.modal__footer {
    padding: 20px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-ghost {
    background: none;
    border: 1px solid var(--border);
    color: var(--ink);
    padding: 8px 16px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-primary {
    background: var(--primary);
    border: 1px solid var(--primary);
    color: white;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
}

/* Review Form Styles */
.review-actions {
    margin-bottom: 24px;
}

.review-actions__label {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
    display: block;
    margin-bottom: 12px;
}

.review-actions__options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.radio-option {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
}

.radio-option:hover {
    background: var(--surface-2);
    border-color: var(--primary);
}

.radio-option input[type="radio"] {
    margin: 0;
    flex-shrink: 0;
    margin-top: 2px;
}

.radio-option__label {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 500;
    color: var(--ink);
    display: block;
    margin-bottom: 2px;
}

.radio-option__desc {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
}

.review-notes {
    margin-bottom: 24px;
}

.review-notes__label {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
    display: block;
    margin-bottom: 8px;
}

.review-notes__textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink);
    resize: vertical;
    transition: var(--transition);
}

.review-notes__textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.1);
}

.review-notes__textarea::placeholder {
    color: var(--muted);
    opacity: 0.7;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .modal {
        width: 95%;
        margin: 20px;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .detail-value {
        text-align: left;
    }
}

@media (max-width: 1024px) {
    .filters-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
