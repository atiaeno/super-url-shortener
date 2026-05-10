<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    payouts: { type: Object, required: true },
    stats: { type: Object, required: true },
    currentStatus: { type: String, default: 'pending' },
});

const rejectModal = ref(null);
const rejectForm = useForm({ note: '' });

const approveForm = useForm({});
const paidForm = useForm({});

const icons = {
    dollar: `<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    clock: `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
    check: `<polyline points="20 6 9 17 4 12"/>`,
    ban: `<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>`,
    user: `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>`,
    history: `<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>`,
};

const statItems = computed(() => [
    { id: 'pending', label: 'Pending', value: props.stats.pending, roman: 'I.', icon: icons.clock, color: 'yellow' },
    { id: 'approved', label: 'Approved', value: props.stats.approved, roman: 'II.', icon: icons.check, color: 'blue' },
    { id: 'paid', label: 'Paid', value: props.stats.paid, roman: 'III.', icon: icons.dollar, color: 'green' },
    { id: 'rejected', label: 'Rejected', value: props.stats.rejected, roman: 'IV.', icon: icons.ban, color: 'red' },
]);

const statusTabs = [
    { id: 'pending', label: 'Pending' },
    { id: 'approved', label: 'Approved' },
    { id: 'paid', label: 'Paid' },
    { id: 'rejected', label: 'Rejected' },
];

const setStatus = (status) => {
    router.get(route('admin.payouts.index'), { status }, { preserveState: true, replace: true });
};

const approve = (id) => {
    approveForm.post(route('admin.payouts.approve', id));
};

const markPaid = (id) => {
    paidForm.post(route('admin.payouts.mark-paid', id));
};

const openReject = (payout) => {
    rejectModal.value = payout;
    rejectForm.reset();
};

const submitReject = () => {
    rejectForm.post(route('admin.payouts.reject', rejectModal.value.id), {
        onSuccess: () => { rejectModal.value = null; },
    });
};

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const formatCurrency = (amount) => `$${Number(amount)?.toFixed(2) || '0.00'}`;
</script>

