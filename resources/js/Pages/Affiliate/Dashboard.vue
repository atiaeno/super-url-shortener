<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    affiliate: { type: Object, default: null },
    tiers: { type: Array, default: () => [] },
    visitsByTier: { type: Array, default: () => [] },
    minPayout: { type: Number, default: 50 },
    payoutMethods: { type: Array, default: () => ['PayPal'] },
});

const enrollForm = useForm({});
const payoutForm = useForm({ payment_method: '', payment_email: '' });
const syncForm = useForm({});

const progressPercent = computed(() => {
    if (!props.affiliate) return 0;
    const visits = props.affiliate.total_visits;
    const threshold = 1000;
    return Math.min(100, Math.round((visits / threshold) * 100));
});

const canPayout = computed(() =>
    props.affiliate && parseFloat(props.affiliate.pending_earnings) >= props.minPayout
);

const shortUrl = (code) => `${window.location.origin}/?ref=${code}`;

const tierCountries = (tierId) => {
    const tier = props.tiers.find(t => t.id === tierId);
    if (!tier?.country_rates?.length) return 'All other countries';
    return tier.country_rates.map(c => c.country_code).join(', ');
};

const copyRef = async (code) => {
    await navigator.clipboard.writeText(shortUrl(code));
};

const statusClass = (status) => ({
    pending: 'badge--warn',
    approved: 'badge--info',
    paid: 'badge--green',
    rejected: 'badge--red',
}[status] ?? 'badge--gray');
</script>

