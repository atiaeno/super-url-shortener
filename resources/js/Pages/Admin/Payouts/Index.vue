<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payouts: { type: Object, required: true },
});

const rejectModal = ref(null);
const rejectForm = useForm({ note: '' });

const approveForm = useForm({});
const paidForm = useForm({});

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
</script>

<template>

    <Head title="Payout Queue" />
    <AdminLayout>
        <div class="admin-page">
            <h1 class="admin-page__title">Payout Queue</h1>

            <div v-if="!payouts.data?.length" class="admin-section">
                <div class="empty-state">
                    <p>No pending payout requests.</p>
                </div>
            </div>

            <div v-else class="admin-section">
                <div class="table-wrapper">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th>Affiliate</th>
                                <th>Amount</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payout in payouts.data" :key="payout.id">
                                <td>
                                    <div class="affiliate-cell">
                                        <div class="affiliate-avatar">
                                            {{ payout.user?.name?.charAt(0)?.toUpperCase() }}
                                        </div>
                                        <div class="affiliate-info">
                                            <div class="affiliate-name">{{ payout.user?.name }}</div>
                                            <div class="affiliate-email">{{ payout.user?.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="amount">${{ Number(payout.amount)?.toFixed(2) || '0.00' }}</span>
                                </td>
                                <td class="date-cell">{{ formatDate(payout.created_at) }}</td>
                                <td>
                                    <span class="status-badge" :class="{
                                        'status-pending': payout.status === 'pending',
                                        'status-approved': payout.status === 'approved',
                                        'status-paid': payout.status === 'paid',
                                        'status-rejected': payout.status === 'rejected'
                                    }">
                                        {{ payout.status?.charAt(0)?.toUpperCase() + payout.status?.slice(1) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <button v-if="payout.status === 'pending'" @click="approve(payout.id)"
                                            class="btn-success-sm" :disabled="approveForm.processing">
                                            Approve
                                        </button>
                                        <button v-if="payout.status === 'approved'" @click="markPaid(payout.id)"
                                            class="btn-primary-sm" :disabled="paidForm.processing">
                                            Mark Paid
                                        </button>
                                        <button v-if="payout.status === 'pending'" @click="openReject(payout)"
                                            class="btn-danger-sm">
                                            Reject
                                        </button>
                                        <Link :href="route('admin.payouts.audit-log', payout.id)" class="btn-ghost-sm">
                                            Audit
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reject Modal -->
            <div v-if="rejectModal" class="modal-backdrop">
                <div class="modal">
                    <div class="modal__header">
                        <h3 class="modal__title">Reject Payout</h3>
                        <button @click="rejectModal = null" class="modal__close">×</button>
                    </div>
                    <form @submit.prevent="submitReject">
                        <div class="modal__body">
                            <p class="mb-4">Reject payout request from <strong>{{ rejectModal.user?.name }}</strong>?
                            </p>

                            <div class="field">
                                <label class="field__label">Reason for rejection</label>
                                <textarea v-model="rejectForm.note" class="field__input" rows="3"
                                    placeholder="Please provide a reason..." required></textarea>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" @click="rejectModal = null" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-danger" :disabled="rejectForm.processing">
                                Reject Payout
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

.admin-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
    font-style: italic;
}

.table-wrapper {
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.payouts-table {
    width: 100%;
    border-collapse: collapse;
}

.payouts-table th {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid var(--border);
}

.payouts-table td {
    padding: 16px 12px;
    border-bottom: 1px solid var(--border);
}

.affiliate-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.affiliate-avatar {
    width: 40px;
    height: 40px;
    background: var(--gold);
    color: var(--ink);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
}

.affiliate-info {
    flex: 1;
}

.affiliate-name {
    font-family: var(--font-display);
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 2px;
}

.affiliate-email {
    font-size: 12px;
    color: var(--muted);
}

.amount {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
}

.date-cell {
    font-size: 13px;
    color: var(--ink-soft);
}

.status-badge {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: var(--radius);
    font-weight: 600;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-approved {
    background: #dbeafe;
    color: #1e40af;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.actions-cell {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-success-sm {
    padding: 4px 8px;
    background: #10b981;
    border: 1px solid #10b981;
    border-radius: var(--radius);
    color: var(--surface);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-success-sm:hover {
    background: #059669;
    border-color: #059669;
}

.btn-primary-sm {
    padding: 4px 8px;
    background: #3b82f6;
    border: 1px solid #3b82f6;
    border-radius: var(--radius);
    color: var(--surface);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-primary-sm:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.btn-danger-sm {
    padding: 4px 8px;
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

.btn-danger-sm:hover {
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
    text-decoration: none;
    display: inline-block;
}

.btn-ghost-sm:hover {
    background: var(--surface-2);
    color: var(--ink);
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

.btn-danger {
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

.btn-danger:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}
</style>
