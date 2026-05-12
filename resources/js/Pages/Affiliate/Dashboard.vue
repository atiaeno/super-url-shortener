<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import '@/../css/affilates_dashboard.css';

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

const totalPendingEarningsIncludingReferrals = computed(() =>
    props.affiliate ? parseFloat(props.affiliate.pending_earnings) + parseFloat(props.affiliate.referral_pending_earnings) : 0
);

const referredAffiliatesCount = computed(() =>
    props.affiliate ? (props.affiliate.referred_users?.length || 0) : 0
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
                        <span class="mini-stat__label">Total Earnings</span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat__val">${{ parseFloat(affiliate.referral_earnings).toFixed(2) }}</span>
                        <span class="mini-stat__label">Referral Earnings</span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat__val">{{ totalPendingEarningsIncludingReferrals.toFixed(2) }}</span>
                        <span class="mini-stat__label">Total Pending</span>
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
                            <div class="referral-box__row">
                                
                                <a :href="shortUrl(affiliate.referral_code)" target="_blank" class="referral-link">{{
                                    shortUrl(affiliate.referral_code) }}</a>
                                <button @click="copyRef(shortUrl(affiliate.referral_code))" class="btn-ghost-sm">Copy
                                    Link</button>
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
                                    <option v-for="method in payoutMethods" :key="method" :value="method">{{ method
                                        }}
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

                <!-- Referral Section -->
                <div class="card mt" style="margin-top:25px">
                    <h3 class="card-title">Referral Program</h3>
                    <div class="referral-stats">
                        <div class="referral-stat">
                            <span class="referral-stat__val">{{ referredAffiliatesCount }}</span>
                            <span class="referral-stat__label">Referred Users</span>
                        </div>
                        <div class="referral-stat">
                            <span class="referral-stat__val">${{ parseFloat(affiliate.referral_earnings).toFixed(2)
                                }}</span>
                            <span class="referral-stat__label">Total Referral Earnings</span>
                        </div>
                        <div class="referral-stat">
                            <span class="referral-stat__val">${{
                                parseFloat(affiliate.referral_pending_earnings).toFixed(2) }}</span>
                            <span class="referral-stat__label">Pending Referral Earnings</span>
                        </div>
                    </div>

                    <div class="referral-info">
                        <h4 class="referral-info__title">How Referrals Work</h4>
                        <ul class="referral-info__list">
                            <li>Share your referral code with friends and colleagues</li>
                            <li>When they sign up and earn from their links, you get a commission</li>
                            <li>Commission rate: {{ (parseFloat(affiliate.referral_commission_rate) || 1.5) }}% of
                                their
                                earnings</li>
                            <li>Referral commissions last for the lifetime of their account</li>
                        </ul>
                    </div>
                </div>

            </template>
        </div>
    </AuthenticatedLayout>
</template>
<!-- ... -->

