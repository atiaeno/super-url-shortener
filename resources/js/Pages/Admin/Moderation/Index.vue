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
        <div class="admin-page">
            <h1 class="admin-page__title">Content Moderation</h1>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats.pending || 0 }}</span>
                    <span class="stat-card__label">Pending Reports</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats.reviewed || 0 }}</span>
                    <span class="stat-card__label">Reviewed Today</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats.actioned || 0 }}</span>
                    <span class="stat-card__label">Action Taken</span>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">Report Queue</h2>
                    <div class="section-actions">
                        <button v-if="selectedReports.length" 
                            @click="batchMode = true" 
                            class="btn-ghost-sm">
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

                <div v-else class="reports-list">
                    <div v-for="report in reports.data" :key="report.id" class="report-card">
                        <div class="report-card__checkbox">
                            <input 
                                type="checkbox" 
                                :value="report.id" 
                                v-model="selectedReports"
                                class="checkbox" />
                        </div>

                        <div class="report-card__content">
                            <div class="report-card__header">
                                <div class="report-info">
                                    <span class="report-reason">{{ reasonLabels[report.reason] }}</span>
                                    <span class="report-status" :class="statusColors[report.status]">
                                        {{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
                                    </span>
                                </div>
                                <span class="report-date">{{ formatDate(report.created_at) }}</span>
                            </div>

                            <div class="report-card__details">
                                <div class="link-info">
                                    <strong>{{ report.link?.short_code }}</strong>
                                    <span class="link-url">{{ report.link?.original_url }}</span>
                                </div>
                                <p v-if="report.description" class="report-description">
                                    {{ report.description }}
                                </p>
                            </div>

                            <div class="report-card__actions">
                                <button @click="openReviewModal(report)" class="btn-primary-sm">
                                    Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flagged Links Section -->
            <div v-if="flaggedLinks?.length" class="admin-section">
                <h2 class="section-title">Flagged Links</h2>
                <div class="flagged-list">
                    <div v-for="link in flaggedLinks" :key="link.id" class="flagged-item">
                        <div class="flagged-info">
                            <strong>{{ link.short_code }}</strong>
                            <span class="link-url">{{ link.original_url }}</span>
                            <span class="flag-reason">Auto-flagged: {{ link.flag_reason }}</span>
                        </div>
                    </div>
                </div>
            </div>

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

.admin-page {
    max-width: 1200px;
}

.admin-page__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--ink);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-page__title::before {
    content: 'ADMIN';
    font-size: 10px;
    color: var(--red);
    letter-spacing: 1px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    text-align: center;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 4px;
}

.stat-card__label {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
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
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--muted);
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

.reports-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.report-card {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px;
    display: flex;
    gap: 16px;
    transition: var(--transition);
}

.report-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.report-card__checkbox {
    display: flex;
    align-items: flex-start;
    padding-top: 2px;
}

.checkbox {
    width: 16px;
    height: 16px;
    accent-color: var(--red);
}

.report-card__content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.report-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.report-info {
    display: flex;
    gap: 8px;
    align-items: center;
}

.report-reason {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
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

.report-card__details {
    display: flex;
    flex-direction: column;
    gap: 8px;
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
    background: var(--surface-2);
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
    font-size: 11px;
    color: var(--red);
    font-style: italic;
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
    background: var(--surface-2);
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
