<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const formatRate = (rate, multiplier) => {
    const r = parseFloat(rate) || 0;
    const m = parseInt(multiplier) || 10000;
    const displayMult = m >= 1000 ? `${m / 1000}k` : m;
    return `$${r.toFixed(2)} / ${displayMult} views`;
};

const getCountryCount = (tier) => {
    return tier.country_rates?.length || 0;
};

const showCountriesModal = ref(false);
const selectedTierCountries = ref([]);

const openCountriesModal = (tier) => {
    selectedTierCountries.value = tier.country_rates || [];
    showCountriesModal.value = true;
};

const closeCountriesModal = () => {
    showCountriesModal.value = false;
    selectedTierCountries.value = [];
};

const countryName = (code) => {
    const countries = {
        US: 'United States', GB: 'United Kingdom', DE: 'Germany', FR: 'France', CA: 'Canada',
        AU: 'Australia', JP: 'Japan', BR: 'Brazil', IN: 'India', IT: 'Italy', ES: 'Spain',
        NL: 'Netherlands', SE: 'Sweden', NO: 'Norway', DK: 'Denmark', FI: 'Finland',
        CH: 'Switzerland', AT: 'Austria', BE: 'Belgium', IE: 'Ireland', PL: 'Poland',
        MX: 'Mexico', KR: 'South Korea', SG: 'Singapore', HK: 'Hong Kong', NZ: 'New Zealand',
    };
    return countries[code] || code;
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
                                <th>Rate</th>
                                <th>Countries</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tier in tiers" :key="tier.id">
                                <td><span class="tier-name">{{ tier.name }}</span></td>
                                <td>{{ formatRate(tier.view_rate, tier.view_multiplier) }}</td>
                                <td>
                                    <button v-if="getCountryCount(tier) > 0" @click="openCountriesModal(tier)"
                                        class="countries-link">
                                        {{ getCountryCount(tier) }} countries
                                    </button>
                                    <span v-else class="countries-none">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- CTA SECTION -->
        <section class="cta-section">
            <div class="cta-backdrop">
                <div class="cta-noise"></div>
            </div>

            <div class="cta-content">
                <h2 class="cta-headline">Ready to start earning?</h2>
                <p class="cta-sub">Join thousands who've already joined our affiliate program.</p>

                <div class="cta-actions">
                    <template v-if="!canLogin">
                        <Link :href="route('register')" class="cta-btn cta-btn--primary">
                            Join Now
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('affiliate.index')" class="cta-btn cta-btn--primary">
                            Go to Dashboard
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </Link>
                        <Link :href="route('dashboard')" class="cta-btn cta-btn--ghost">View Links</Link>
                    </template>
                </div>

                <p class="cta-footnote">No credit card required. Free to join.</p>
            </div>
            <!-- ... -->
        </section>

        <!-- Countries Modal -->
        <Teleport to="body">
            <div v-if="showCountriesModal" class="modal-overlay" @click="closeCountriesModal">
                <div class="modal" @click.stop>
                    <div class="modal__header">
                        <h3 class="modal__title">Premium Countries</h3>
                        <button @click="closeCountriesModal" class="modal__close">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal__body">
                        <div class="countries-grid">
                            <div v-for="rate in selectedTierCountries" :key="rate.country_code" class="country-item">
                                <span class="country-code">{{ rate.country_code }}</span>
                                <span class="country-name">{{ countryName(rate.country_code) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

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
    font-weight: 700;
    line-height: 0.9;
    letter-spacing: -2px;
    margin: 0;
}

.hero-headline .line-1 {
    display: block;
    font-size: clamp(48px, 10vw, 100px);
    color: #fff;
}

.hero-headline .line-2 {
    display: block;
    font-size: clamp(36px, 8vw, 72px);
    color: #fafafa;
    margin-top: 8px;
}

.hero-headline .line-2 em {
    font-family: 'Crimson Pro', serif;
    font-style: italic;
    font-weight: 400;
    color: #e74c3c;
}

.hero-headline .line-3 {
    display: block;
    font-size: clamp(24px, 5vw, 48px);
    color: #888;
    margin-top: 8px;
}

.hero-headline .highlight {
    color: #d4af37;
    font-size: 1.5em;
    line-height: 0.5;
}

.hero-subdeck {
    font-size: clamp(18px, 2vw, 22px);
    line-height: 1.6;
    max-width: 600px;
    margin: 32px auto 0;
    color: #aaa;
    font-weight: 400;
}

/* ── CTA Buttons ───────────────────────────────── */
.hero-actions-row {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: transparent;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 16px 32px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.cta-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}

.cta-btn--primary {
    background: #e74c3c;
    border-color: #e74c3c;
}

.cta-btn--primary:hover {
    background: #c0392b;
    border-color: #c0392b;
}

.auth-notice {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: #888;
}

.link-gold {
    color: #d4af37;
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
    background: rgba(231, 76, 60, 0.05);
}

.tier-name {
    font-family: 'Oswald', sans-serif;
    font-weight: 600;
    color: #e74c3c;
}

/* ── CTA SECTION ───────────────────────────────── */
.cta-section {
    position: relative;
    padding: 160px 60px;
    background: #0a0a0a;
    text-align: center;
    overflow: hidden;
}

.cta-backdrop {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 40% at 50% 100%, rgba(231, 76, 60, 0.15) 0%, transparent 60%);
}

.cta-noise {
    position: absolute;
    inset: 0;
    opacity: 0.02;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}

.cta-content {
    position: relative;
    z-index: 2;
    max-width: 700px;
    margin: 0 auto;
}

.cta-headline {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 700;
    color: #fff;
    letter-spacing: -1px;
    margin: 0 0 20px;
}

.cta-sub {
    font-size: 20px;
    color: #888;
    margin: 0 0 40px;
}

.cta-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 32px;
}

.cta-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 36px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-decoration: none;
    transition: all 0.3s;
}

.cta-btn--primary {
    background: #e74c3c;
    color: #fff;
    border: none;
}

.cta-btn--primary:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);
}

.cta-btn--primary svg {
    width: 18px;
    height: 18px;
}

.cta-btn--ghost {
    background: transparent;
    color: #888;
    border: 1px solid #444;
}

.cta-btn--ghost:hover {
    border-color: #d4af37;
    color: #d4af37;
}

.cta-footnote {
    font-size: 14px;
    color: #555;
    margin: 0;
}

/* ── Countries Link ───────────────────────────── */
.countries-link {
    background: none;
    border: none;
    color: #e74c3c;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.countries-link:hover {
    color: #c0392b;
}

.countries-none {
    color: #666;
}

/* ── Modal ─────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 24px;
}

.modal {
    background: #1a1a1a;
    border: 1px solid #333;
    border-radius: 4px;
    max-width: 500px;
    width: 100%;
    max-height: 80vh;
    overflow: auto;
}

.modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #333;
}

.modal__title {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.modal__close {
    background: none;
    border: none;
    color: #888;
    cursor: pointer;
    padding: 4px;
    display: flex;
}

.modal__close svg {
    width: 20px;
    height: 20px;
}

.modal__close:hover {
    color: #fff;
}

.modal__body {
    padding: 24px;
}

.countries-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.country-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid #222;
    border-radius: 4px;
}

.country-code {
    font-family: 'Oswald', sans-serif;
    font-weight: 700;
    color: #e74c3c;
    min-width: 40px;
}

.country-name {
    flex: 1;
    color: #ccc;
    font-size: 14px;
}

.country-rate {
    font-family: 'Oswald', sans-serif;
    font-weight: 600;
    color: #22c55e;
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
