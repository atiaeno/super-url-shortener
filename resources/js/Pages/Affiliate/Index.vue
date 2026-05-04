<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    tiers: { type: Array, default: () => [] },
});

const formatThreshold = (val) => {
    const num = parseInt(val) || 0;
    return num >= 1000 ? `${(num / 1000).toFixed(0)}k+ visits` : `${num}+ visits`;
};
</script>

<template>

    <Head title="Affiliate Program — ShortLink" />

    <div class="editorial">
        <Masthead variant="blend" :show-nav="true" />

        <section class="hero-theater">
            <div class="hero-backdrop"></div>
            <div class="hero-grid"></div>

            <div class="hero-content">
                <div class="hero-meta">
                    <span class="hero-issue">Partnership</span>
                    <span class="hero-divider"></span>
                    <span class="hero-season">Earn Revenue</span>
                </div>

                <div class="hero-typography">
                    <h1 class="hero-headline">
                        <span class="line-1">Earn with</span>
                        <span class="line-2">Every <em>Click</em></span>
                        <span class="line-3">Share & <span class="highlight">Profit</span></span>
                    </h1>

                    <p class="hero-subdeck">
                        Join our affiliate program and earn commissions for every visit
                        your short links generate. Country-tiered rates mean big markets pay more.
                    </p>
                </div>

                <div class="hero-action">
                    <div v-if="canLogin" class="cta-actions">
                        <Link v-if="canRegister" :href="route('register')" class="cta-btn cta-btn--primary">
                            Get Started
                        </Link>
                        <Link v-if="canLogin" :href="route('login')" class="cta-btn cta-btn--secondary">
                            Sign In
                        </Link>
                    </div>
                    <p v-else class="auth-notice">
                        <Link :href="route('dashboard')" class="link-gold">Go to Dashboard →</Link>
                    </p>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="content-section">
            <div class="section-container">
                <header class="section-header">
                    <span class="section-label">The Program</span>
                    <h2 class="section-title">How It Works</h2>
                </header>

                <div class="steps-grid">
                    <div class="step-card">
                        <span class="step-num">01</span>
                        <h3 class="step-title">Share Links</h3>
                        <p class="step-desc">
                            Create short links using your unique referral code and share them anywhere.
                        </p>
                    </div>
                    <div class="step-card">
                        <span class="step-num">02</span>
                        <h3 class="step-title">Earn Clicks</h3>
                        <p class="step-desc">
                            Get paid for every visitor from supported countries that clicks your links.
                        </p>
                    </div>
                    <div class="step-card">
                        <span class="step-num">03</span>
                        <h3 class="step-title">Get Paid</h3>
                        <p class="step-desc">
                            Request payouts via PayPal once you reach the minimum threshold.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tiers -->
        <section class="content-section section--alt">
            <div class="section-container">
                <header class="section-header">
                    <span class="section-label">Commission</span>
                    <h2 class="section-title">Tier Structure</h2>
                </header>

                <div class="tiers-table-wrapper">
                    <table class="tiers-table">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Visit Threshold</th>
                                <th>Base Rate</th>
                                <th>Premium Markets</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tier in tiers" :key="tier.id">
                                <td><span class="tier-name">{{ tier.name }}</span></td>
                                <td>{{ formatThreshold(tier.visit_threshold) }}</td>
                                <td>{{ tier.commission_rate }}%</td>
                                <td>Premium markets +bonus</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <div class="cta-container">
                <h2 class="cta-headline">Ready to Start Earning?</h2>
                <p class="cta-sub">Join thousands of affiliates earning passive income</p>
                <div class="cta-actions">
                    <Link v-if="canRegister" :href="route('register')" class="cta-btn cta-btn--primary">
                        Join Now
                    </Link>
                    <Link v-if="canLogin" :href="route('login')" class="cta-btn cta-btn--secondary">
                        Partner Login
                    </Link>
                </div>
            </div>
        </section>

        <EditorialFooter />
    </div>
</template>

<style scoped>
/* ── Hero ─────────────────────────────────────── */
.hero-theater {
    position: relative;
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 120px 24px 80px;
    overflow: hidden;
}

