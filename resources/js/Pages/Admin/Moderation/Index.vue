<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    reports: Object,
    flaggedLinks: Array,
    stats: Object,
});

const selectedReports = ref([]);
const showReviewModal = ref(false);
const reviewingReport = ref(null);
const batchMode = ref(false);

const reviewForm = useForm({
    action: 'dismiss',
    notes: '',
});

const batchForm = useForm({
    report_ids: [],
    action: 'dismiss',
    notes: '',
});

const reasonLabels = {
    spam: 'Spam',
    phishing: 'Phishing',
    malware: 'Malware',
    violence: 'Violence',
    other: 'Other',
};

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    reviewed: 'bg-blue-100 text-blue-800',
    actioned: 'bg-red-100 text-red-800',
    dismissed: 'bg-green-100 text-green-800',
};

const toggleSelectAll = () => {
    if (selectedReports.value.length === props.reports.data.length) {
        selectedReports.value = [];
    } else {
        selectedReports.value = props.reports.data.map(r => r.id);
    }
};

const openReviewModal = (report) => {
    reviewingReport.value = report;
    reviewForm.reset();
    showReviewModal.value = true;
};

const submitReview = () => {
    reviewForm.post(route('admin.moderation.review', reviewingReport.value.id), {
        onSuccess: () => {
            showReviewModal.value = false;
            reviewingReport.value = null;
        }
    });
};

const submitBatch = () => {
    batchForm.report_ids = selectedReports.value;
    batchForm.post(route('admin.moderation.batch'), {
        onSuccess: () => {
            selectedReports.value = [];
            batchMode.value = false;
        }
    });
};

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
</script>

