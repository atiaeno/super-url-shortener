<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    payout: { type: Object, required: true },
    logs: { type: Array, default: () => [] },
});

const statusColor = {
    pending: '#F59E0B',
    approved: '#22D3EE',
    rejected: '#EF4444',
    paid: '#22C55E',
};

const formatDate = (d) => new Date(d).toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
});
</script>

<template>

    <Head :title="`Payout #${payout.id} — Audit Log`" />
    <AdminLayout>
        <div class="admin-page">
            <h1 class="admin-page__title">Payout Audit Log</h1>

            <!-- Payout summary -->
            <div class="admin-section">
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-item__label">Affiliate</span>
                        <span class="summary-item__val">{{ payout.user?.name }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item__label">Amount</span>
                        <span class="summary-item__val summary-item__val--amount">${{ Number(payout.amount)?.toFixed(2)
                            || '0.00' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item__label">PayPal Email</span>
                        <span class="summary-item__val">{{ payout.paypal_email }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item__label">Current Status</span>
                        <span class="summary-item__val" :style="{ color: statusColor[payout.status] }">
                            {{ payout.status.charAt(0).toUpperCase() + payout.status.slice(1) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="admin-section">
                <h2 class="section-title">Change History</h2>

                <div v-if="!logs.length" class="empty-state">
                    <p>No audit entries yet.</p>
                </div>

                <div v-else class="timeline">
                    <div v-for="log in logs" :key="log.id" class="timeline-item">
                        <div class="timeline-item__dot"
                            :style="{ background: statusColor[log.new_status] ?? '#888' }" />
                        <div class="timeline-item__body">
                            <div class="timeline-item__header">
                                <span class="timeline-item__change">
                                    <span v-if="log.old_status" class="status-pill"
                                        :style="{ color: statusColor[log.old_status] }">
                                        {{ log.old_status }}
                                    </span>
                                    <span v-if="log.old_status" class="arrow">→</span>
                                    <span class="status-pill" :style="{ color: statusColor[log.new_status] }">
                                        {{ log.new_status }}
                                    </span>
                                </span>
                                <span class="timeline-item__meta">
                                    by {{ log.actor?.name ?? 'System' }} · {{ formatDate(log.created_at) }}
                                </span>
                            </div>
                            <p v-if="log.note" class="timeline-item__note">{{ log.note }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="back-row">
                <Link :href="route('admin.payouts.index')" class="btn-ghost">← Back to Payout Queue</Link>
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
    margin-bottom: 24px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.summary-item__label {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.summary-item__val {
    font-family: var(--font-body);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
}

.summary-item__val--amount {
    font-family: var(--font-display);
    font-size: 18px;
    color: #22C55E;
}

.section-title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 20px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
    font-style: italic;
}

/* Timeline Styles */
.timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-left: 24px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 8px;
    bottom: 8px;
    width: 1px;
    background: var(--border);
}

.timeline-item {
    display: flex;
    gap: 16px;
    position: relative;
    padding-bottom: 24px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item__dot {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid var(--surface);
    flex-shrink: 0;
}

.timeline-item__body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
}

.timeline-item__header {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.timeline-item__change {
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-pill {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.arrow {
    color: var(--muted);
    font-size: 12px;
}

.timeline-item__meta {
    font-size: 12px;
    color: var(--muted);
}

.timeline-item__note {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink-soft);
    background: var(--surface-2);
    border-left: 2px solid var(--border);
    padding: 8px 12px;
    border-radius: 0 var(--radius) var(--radius) 0;
    margin: 0;
    font-style: italic;
}

.back-row {
    display: flex;
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
}

.btn-ghost:hover {
    color: var(--ink);
    border-color: #ccc;
    background: var(--surface-2);
}

@media (max-width: 768px) {
    .summary-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