<template>

    <Head title="Payout Management" />

    <AdminLayout>
        <template #header-icon>
            <line x1="12" y1="1" x2="12" y2="23" />
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
        </template>
        <template #header>Payouts</template>

        <div class="payouts-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Affiliate Management</span>
                    <h1 class="page-header__title">Payout Queue</h1>
                    <p class="page-header__sub">
                        Total pending amount: <strong class="highlight">{{ formatCurrency(stats.totalAmount) }}</strong>
                    </p>
                </div>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <button v-for="item in statItems" :key="item.id" @click="setStatus(item.id)" class="stat-card"
                        :class="[`stat-card--${item.color}`, { 'stat-card--active': currentStatus === item.id }]">
                        <div class="stat-card__top">
                            <span class="stat-card__roman">{{ item.roman }}</span>
                            <div class="stat-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="item.icon" />
                            </div>
                        </div>
                        <div class="stat-card__value">{{ item.value }}</div>
                        <div class="stat-card__label">{{ item.label }}</div>
                    </button>
                </div>
            </section>

            <!-- Status Tabs -->
            <section class="tabs-section">
                <div class="status-tabs">
                    <button v-for="tab in statusTabs" :key="tab.id" @click="setStatus(tab.id)" class="tab-btn"
                        :class="{ 'tab-btn--active': currentStatus === tab.id }">
                        {{ tab.label }}
                        <span v-if="stats[tab.id] > 0" class="tab-count">{{ stats[tab.id] }}</span>
                    </button>
                </div>
            </section>

            <!-- Payouts Table -->
            <section class="table-section">
                <div class="table-card">
                    <div v-if="!payouts.data?.length" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </div>
                        <p class="empty-state__title">No {{ currentStatus }} payouts</p>
                        <p class="empty-state__text">There are no payouts with this status.</p>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="payouts-table">
                            <thead>
                                <tr>
                                    <th>Affiliate</th>
                                    <th>Tier</th>
                                    <th class="col-right">Amount</th>
                                    <th class="col-center">Requested</th>
                                    <th class="col-center">Status</th>
                                    <th class="col-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payout in payouts.data" :key="payout.id">
                                    <td>
                                        <div class="affiliate-cell">
                                            <div class="affiliate-avatar">
                                                {{ payout.affiliate?.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </div>
                                            <div class="affiliate-info">
                                                <div class="affiliate-name">{{ payout.affiliate?.user?.name || 'Unknown'
                                                    }}
                                                </div>
                                                <div class="affiliate-email">{{ payout.affiliate?.user?.email || '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tier-badge">{{ payout.affiliate?.tier?.name || 'Standard' }}</span>
                                    </td>
                                    <td class="col-right">
                                        <span class="amount">{{ formatCurrency(payout.amount) }}</span>
                                    </td>
                                    <td class="col-center">
                                        <span class="date-cell">{{ formatDate(payout.created_at) }}</span>
                                    </td>
                                    <td class="col-center">
                                        <span class="status-badge" :class="`status-badge--${payout.status}`">
                                            {{ payout.status?.charAt(0)?.toUpperCase() + payout.status?.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <div class="actions-cell">
                                            <button v-if="payout.status === 'pending'" @click="approve(payout.id)"
                                                class="btn-icon btn-icon--approve" :disabled="approveForm.processing"
                                                title="Approve">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            </button>
                                            <button v-if="payout.status === 'approved'" @click="markPaid(payout.id)"
                                                class="btn-icon btn-icon--paid" :disabled="paidForm.processing"
                                                title="Mark Paid">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <line x1="12" y1="1" x2="12" y2="23" />
                                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                                </svg>
                                            </button>
                                            <button v-if="payout.status === 'pending'" @click="openReject(payout)"
                                                class="btn-icon btn-icon--reject" title="Reject">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                                </svg>
                                            </button>
                                            <Link :href="route('admin.payouts.audit-log', payout.id)"
                                                class="btn-icon btn-icon--audit" title="View Audit Log">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <polyline points="1 4 1 10 7 10" />
                                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                                                </svg>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="payouts.last_page > 1" class="pagination">
                            <template v-for="(link, i) in payouts.links" :key="i">
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

        <!-- Reject Modal -->
        <Teleport to="body">
            <div v-if="rejectModal" class="modal-overlay" @click="rejectModal = null">
                <div class="modal" @click.stop>
                    <div class="modal__header">
                        <div class="modal__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                            </svg>
                        </div>
                        <h3 class="modal__title">Reject Payout</h3>
                    </div>
                    <form @submit.prevent="submitReject">
                        <div class="modal__body">
                            <p class="modal__text">
                                Reject payout request from <strong>{{ rejectModal.affiliate?.user?.name }}</strong>
                                for <strong>{{ formatCurrency(rejectModal.amount) }}</strong>?
                            </p>
                            <div class="field">
                                <label class="field__label">Reason for rejection <span class="required">*</span></label>
                                <textarea v-model="rejectForm.note" class="field__input" rows="3"
                                    placeholder="Please provide a reason..." required></textarea>
                                <span v-if="rejectForm.errors.note" class="field__error">{{ rejectForm.errors.note
                                    }}</span>
                            </div>
                        </div>
                        <div class="modal__actions">
                            <button type="button" @click="rejectModal = null"
                                class="modal__btn modal__btn--ghost">Cancel</button>
                            <button type="submit" class="modal__btn modal__btn--danger"
                                :disabled="rejectForm.processing">
                                {{ rejectForm.processing ? 'Rejecting...' : 'Reject Payout' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

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

.highlight {
    color: var(--red);
    font-style: normal;
}

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Stats Grid ───────────────────────────── */
.stats-section {
    margin-bottom: 16px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    transition: all 0.2s ease;
    cursor: pointer;
    text-align: left;
}

.stat-card:hover {
    border-color: var(--gold);
}

.stat-card--active {
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.stat-card--yellow {
    background: var(--surface);
}

.stat-card--blue {
    background: var(--surface);
}

.stat-card--green {
    background: var(--surface);
}

.stat-card--red {
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
    font-weight: 700;
    color: var(--red);
}

.stat-card__icon {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card--yellow .stat-card__icon {
    background: #fef08a;
    color: #ca8a04;
}

.stat-card--blue .stat-card__icon {
    background: #dbeafe;
    color: #3b82f6;
}

.stat-card--green .stat-card__icon {
    background: #dcfce7;
    color: #16a34a;
}

.stat-card--red .stat-card__icon {
    background: #fee2e2;
    color: var(--red);
}

.stat-card__icon svg {
    width: 14px;
    height: 14px;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 2px;
}

.stat-card__label {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--muted);
}

/* ── Status Tabs ──────────────────────────── */
.tabs-section {
    margin-bottom: 20px;
}

.status-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 12px;
}

.tab-btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.tab-btn--active {
    background: var(--surface);
    border-color: var(--red);
    color: var(--red);
}

.tab-count {
    font-size: 10px;
    padding: 2px 6px;
    background: var(--surface-2);
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

.tab-btn--active .tab-count {
    background: var(--red);
    color: var(--surface);
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
    font-weight: 600;
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

.payouts-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-body);
    font-size: 14px;
}

.payouts-table th {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    padding: 14px 16px;
    text-align: left;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

.payouts-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.payouts-table tr:last-child td {
    border-bottom: none;
}

.payouts-table tr:hover td {
    background: var(--surface-2);
}

.col-center {
    text-align: center;
}

.col-right {
    text-align: right;
}

.affiliate-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.affiliate-avatar {
    width: 36px;
    height: 36px;
    background: var(--surface-2);
    color: var(--ink);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.affiliate-info {
    flex: 1;
}

.affiliate-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    color: var(--ink);
    margin-bottom: 2px;
}

.affiliate-email {
    font-size: 12px;
    color: var(--muted);
}

.tier-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 4px 8px;
    background: var(--surface-2);
    border-radius: var(--radius);
    color: var(--muted);
}

.amount {
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
}

.date-cell {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
}

.status-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: var(--radius);
    white-space: nowrap;
}

.status-badge--pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge--approved {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge--paid {
    background: #d1fae5;
    color: #065f46;
}

.status-badge--rejected {
    background: #fee2e2;
    color: #991b1b;
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
    color: var(--ink-soft);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-icon:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.btn-icon svg {
    width: 14px;
    height: 14px;
}

.btn-icon--approve {
    background: #f0fdf4;
    color: #16a34a;
    border-color: #86efac;
}

.btn-icon--approve:hover {
    background: #dcfce7;
    color: #15803d;
}

.btn-icon--paid {
    background: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
}

.btn-icon--paid:hover {
    background: #dbeafe;
    color: #2563eb;
}

.btn-icon--reject {
    background: #fef2f2;
    color: var(--red);
    border-color: #fecaca;
}

.btn-icon--reject:hover {
    background: #fee2e2;
    color: var(--red-dark);
}

.btn-icon--audit {
    background: #f5f3ef;
    color: var(--gold);
    border-color: #fde68a;
}

.btn-icon--audit:hover {
    background: #fef3c7;
    color: #d97706;
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
    color: var(--ink-soft);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}

.pagination__link:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.pagination__link--active {
    background: var(--red);
    color: var(--surface);
    border-color: var(--red);
}

.pagination__link--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: var(--surface-2);
}

/* ── Modal ─────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    width: 100%;
    max-width: 480px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    animation: modalEnter 0.2s ease;
}

@keyframes modalEnter {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal__header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 24px 24px 0;
}

.modal__icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fef2f2;
    border-radius: var(--radius);
    color: var(--red);
}

.modal__icon svg {
    width: 20px;
    height: 20px;
}

.modal__title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.modal__body {
    padding: 16px 24px 24px;
}

.modal__text {
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--ink-soft);
    margin: 0 0 20px;
    line-height: 1.5;
}

.modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background: var(--surface-2);
    border-top: 1px solid var(--border);
}

.modal__btn {
    padding: 10px 20px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition);
    border: 1px solid transparent;
}

.modal__btn--ghost {
    background: var(--surface);
    border-color: var(--border);
    color: var(--ink);
}

.modal__btn--ghost:hover {
    background: var(--border);
}

.modal__btn--danger {
    background: var(--red);
    border-color: var(--red);
    color: var(--surface);
}

.modal__btn--danger:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.field {
    margin-bottom: 0;
}

.field__label {
    display: block;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 8px;
}

.required {
    color: var(--red);
}

.field__input {
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: var(--transition);
    width: 100%;
    resize: vertical;
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.field__error {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--red);
    margin-top: 6px;
    display: block;
}

/* ── Responsive ────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .status-tabs {
        flex-wrap: wrap;
    }

    .actions-cell {
        flex-wrap: wrap;
    }
}
</style>