<template>

    <Head title="Content Moderation" />
    <AdminLayout>
        <div class="dashboard">
            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Moderation</span>
                    <h1 class="page-header__title">Content Moderation</h1>
                    <p class="page-header__sub">Review and manage reported content</p>
                </div>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card stat-card--pending">
                        <span class="stat-card__value">{{ stats.pending || 0 }}</span>
                        <span class="stat-card__label">Pending Reports</span>
                    </div>
                    <div class="stat-card stat-card--reviewed">
                        <span class="stat-card__value">{{ stats.reviewed || 0 }}</span>
                        <span class="stat-card__label">Reviewed Today</span>
                    </div>
                    <div class="stat-card stat-card--actioned">
                        <span class="stat-card__value">{{ stats.actioned || 0 }}</span>
                        <span class="stat-card__label">Action Taken</span>
                    </div>
                </div>
            </section>

            <!-- Reports Section -->
            <section class="table-section">
                <div class="section-header">
                    <div>
                        <span class="section-marker">Report Queue</span>
                    </div>
                    <div class="section-actions">
                        <button v-if="selectedReports.length" @click="batchMode = true" class="btn-ghost-sm">
                            Batch Review ({{ selectedReports.length }})
                        </button>
                        <button @click="toggleSelectAll" class="btn-ghost-sm">
                            {{ selectedReports.length === reports.data.length ? 'Deselect All' : 'Select All' }}
                        </button>
                    </div>
                </div>

                <div v-if="!reports.data?.length" class="empty-state">
                    <p>No reports to review.</p>
                </div>

                <div v-else class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th class="col-checkbox"></th>
                                <th>Reason</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="report in reports.data" :key="report.id">
                                <td>
                                    <input type="checkbox" :value="report.id" v-model="selectedReports"
                                        class="checkbox" />
                                </td>
                                <td>
                                    <span class="report-reason">{{ reasonLabels[report.reason] }}</span>
                                </td>
                                <td>
                                    <div class="link-cell">
                                        <strong>{{ report.link?.short_code }}</strong>
                                        <span class="link-url">{{ report.link?.original_url }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="report-status" :class="statusColors[report.status]">
                                        {{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="report-date">{{ formatDate(report.created_at) }}</span>
                                </td>
                                <td>
                                    <button @click="openReviewModal(report)" class="btn-primary-sm">
                                        Review
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Flagged Links Section -->
            <section v-if="flaggedLinks?.length" class="table-section">
                <div class="section-header">
                    <div>
                        <span class="section-marker">Flagged Links</span>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Short Code</th>
                                <th>Original URL</th>
                                <th>Flag Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="link in flaggedLinks" :key="link.id">
                                <td>
                                    <strong>{{ link.short_code }}</strong>
                                </td>
                                <td>
                                    <span class="link-url">{{ link.original_url }}</span>
                                </td>
                                <td>
                                    <span class="flag-reason">{{ link.flag_reason }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Review Modal -->
            <div v-if="showReviewModal" class="modal-backdrop">
                <div class="modal">
                    <div class="modal__header">
                        <h3 class="modal__title">Review Report</h3>
                        <button @click="showReviewModal = false" class="modal__close">×</button>
                    </div>
                    <form @submit.prevent="submitReview">
                        <div class="modal__body">
                            <div class="report-summary">
                                <div class="report-summary__item">
                                    <span class="report-summary__label">Reason:</span>
                                    <span>{{ reasonLabels[reviewingReport?.reason] }}</span>
                                </div>
                                <div class="report-summary__item">
                                    <span class="report-summary__label">Link:</span>
                                    <span>{{ reviewingReport?.link?.short_code }}</span>
                                </div>
                                <div v-if="reviewingReport?.description" class="report-summary__item">
                                    <span class="report-summary__label">Description:</span>
                                    <span>{{ reviewingReport.description }}</span>
                                </div>
                            </div>

                            <div class="field">
                                <label class="field__label">Action</label>
                                <select v-model="reviewForm.action" class="field__input" required>
                                    <option value="dismiss">Dismiss Report</option>
                                    <option value="remove_link">Remove Link</option>
                                    <option value="ban_user">Ban User</option>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Notes (optional)</label>
                                <textarea v-model="reviewForm.notes" class="field__input" rows="3"
                                    placeholder="Add notes about this decision..."></textarea>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" @click="showReviewModal = false" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-primary" :disabled="reviewForm.processing">
                                Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Batch Review Modal -->
            <div v-if="batchMode" class="modal-backdrop">
                <div class="modal">
                    <div class="modal__header">
                        <h3 class="modal__title">Batch Review ({{ selectedReports.length }} reports)</h3>
                        <button @click="batchMode = false" class="modal__close">×</button>
                    </div>
                    <form @submit.prevent="submitBatch">
                        <div class="modal__body">
                            <div class="field">
                                <label class="field__label">Action</label>
                                <select v-model="batchForm.action" class="field__input" required>
                                    <option value="dismiss">Dismiss All</option>
                                    <option value="remove_links">Remove All Links</option>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Notes (optional)</label>
                                <textarea v-model="batchForm.notes" class="field__input" rows="3"
                                    placeholder="Add notes about this batch decision..."></textarea>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" @click="batchMode = false" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-primary" :disabled="batchForm.processing">
                                Apply to All
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

:root {
    --font-display: 'Oswald', sans-serif;
    --font-body: 'Crimson Pro', serif;
    --red: #e74c3c;
    --red-dark: #c0392b;
    --gold: #d4af37;
    --ink: #1a1a1a;
    --ink-soft: #444;
    --muted: #888;
    --border: #e8e5e0;
    --surface: #fff;
    --surface-2: #f5f3ef;
    --radius: 4px;
    --transition: all 0.2s ease;
}

.dashboard {
    max-width: 1200px;
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
    grid-template-columns: repeat(3, 1fr);
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

.stat-card--pending {
    background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 50%, #fff 100%);
}

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #eff6ff 0%, #f0f7ff 50%, #fff 100%);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #f0fdf4 0%, #f5fff0 50%, #fff 100%);
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

.admin-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 24px;
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

.section-title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
}

.section-actions {
    display: flex;
    gap: 8px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
    font-style: italic;
}

/* ── Table Section ──────────────────────── */
.table-section {
    margin-bottom: 32px;
}

.table-wrapper {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.reports-table {
    width: 100%;
    border-collapse: collapse;
}

.reports-table th {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    text-align: left;
    padding: 12px 16px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
}

.reports-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}

.reports-table tr:last-child td {
    border-bottom: none;
}

.reports-table tbody tr:hover {
    background: #fdf9f5;
}

.col-checkbox {
    width: 40px;
}

.link-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.link-cell strong {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
}

.link-url {
    font-family: var(--font-body);
    font-size: 11px;
    color: var(--muted);
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.report-reason {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 700;
    color: var(--red);
    text-transform: uppercase;
}

.report-status {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: var(--radius);
    font-weight: 600;
    text-transform: uppercase;
}

.report-date {
    font-size: 12px;
    color: var(--muted);
}

.checkbox {
    width: 16px;
    height: 16px;
    accent-color: var(--red);
}

.report-info {
    display: flex;
    gap: 8px;
    align-items: center;
}

.link-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.link-url {
    color: var(--ink-soft);
    font-family: var(--font-body);
    font-size: 12px;
}

.report-description {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink-soft);
    margin: 0;
    font-style: italic;
}

.report-card__actions {
    display: flex;
    justify-content: flex-end;
}

.btn-primary-sm {
    padding: 4px 12px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-primary-sm:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.btn-ghost-sm {
    padding: 4px 8px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost-sm:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.flagged-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.flagged-item {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px;
}

.flagged-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
}

.flag-reason {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--red);
    text-transform: uppercase;
}

/* Modal Styles */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 26, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
}

.modal__title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
}

.modal__close {
    width: 32px;
    height: 32px;
    background: transparent;
    border: none;
    color: var(--muted);
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius);
    transition: var(--transition);
}

.modal__close:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.modal__body {
    padding: 24px;
}

.modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid var(--border);
}

.report-summary {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px;
    margin-bottom: 20px;
}

.report-summary__item {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 13px;
}

.report-summary__item:last-child {
    margin-bottom: 0;
}

.report-summary__label {
    font-weight: 600;
    color: var(--ink);
}

.field {
    margin-bottom: 16px;
}

.field__label {
    display: block;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 6px;
}

.field__input {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: var(--transition);
    width: 100%;
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.btn-ghost {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-primary {
    padding: 8px 16px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-primary:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}
</style>