<template>

    <Head title="Affiliate Dashboard" />

    <AuthenticatedLayout>
        <template #header><span class="material-icons">trending_up</span> Affiliate Program</template>

        <div class="page-content">

            <!-- Not enrolled yet -->
            <div v-if="!affiliate" class="enroll-section">
                <div class="enroll-hero">
                    <span class="enroll-icon material-icons">attach_money</span>
                    <h2 class="enroll-title">Earn with Every Click</h2>
                    <p class="enroll-desc">
                        Join our affiliate program and earn commissions for every visit your short links generate.
                        Country-tiered rates mean big markets pay more.
                    </p>
                </div>

                <div class="tiers-grid">
                    <div v-for="tier in tiers" :key="tier.id" class="tier-card">
                        <span class="tier-name">{{ tier.name }}</span>
                        <span class="tier-rate">{{ tier.commission_rate }}%</span>
                        <span class="tier-label">commission</span>
                    </div>
                </div>

                <form @submit.prevent="enrollForm.post(route('affiliate.enroll'))" class="enroll-action">
                    <button type="submit" class="btn-primary btn-large" :disabled="enrollForm.processing">
                        <span v-if="enrollForm.processing">Enrolling…</span>
                        <span v-else><span class="material-icons">star</span> Become an Affiliate</span>
                    </button>
                </form>
            </div>

            <!-- Enrolled: dashboard -->
            <template v-else>
                <!-- Stat row -->
                <div class="stat-row">
                    <div class="mini-stat">
                        <span class="mini-stat__val">${{ parseFloat(affiliate.total_earnings).toFixed(2) }}</span>
                        <span class="mini-stat__label">Total Earned</span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat__val">${{ parseFloat(affiliate.pending_earnings).toFixed(2) }}</span>
                        <span class="mini-stat__label">Pending</span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat__val">${{ parseFloat(affiliate.paid_earnings).toFixed(2) }}</span>
                        <span class="mini-stat__label">Paid Out</span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat__val">{{ affiliate.total_visits.toLocaleString() }}</span>
                        <span class="mini-stat__label">Total Visits</span>
                    </div>
                </div>

                <div class="sync-bar">
                    <p class="sync-note">Earnings update daily. Click to sync now:</p>
                    <form @submit.prevent="syncForm.post(route('affiliate.sync'))">
                        <button type="submit" class="btn-sync" :disabled="syncForm.processing">
                            <span v-if="syncForm.processing">Syncing…</span>
                            <span v-else><span class="material-icons">sync</span> Sync Earnings</span>
                        </button>
                    </form>
                </div>

                <div class="grid-2">
                    <!-- Referral Link -->
                    <div class="card">
                        <h3 class="card-title">How to Earn</h3>
                        <div class="how-to-use">
                            <p class="how-to-use__step">1. Shorten any URL from your account</p>
                            <p class="how-to-use__step">2. Share the short link anywhere — social media, blogs,
                                groups</p>
                            <p class="how-to-use__step">3. Every unique visitor earns you based on their
                                country's tier rate</p>
                            <p class="how-to-use__step">4. No extra steps — your links are already tied to your
                                account</p>
                        </div>
                        <div class="referral-box">
                            <span class="referral-box__label">Your referral code (for manual use)</span>
                            <div class="referral-box__row">
                                <code class="referral-box__code">{{ affiliate.referral_code }}</code>
                                <button @click="copyRef(affiliate.referral_code)" class="btn-ghost-sm">Copy</button>
                            </div>
                        </div>
                    </div>

                    <!-- Payout request -->
                    <div class="card">
                        <h3 class="card-title">Request Payout</h3>

                        <div v-if="!canPayout" class="payout-locked">
                            <p>Minimum payout is <strong>${{ minPayout }}</strong>.</p>
                            <p>You currently have <strong>${{ parseFloat(affiliate.pending_earnings).toFixed(2)
                            }}</strong> pending.</p>
                            <div class="payout-progress-track">
                                <div class="payout-progress-fill"
                                    :style="{ width: Math.min(100, Math.round((parseFloat(affiliate.pending_earnings) / minPayout) * 100)) + '%' }" />
                            </div>
                        </div>

                        <form v-else @submit.prevent="payoutForm.post(route('affiliate.payout.request'))"
                            class="payout-form">
                            <div class="field">
                                <label class="field__label">Payment Method</label>
                                <select v-model="payoutForm.payment_method" class="field__input" required>
                                    <option value="" disabled>Select payment method</option>
                                    <option v-for="method in payoutMethods" :key="method" :value="method">{{ method }}
                                    </option>
                                </select>
                            </div>
                            <div class="field">
                                <label class="field__label">{{ payoutForm.payment_method }} Email/Address</label>
                                <input v-model="payoutForm.payment_email" type="text" class="field__input"
                                    :placeholder="`Your ${payoutForm.payment_method} email or address`" required />
                                <span v-if="payoutForm.errors.payment_email" class="field__error">{{
                                    payoutForm.errors.payment_email }}</span>
                            </div>
                            <button type="submit" class="btn-primary" :disabled="payoutForm.processing">
                                Request ${{ parseFloat(affiliate.pending_earnings).toFixed(2) }} Payout
                            </button>
                        </form>

                        <div class="payout-history-link">
                            <Link :href="route('affiliate.payouts')" class="link-muted">View payout history →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Visits by Tier -->
                <div class="card mt">
                    <h3 class="card-title">Visits by Tier</h3>
                    <div class="tiers-table-wrapper">
                        <table class="tiers-table">
                            <thead>
                                <tr>
                                    <th>Tier</th>
                                    <th>Countries</th>
                                    <th>Rate</th>
                                    <th>Unique Visits</th>
                                    <th>Earned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in visitsByTier" :key="row.tier_id">
                                    <td><span class="tier-name">{{ row.name }}</span></td>
                                    <td>
                                        <span class="countries-list">{{ tierCountries(row.tier_id) }}</span>
                                    </td>
                                    <td>${{ row.rate }} / {{ row.multiplier / 1000 }}k visits</td>
                                    <td>{{ row.visits.toLocaleString() }}</td>
                                    <td class="earned-cell">${{ row.earned.toFixed(4) }}</td>
                                </tr>
                                <tr v-if="!visitsByTier.length">
                                    <td colspan="5" class="no-data-cell">No visits yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>



            </template>
        </div>
    </AuthenticatedLayout>
</template>
<!-- ... -->

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Oswald:wght@300;400;500;600;700&display=swap');

:root {
    --font-display: 'Oswald', sans-serif;
    --font-body: 'DM Sans', sans-serif !important;
    --red: #e74c3c;
    --ink: #000000;
    --muted: #333333;
    --border: #e5e5e5;
    --surface: #fff;
    --radius: 4px;
}

.page-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 24px;
    width: 100%;
    overflow-x: hidden;
}

/* Material Icons */
.material-icons {
    font-size: 20px;
    vertical-align: middle;
    margin-right: 4px;
}

/* ── Enroll Section ─────────────────────────────── */
.enroll-section {
    max-width: 600px;
    margin: 40px auto;
    text-align: center;
}

.enroll-hero {
    margin-bottom: 32px;
}

.enroll-icon {
    font-size: 56px;
    display: block;
    margin-bottom: 16px;
    color: #1a1a1a;
}

.enroll-title {
    font-family: var(--font-display);
    font-size: 38px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 12px 0;
}

.enroll-desc {
    font-size: 17px;
    color: #444;
    line-height: 1.6;
    margin: 0;
}

.tiers-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

