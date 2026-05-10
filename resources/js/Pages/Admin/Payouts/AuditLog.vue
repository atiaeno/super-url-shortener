<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    payout: { type: Object, required: true },
    logs: { type: Array, default: () => [] },
});

const icons = {
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
    user: `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>`,
    dollar: `<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    mail: `<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>`,
    history: `<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>`,
    clock: `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
};

const statusConfig = {
    pending: { color: '#ca8a04', bg: '#fef9c3', label: 'Pending' },
    approved: { color: '#3b82f6', bg: '#dbeafe', label: 'Approved' },
    rejected: { color: '#dc2626', bg: '#fee2e2', label: 'Rejected' },
    paid: { color: '#16a34a', bg: '#d1fae5', label: 'Paid' },
};

const summaryItems = computed(() => [
    { id: 'affiliate', label: 'Affiliate', value: props.payout.affiliate?.user?.name || 'Unknown', icon: icons.user, roman: 'I.', color: 'red' },
    { id: 'amount', label: 'Amount', value: `$${Number(props.payout.amount)?.toFixed(2) || '0.00'}`, icon: icons.dollar, roman: 'II.', color: 'green' },
    { id: 'paypal', label: 'PayPal', value: props.payout.paypal_email || '-', icon: icons.mail, roman: 'III.', color: 'blue' },
    { id: 'status', label: 'Status', value: statusConfig[props.payout.status]?.label || props.payout.status, icon: icons.clock, roman: 'IV.', color: props.payout.status === 'pending' ? 'yellow' : props.payout.status === 'approved' ? 'blue' : props.payout.status === 'paid' ? 'green' : 'red' },
]);

const formatDate = (d) => new Date(d).toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
});

const formatDateShort = (d) => new Date(d).toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
});
</script>

<template>

    <Head :title="`Payout #${payout.id} — Audit Log`" />

    <AdminLayout>
        <template #header-icon>
            <polyline points="1 4 1 10 7 10" />
            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
        </template>
        <template #header>Audit Log</template>

        <div class="audit-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Payout #{{ payout.id }}</span>
                    <h1 class="page-header__title">Audit History</h1>
                    <p class="page-header__sub">
                        Requested {{ formatDateShort(payout.created_at) }} ·
                        <span class="status-inline" :style="{ color: statusConfig[payout.status]?.color }">
                            {{ statusConfig[payout.status]?.label || payout.status }}
                        </span>
                    </p>
                </div>
                <Link :href="route('admin.payouts.index')" class="back-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.arrow" />
                    Back to Payouts
                </Link>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Summary Cards -->
            <section class="summary-section">
                <div class="summary-grid">
                    <div v-for="item in summaryItems" :key="item.id" class="summary-card"
                        :class="`summary-card--${item.color}`">
                        <div class="summary-card__top">
                            <span class="summary-card__roman">{{ item.roman }}</span>
                            <div class="summary-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="item.icon" />
                            </div>
                        </div>
                        <div class="summary-card__value">{{ item.value }}</div>
                        <div class="summary-card__label">{{ item.label }}</div>
                    </div>
                </div>
            </section>

            <!-- Timeline -->
            <section class="timeline-section">
                <div class="section-header">
                    <h2 class="section-header__title">Change History</h2>
                    <span class="section-header__count">{{ logs.length }} entries</span>
                </div>

                <div class="timeline-card">
                    <div v-if="!logs.length" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.history" />
                        </div>
                        <p class="empty-state__title">No audit entries</p>
                        <p class="empty-state__text">This payout has no change history yet.</p>
                    </div>

                    <div v-else class="timeline">
                        <div v-for="(log, index) in logs" :key="log.id" class="timeline-item"
                            :class="{ 'timeline-item--last': index === logs.length - 1 }">
                            <div class="timeline-item__left">
                                <div class="timeline-dot"
                                    :style="{ background: statusConfig[log.new_status]?.color || '#888' }">
                                    <svg v-if="log.new_status === 'paid'" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <svg v-else-if="log.new_status === 'approved'" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <svg v-else-if="log.new_status === 'rejected'" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                    </svg>
                                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                </div>
                                <div v-if="index !== logs.length - 1" class="timeline-line"></div>
                            </div>
                            <div class="timeline-item__body">
                                <div class="timeline-item__header">
                                    <div class="status-change">
                                        <span v-if="log.old_status" class="status-badge"
                                            :class="`status-badge--${log.old_status}`">
                                            {{ statusConfig[log.old_status]?.label || log.old_status }}
                                        </span>
                                        <span v-if="log.old_status" class="status-arrow">→</span>
                                        <span class="status-badge" :class="`status-badge--${log.new_status}`">
                                            {{ statusConfig[log.new_status]?.label || log.new_status }}
                                        </span>
                                    </div>
                                    <span class="timeline-item__date">{{ formatDate(log.created_at) }}</span>
                                </div>
                                <div class="timeline-item__actor">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        v-html="icons.user" />
                                    by {{ log.actor?.name || 'System' }}
                                </div>
                                <p v-if="log.note" class="timeline-item__note">{{ log.note }}</p>
                            </div>
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

