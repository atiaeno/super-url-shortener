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

const icons = {
    moderation: 'gavel',
    review: 'visibility',
    delete: 'delete',
    edit: 'edit',
    plus: 'add',
};

const statCards = computed(() => [
    { label: 'Pending Reports', value: props.stats.pending || 0 },
    { label: 'Reviewed Today', value: props.stats.reviewed || 0 },
    { label: 'Action Taken', value: props.stats.actioned || 0 },
    { label: 'Flagged Links', value: props.flaggedLinks?.length || 0 },
]);

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
        <template #header><span class="material-icons">{{ icons.moderation }}</span> Content Moderation</template>

        <div class="page-content">

            <!-- Stats Row -->
            <div class="stats-row">
                <div v-for="stat in statCards" :key="stat.label" class="stat-box">
                    <span class="stat-value">{{ stat.value.toLocaleString() }}</span>
                    <span class="stat-label">{{ stat.label }}</span>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">

                <!-- Reports Queue -->
                <div class="chart-card chart-card--full">
                    <div class="chart-header">
                        <span class="header-icon"><span class="material-icons">{{ icons.moderation }}</span></span>
                        <h3>Report Queue</h3>
                        <div class="section-actions">
                            <button v-if="selectedReports.length" @click="batchMode = true" class="btn-create">
                                <span class="material-icons btn-icon">{{ icons.edit }}</span>Batch ({{
                                    selectedReports.length
                                }})
                            </button>
                            <button @click="toggleSelectAll" class="btn-ghost">
                                {{ selectedReports.length === reports.data.length ? 'Deselect All' : 'Select All' }}
                            </button>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div v-if="!reports.data?.length" class="no-data">No reports to review</div>
                        <div v-else class="data-list">
                            <div v-for="report in reports.data" :key="report.id" class="data-row">
                                <div class="data-label">
                                    <span>{{ reasonLabels[report.reason] }}</span>
                                    <span class="data-format">{{ report.link?.short_code }}</span>
                                </div>
                                <div class="data-value">
                                    <span class="status-badge" :class="statusColors[report.status]">
                                        {{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
                                    </span>
                                    <button @click="openReviewModal(report)" class="btn-action"><span
                                            class="material-icons">{{
                                                icons.review }}</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Flagged Links -->
                <div v-if="flaggedLinks?.length" class="chart-card">
                    <div class="chart-header">
                        <span class="header-icon"><span class="material-icons">warning</span></span>
                        <h3>Flagged Links</h3>
                    </div>
                    <div class="chart-body">
                        <div class="data-list">
                            <div v-for="link in flaggedLinks" :key="link.id" class="data-row">
                                <div class="data-label">
                                    <span>{{ link.short_code }}</span>
                                    <span class="data-format">{{ link.flag_reason }}</span>
                                </div>
                                <div class="data-value">
                                    <span class="status-badge status-active">Flagged</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Review Modal -->
        <div v-if="showReviewModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Review Report</h3>
                    <button @click="showReviewModal = false" class="modal__close">
                        <span class="material-icons">close</span>
                    </button>
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
                    <button @click="batchMode = false" class="modal__close">
                        <span class="material-icons">close</span>
                    </button>
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
    </AdminLayout>
</template>

<style scoped>
.page-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 24px;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    text-align: center;
}

.stat-value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 600;
    color: var(--ink);
    display: block;
    margin-bottom: 4px;
}

.stat-label {
    font-family: var(--font-display);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.chart-card--full {
    grid-column: 1 / -1;
}

.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.chart-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-icon {
    display: flex;
    align-items: center;
    color: var(--red);
}

.chart-header h3 {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--ink);
    margin: 0;
    flex: 1;
}

.section-actions {
    display: flex;
    gap: 8px;
}

.btn-create {
    background: var(--red);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-create:hover {
    background: #c0392b;
}

.chart-body {
    padding: 20px;
    min-height: 180px;
}

.no-data {
    text-align: center;
    color: var(--muted);
    font-size: 14px;
    padding: 40px 0;
}

/* Data List */
.data-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.data-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.data-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.data-label {
    font-size: 12px;
    color: var(--ink);
    font-family: 'DM Sans', sans-serif;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.data-format {
    font-size: 10px;
    color: var(--muted);
    font-family: var(--font-display);
    text-transform: uppercase;
}

.data-value {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-badge {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 2px;
    letter-spacing: 0.5px;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.btn-action {
    background: transparent;
    border: 1px solid var(--border);
    padding: 4px 8px;
    border-radius: var(--radius);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-action:hover {
    background: var(--surface-2);
}

.btn-danger {
    color: var(--red);
    border-color: var(--red);
}

.btn-danger:hover {
    background: var(--red);
    color: white;
}

/* Material Icons */
.material-icons {
    font-family: 'Material Icons';
    font-weight: normal;
    font-style: normal;
    font-size: 18px;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    -webkit-font-feature-settings: 'liga';
    -webkit-font-smoothing: antialiased;
}

.btn-icon {
    font-size: 16px;
    margin-right: 6px;
    vertical-align: middle;
}

/* Modal Styles */
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
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal__header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.modal__close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--muted);
    padding: 4px;
    border-radius: var(--radius);
    transition: all 0.2s;
}

.modal__close:hover {
    color: var(--ink);
    background: var(--surface-2);
}

.modal__body {
    padding: 20px;
}

.modal__footer {
    padding: 20px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
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
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.field__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink);
    margin-bottom: 6px;
}

.field__input {
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    transition: all 0.2s;
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.btn-ghost {
    background: transparent;
    border: 1px solid var(--border);
    padding: 6px 12px;
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-primary {
    background: var(--red);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #c0392b;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }

    .page-content {
        padding: 16px;
    }
}
</style>