.tier-card {
    background: var(--surface);
    border: 2px solid var(--border);
    border-radius: 4px;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    transition: border-color 0.2s, transform 0.2s;
}

.tier-card:hover {
    border-color: var(--red);
    transform: translateY(-4px);
}

.tier-name {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #333;
}

.tier-rate {
    font-family: var(--font-display);
    font-size: 42px;
    font-weight: 700;
    color: #c00;
}

.tier-label {
    font-size: 13px;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.enroll-action {
    margin-top: 8px;
}

.btn-large {
    padding: 16px 40px;
    font-size: 18px;
    font-family: var(--font-display);
    font-weight: 600;
    letter-spacing: 1px;
}

.btn-large .material-icons,
.btn-sync .material-icons {
    font-size: 20px;
    margin-right: 6px;
}

/* ── Sync bar ───────────────────────────────── */
.sync-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #fafafa;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    margin-bottom: 16px;
}

.sync-note {
    font-size: 15px;
    color: #444;
    margin: 0;
}

.btn-sync {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: var(--red);
    color: #fff;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.5px;
    border-radius: var(--radius);
    border: none;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-sync:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.btn-sync:not(:disabled):hover {
    opacity: 0.75;
}

/* ── Stat row ─────────────────────────────────── */
.stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

.mini-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mini-stat__val {
    font-family: var(--font-display);
    font-size: 26px;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.02em;
}

.mini-stat__label {
    font-size: 13px;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ── Grid & Cards ────────────────────────────── */
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.mt {
    margin-top: 0;
}

.card-title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 18px;
}

/* ── Tier badge ──────────────────────────────── */
.tier-badge {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.tier-badge__name {
    font-size: 18px;
    font-weight: 700;
    color: var(--red);
}

.tier-badge__rate {
    font-size: 13px;
    color: var(--muted);
}

/* ── Progress ────────────────────────────────── */
.progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 6px;
}

.progress-track {
    height: 6px;
    background: var(--border);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--red);
    border-radius: 3px;
    transition: width 600ms ease;
}

.progress-hint {
    font-size: 11px;
    color: var(--muted);
    margin-top: 6px;
}

.top-tier-msg {
    font-size: 14px;
    color: #22C55E;
}