.hero-backdrop {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, #0a0a0a 0%, #1a1a1a 100%);
}

.hero-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
    background-size: 60px 60px;
    mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, black 40%, transparent 100%);
}

.hero-content {
    position: relative;
    max-width: 800px;
    text-align: center;
}

.hero-meta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-bottom: 32px;
}

.hero-issue,
.hero-season {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #888;
}

.hero-divider {
    width: 40px;
    height: 1px;
    background: #333;
}

.hero-typography {
    margin-bottom: 48px;
}

.hero-headline {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(48px, 8vw, 96px);
    font-weight: 700;
    line-height: 0.95;
    color: #fff;
    letter-spacing: -2px;
    margin: 0 0 24px;
}

.hero-headline .line-1,
.hero-headline .line-2,
.hero-headline .line-3 {
    display: block;
}

.hero-headline em {
    font-style: normal;
    color: #e74c3c;
}

.hero-headline .highlight {
    color: #e74c3c;
}

.hero-subdeck {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    color: #888;
    max-width: 560px;
    margin: 0 auto;
    line-height: 1.6;
}

/* ── CTA Buttons ───────────────────────────────── */
.cta-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 32px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-decoration: none;
    transition: all 0.3s;
}

.cta-btn--primary {
    background: #e74c3c;
    color: #0a0a0a;
    border: none;
}

.cta-btn--primary:hover {
    background: #e5c158;
    transform: translateY(-2px);
}

.cta-btn--secondary {
    background: transparent;
    color: #fff;
    border: 1px solid #444;
}

.cta-btn--secondary:hover {
    border-color: #e74c3c;
    color: #e74c3c;
}

.auth-notice {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: #888;
}

.link-gold {
    color: #e74c3c;
    text-decoration: none;
}

.link-gold:hover {
    text-decoration: underline;
}

/* ── Content Sections ─────────────────────────── */
.content-section {
    padding: 100px 24px;
}

.section-container {
    max-width: 900px;
    margin: 0 auto;
}

.section--alt {
    background: #0f0f0f;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-label {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #e74c3c;
    margin-bottom: 12px;
}

.section-title {
    font-family: 'Oswald', sans-serif;
    font-size: 42px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

/* ── Steps Grid ────────────────────────────────── */
.steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
}

.step-card {
    text-align: center;
    padding: 40px 24px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid #222;
    border-radius: 4px;
}

.step-num {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 48px;
    font-weight: 700;
    color: #e74c3c;
    opacity: 0.4;
    margin-bottom: 16px;
}

.step-title {
    font-family: 'Oswald', sans-serif;
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 12px;
}

.step-desc {
    font-size: 14px;
    color: #888;
    line-height: 1.6;
    margin: 0;
}

/* ── Tiers Table ──────────────────────────────── */
.tiers-table-wrapper {
    overflow-x: auto;
}

.tiers-table {
    width: 100%;
    border-collapse: collapse;
}

.tiers-table th,
.tiers-table td {
    padding: 20px 24px;
    text-align: left;
    border-bottom: 1px solid #222;
}

.tiers-table th {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
    background: #1a1a1a;
}

.tiers-table td {
    font-size: 14px;
    color: #ccc;
}

.tiers-table tr:hover td {
    background: rgba(212, 175, 55, 0.03);
}

.tier-name {
    font-family: 'Oswald', sans-serif;
    font-weight: 600;
    color: #e74c3c;
}

/* ── CTA Section ───────────────────────────────── */
.cta-section {
    padding: 120px 24px;
    background: linear-gradient(180deg, #1a1a1a 0%, #0a0a0a 100%);
    text-align: center;
}

.cta-container {
    max-width: 600px;
    margin: 0 auto;
}

.cta-headline {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 700;
    color: #fff;
    margin: 0 0 20px;
}

.cta-sub {
    font-size: 18px;
    color: #888;
    margin: 0 0 40px;
}

@media (max-width: 768px) {
    .steps-grid {
        grid-template-columns: 1fr;
    }

    .tiers-table th,
    .tiers-table td {
        padding: 14px 16px;
        font-size: 12px;
    }
}
</style>
