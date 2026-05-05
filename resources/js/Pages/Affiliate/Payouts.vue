<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    affiliate: { type: Object, required: true },
    payouts:   { type: Object, required: true },
});

const statusLabel = { pending: 'Pending', approved: 'Approved', rejected: 'Rejected', paid: 'Paid' };
const statusClass = { pending: 'badge--warn', approved: 'badge--info', rejected: 'badge--red', paid: 'badge--green' };

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
</script>

<template>
    <Head title="Payout History" />

    <AuthenticatedLayout>
        <template #header><span class="material-icons">payments</span> Payout History</template>

        <div class="page-content">
            <div class="page-header">
                <Link :href="route('affiliate.index')" class="btn-back">
                    <span class="material-icons">arrow_back</span> Back to Dashboard
                </Link>
            </div>

            <div v-if="!payouts.data?.length" class="empty">
                <span class="material-icons empty-icon">account_balance_wallet</span>
                <p>No payout requests yet.</p>
                <p class="empty-hint">Request a payout from your dashboard when you have enough earnings.</p>
            </div>

            <div v-else class="payouts-list">
                <div v-for="payout in payouts.data" :key="payout.id" class="payout-card">
                    <div class="payout-main">
                        <div class="payout-amount">
                            <span class="amount-val">${{ parseFloat(payout.amount).toFixed(2) }}</span>
                            <span class="amount-label">Amount</span>
                        </div>
                        <div class="payout-status">
                            <span class="badge" :class="statusClass[payout.status]">
                                <span class="material-icons">{{ payout.status === 'paid' ? 'check_circle' : payout.status === 'rejected' ? 'cancel' : payout.status === 'approved' ? 'verified' : 'schedule' }}</span>
                                {{ statusLabel[payout.status] }}
                            </span>
                        </div>
                    </div>
                    <div class="payout-details">
                        <div class="detail-item">
                            <span class="material-icons">event</span>
                            <span>{{ formatDate(payout.created_at) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="material-icons">credit_card</span>
                            <span>{{ payout.payment_method || 'PayPal' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="material-icons">email</span>
                            <span>{{ payout.payment_email || payout.paypal_email || '—' }}</span>
                        </div>
                        <div v-if="payout.admin_note" class="detail-item note">
                            <span class="material-icons">info</span>
                            <span>{{ payout.admin_note }}</span>
                        </div>
                    </div>
                    <div v-if="payout.processed_at" class="payout-processed">
                        Processed: {{ formatDate(payout.processed_at) }}
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="payouts.last_page > 1" class="pagination">
                <Link v-if="payouts.prev_page_url" :href="payouts.prev_page_url" class="page-btn">
                    <span class="material-icons">chevron_left</span> Prev
                </Link>
                <span class="page-info">Page {{ payouts.current_page }} of {{ payouts.last_page }}</span>
                <Link v-if="payouts.next_page_url" :href="payouts.next_page_url" class="page-btn">
                    Next <span class="material-icons">chevron_right</span>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.page-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 24px;
}

.page-header {
    margin-bottom: 24px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: #444;
    font-size: 14px;
    text-decoration: none;
    transition: all 200ms;
}

.btn-back:hover {
    background: #f5f5f5;
    color: #1a1a1a;
}

.btn-back .material-icons {
    font-size: 18px;
}

.empty {
    text-align: center;
    padding: 60px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
}

.empty-icon {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 16px;
}

.empty p {
    font-size: 16px;
    color: #444;
    margin: 0;
}

.empty-hint {
    font-size: 13px !important;
    color: #888 !important;
    margin-top: 8px !important;
}

.payouts-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.payout-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    transition: border-color 200ms;
}

.payout-card:hover {
    border-color: #ccc;
}

.payout-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.payout-amount {
    display: flex;
    flex-direction: column;
}

.amount-val {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
}

.amount-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.badge .material-icons {
    font-size: 16px;
}

.badge--warn   { color: #F59E0B; background: rgba(245,158,11,0.1); }
.badge--info   { color: #22D3EE; background: rgba(34,211,238,0.1); }
.badge--green  { color: #22C55E; background: rgba(34,197,94,0.1); }
.badge--red    { color: #EF4444; background: rgba(239,68,68,0.1); }

.payout-details {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #444;
}

.detail-item .material-icons {
    font-size: 18px;
    color: #666;
}

.detail-item.note {
    color: #666;
    font-style: italic;
}

.payout-processed {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    font-size: 12px;
    color: #888;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 32px;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    color: #444;
    text-decoration: none;
    padding: 8px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    transition: all 200ms;
}

.page-btn:hover {
    background: #f5f5f5;
    color: #1a1a1a;
}

.page-btn .material-icons {
    font-size: 18px;
}

.page-info {
    font-size: 14px;
    color: #666;
}

/* Material Icons */
.material-icons {
    font-size: 20px;
    vertical-align: middle;
    margin-right: 4px;
}
</style>