/* ── Referral box ────────────────────────────── */
.referral-box {
    margin-top: 20px;
    padding: 14px;
    background: #fef2f2;
    border: 1px solid var(--border);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.referral-box__label {
    font-size: 11px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.referral-box__row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.referral-box__code {
    font-family: var(--font-body);
    font-size: 15px;
    font-weight: 700;
    color: var(--red);
    letter-spacing: 0.05em;
}

/* ── Payout form ─────────────────────────────── */
.payout-locked {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.payout-locked p {
    font-size: 13px;
    color: var(--muted);
}

.payout-locked strong {
    color: var(--ink);
}

.payout-progress-track {
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
}

.payout-progress-fill {
    height: 100%;
    background: #22C55E;
    border-radius: 2px;
}

.payout-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.payout-form .field__input {
    width: 100%;
    box-sizing: border-box;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field__label {
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
}

.field__input {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    color: var(--ink);
    outline: none;
    transition: border-color 200ms;
}

.field__input:focus {
    border-color: var(--red);
}

.field__error {
    font-size: 12px;
    color: #EF4444;
}

.payout-history-link {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}

/* ── Country rates ───────────────────────────── */
.rate-grid {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.rate-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
}

.rate-chip__flag {
    font-size: 12px;
    color: var(--muted);
    font-weight: 600;
}

.rate-chip__val {
    font-size: 13px;
    color: var(--red);
    font-weight: 700;
}

/* ── Tiers Table ─────────────────────────────── */
.tiers-table-wrapper {
    overflow-x: auto;
}

.tiers-table {
    width: 100%;
    border-collapse: collapse;
}

.tiers-table th,
.tiers-table td {
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}

.tiers-table th {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    background: var(--surface);
}

.tiers-table td {
    font-size: 14px;
    color: var(--ink);
}

.tiers-table tr:hover td {
    background: #fafafa;
}

.tier-current td {
    background: #fef2f2;
}

.tier-name {
    font-family: var(--font-display);
    font-weight: 600;
    color: var(--red);
    display: flex;
    align-items: center;
    gap: 8px;
}

.tier-badge-current {
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--red);
    color: #fff;
    padding: 2px 6px;
    border-radius: 2px;
}

.countries-list {
    font-size: 12px;
    color: var(--muted);
}

.countries-none {
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
}

.current-tier-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.tier-stat {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.tier-stat__label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
}

.tier-stat__value {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
}

.referral-hint {
    font-size: 12px;
    color: var(--muted);
    margin-top: 12px;
    line-height: 1.5;
}

.how-to-use {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-family:'DM Sans';
    font-size:10px ;
}

.how-to-use__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #1a1a1a;
    margin: 0;
}

.how-to-use__step {
    font-size: 13px;
    color: #060606;
    margin: 0;
}

.how-to-use__example {
    font-size: 11px;
    color: var(--muted);
    margin: 4px 0 0 0;
    word-break: break-all;
}

.inline-code {
    background: #f0f0f0;
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 11px;
    color: var(--ink);
    font-family: var(--font-body);
}

.earned-cell {
    font-family: var(--font-display);
    font-weight: 700;
    color: var(--red);
}

.no-data-cell {
    text-align: center;
    padding: 24px;
    color: var(--muted);
    font-size: 13px;
    font-style: italic;
}

.earning-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.earning-desc {
    font-size: 14px;
    color: var(--ink);
    line-height: 1.6;
    margin: 0;
}

.earning-list {
    margin: 0;
    padding-left: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.earning-list li {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.5;
}

.earning-list strong {
    color: var(--red);
}

.earning-tiers {
    display: flex;
    flex-direction: column;
    gap: 0;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.earning-tier-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    gap: 16px;
}

.earning-tier-row:last-child {
    border-bottom: none;
}

.earning-tier-left {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
}

.earning-tier-name {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--red);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.earning-tier-countries {
    font-size: 11px;
    color: var(--muted);
    line-height: 1.4;
}

.earning-tier-rate {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 700;
    color: var(--ink);
    white-space: nowrap;
}

.earning-note {
    font-size: 13px;
    color: var(--muted);
    background: #fafafa;
    padding: 12px 16px;
    border-radius: var(--radius);
    margin: 0;
}

/* ── Buttons ─────────────────────────────────── */
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 22px;
    background: var(--red);
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-primary:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.btn-primary:not(:disabled):hover {
    opacity: 0.85;
}

.btn-ghost-sm {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: none;
    color: var(--muted);
    cursor: pointer;
    transition: all 200ms;
}

.btn-ghost-sm:hover {
    color: var(--ink);
    border-color: var(--ink);
}

.link-muted {
    font-size: 13px;
    color: #52525B;
    text-decoration: none;
}

.link-muted:hover {
    color: #A1A1AA;
}

@media (max-width: 768px) {
    .page-content {
        padding: 16px;
        max-width: 100%;
    }

    * {
        max-width: 100%;
        box-sizing: border-box;
    }

    .enroll-section {
        margin: 20px auto;
    }

    .enroll-hero {
        padding: 24px 16px;
    }

    .enroll-title {
        font-size: 24px;
    }

    .enroll-desc {
        font-size: 14px;
    }

    .tiers-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .tier-card {
        flex-direction: row;
        justify-content: space-between;
        padding: 16px 20px;
    }

    .tier-rate {
        font-size: 24px;
    }

    .stat-row {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .mini-stat {
        padding: 16px;
    }

    .mini-stat__val {
        font-size: 20px;
    }

    .mini-stat__label {
        font-size: 11px;
    }

    .sync-bar {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }

    .grid-2 {
        grid-template-columns: 1fr;
    }

    .card {
        padding: 20px;
    }

    .card-title {
        font-size: 14px;
    }

    .how-to-use__step {
        font-size: 13px;
    }

    .referral-box {
        padding: 16px;
    }

    .referral-input {
        font-size: 14px;
        padding: 12px;
    }

    .payout-form {
        padding: 20px;
    }

    .field {
        margin-bottom: 16px;
    }

    .field__label {
        font-size: 13px;
    }

    .field__input {
        padding: 10px 12px;
        font-size: 14px;
    }

    .toggle-group {
        flex-direction: column;
    }

    .toggle-btn {
        width: 100%;
        justify-content: center;
    }

    .amount-display {
        padding: 20px;
    }

    .amount-value {
        font-size: 32px;
    }

    .btn-primary {
        width: 100%;
        justify-content: center;
    }

    .btn-sync {
        width: 100%;
        justify-content: center;
    }

    .payout-history {
        padding: 16px;
    }

    .history-table {
        display: block;
        overflow-x: auto;
    }

    .history-table table {
        min-width: 600px;
    }

    .history-table th,
    .history-table td {
        padding: 12px 8px;
        font-size: 12px;
    }

    .status-badge {
        padding: 4px 8px;
        font-size: 10px;
    }
}
</style>