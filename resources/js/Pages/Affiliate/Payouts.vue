<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    affiliate: { type: Object, required: true },
    payouts: { type: Object, required: true },
});

const statusLabel = { pending: 'Pending', approved: 'Approved', rejected: 'Rejected', paid: 'Paid' };
const statusClass = { pending: 'badge--warn', approved: 'badge--info', rejected: 'badge--red', paid: 'badge--green' };
const statusIcon = { pending: 'schedule', approved: 'verified', rejected: 'cancel', paid: 'check_circle' };

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

            <div v-else class="table-wrapper">
                <table class="payout-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Email / Address</th>
                            <th>Status</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payout in payouts.data" :key="payout.id">
                            <td class="cell-date">{{ formatDate(payout.created_at) }}</td>
                            <td class="cell-amount">${{ parseFloat(payout.amount).toFixed(2) }}</td>
                            <td class="cell-method">{{ payout.payment_method || 'PayPal' }}</td>
                            <td class="cell-email">{{ payout.payment_email || payout.paypal_email || '—' }}</td>
                            <td>
                                <span class="badge" :class="statusClass[payout.status]">
                                    <span class="material-icons">{{ statusIcon[payout.status] }}</span>
                                    {{ statusLabel[payout.status] }}
                                </span>
                            </td>
                            <td class="cell-note">{{ payout.admin_note || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
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
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');

.page-content {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #444;
    margin: 0;
}

.empty-hint {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px !important;
    letter-spacing: 0 !important;
    color: #888 !important;
    margin-top: 8px !important;
}

.table-wrapper {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}

.payout-table {
    width: 100%;
    border-collapse: collapse;
}

.payout-table th {
    text-align: left;
    padding: 14px 20px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0;
    background: #fafafa;
    border-bottom: 1px solid var(--border);
}

.payout-table td {
    padding: 16px 20px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #333;
    border-bottom: 1px solid var(--border);
}

.payout-table tr:last-child td {
    border-bottom: none;
}

.payout-table tr:hover {
    background: #fafafa;
}

.cell-date {
    font-family: 'DM Sans', sans-serif;
    color: #666;
    font-size: 12px;
    letter-spacing: 0;
}

.cell-amount {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
}

.cell-method {
    color: #444;
}

.cell-email {
    color: #555;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cell-note {
    font-family: 'DM Sans', sans-serif;
    color: #888;
    font-size: 12px;
    letter-spacing: 0;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0;
}

.badge .material-icons {
    font-size: 14px;
}

.badge--warn {
    color: #F59E0B;
    background: rgba(245, 158, 11, 0.1);
}

.badge--info {
    color: #22D3EE;
    background: rgba(34, 211, 238, 0.1);
}

.badge--green {
    color: #22C55E;
    background: rgba(34, 197, 94, 0.1);
}

.badge--red {
    color: #EF4444;
    background: rgba(239, 68, 68, 0.1);
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 24px;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #666;
}

/* Material Icons */
.material-icons {
    font-size: 20px;
    vertical-align: middle;
    margin-right: 4px;
}
</style>