.status-inline {
    font-weight: 600;
    font-style: normal;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    text-decoration: none;
    transition: var(--transition);
}

.back-btn:hover {
    color: var(--ink);
    border-color: var(--red);
    background: #fef2f2;
}

.back-btn svg {
    width: 16px;
    height: 16px;
}

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Summary Cards ───────────────────────── */
.summary-section {
    margin-bottom: 32px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    transition: var(--transition);
}

.summary-card--red {
    background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
}

.summary-card--green {
    background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);
}

.summary-card--blue {
    background: linear-gradient(135deg, #eff6ff 0%, #fff 100%);
}

.summary-card--yellow {
    background: linear-gradient(135deg, #fef9c3 0%, #fff 100%);
}

.summary-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.summary-card__roman {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    color: var(--red);
}

.summary-card__icon {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.summary-card--red .summary-card__icon {
    background: #fef2f2;
    color: var(--red);
}

.summary-card--green .summary-card__icon {
    background: #dcfce7;
    color: #16a34a;
}

.summary-card--blue .summary-card__icon {
    background: #dbeafe;
    color: #3b82f6;
}

.summary-card--yellow .summary-card__icon {
    background: #fef08a;
    color: #ca8a04;
}

.summary-card__icon svg {
    width: 14px;
    height: 14px;
}

.summary-card__value {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 2px;
    word-break: break-word;
}

.summary-card__label {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--muted);
}

/* ── Timeline Section ─────────────────────── */
.timeline-section {
    margin-bottom: 32px;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.section-header__title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
}

.section-header__count {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--muted);
    padding: 4px 10px;
    background: var(--surface-2);
    border-radius: var(--radius);
}

.timeline-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
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

/* ── Timeline ───────────────────────────── */
.timeline {
    display: flex;
    flex-direction: column;
}

.timeline-item {
    display: flex;
    gap: 20px;
    padding-bottom: 24px;
    position: relative;
}

.timeline-item--last {
    padding-bottom: 0;
}

.timeline-item__left {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
}

.timeline-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.timeline-dot svg {
    width: 16px;
    height: 16px;
}

.timeline-line {
    width: 1px;
    flex: 1;
    background: var(--border);
    margin: 8px 0;
    min-height: 40px;
}

.timeline-item__body {
    flex: 1;
    padding-top: 4px;
}

.timeline-item__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.status-change {
    display: flex;
    align-items: center;
    gap: 8px;
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

.status-arrow {
    color: var(--muted);
    font-size: 12px;
    font-weight: 600;
}

.timeline-item__date {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
}

.timeline-item__actor {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    color: var(--ink-soft);
    margin-bottom: 8px;
}

.timeline-item__actor svg {
    width: 14px;
    height: 14px;
    color: var(--muted);
}

.timeline-item__note {
    font-family: var(--font-body);
    font-size: 14px;
    font-style: italic;
    color: var(--ink-soft);
    background: var(--surface-2);
    border-left: 3px solid var(--gold);
    padding: 12px 16px;
    border-radius: 0 var(--radius) var(--radius) 0;
    margin: 0;
    line-height: 1.5;
}

/* ── Responsive ────────────────────────── */
@media (max-width: 1024px) {
    .summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .timeline-item__header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>
