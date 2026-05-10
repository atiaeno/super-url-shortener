<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

// Animated counter for stats
const count1 = ref(0);
const count2 = ref(0);
const count3 = ref(0);
const target1 = 10000000; // 10M
const target2 = 195;       // Countries
const target3 = 99.99;     // Uptime

const animateValue = (ref_val, target, duration = 2000, isFloat = false) => {
    const start = performance.now();
    const animate = (now) => {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = target * easeOut;
        ref_val.value = isFloat ? current.toFixed(2) : Math.floor(current);
        if (progress < 1) requestAnimationFrame(animate);
    };
    requestAnimationFrame(animate);
};

let observer;
onMounted(() => {
    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateValue(count1, target1, 2500);
                animateValue(count2, target2, 1500);
                animateValue(count3, target3, 2000, true);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) observer.observe(statsSection);
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});

const props = defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
    donationEnabled: { type: Boolean, default: false },
    donationButtonId: { type: String, default: '' },
});

const url = ref('');
const result = ref(null);
const error = ref(null);
const loading = ref(false);
const copied = ref(false);

const shorten = async () => {
    error.value = null;
    result.value = null;
    loading.value = true;
    try {
        const res = await axios.post('/guest/shorten', { url: url.value });
        result.value = res.data;
    } catch (e) {
        error.value = e.response?.data?.error ?? 'Something went wrong';
    } finally {
        loading.value = false;
    }
};

const copy = async () => {
    await navigator.clipboard.writeText(result.value.short_url);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const scrollToSection = (id) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>

    <Head :title="`${appName} — Editorial`" />

    <div class="editorial">
        <Masthead variant="blend" :show-nav="true" />

        <!-- NEW HERO: Dramatic Typography-First Layout -->
        <section class="hero-theater">
            <div class="hero-backdrop"></div>
            <div class="hero-grid"></div>

            <div class="hero-content">
                <div class="hero-meta">
                    <span class="hero-issue">Vol. XLVII</span>
                    <span class="hero-divider"></span>
                    <span class="hero-season">Spring MMXXV</span>
                </div>

                <div class="hero-typography">
                    <h1 class="hero-headline">
                        <span class="line-1">Compress</span>
                        <span class="line-2">the <em>World</em></span>
                        <span class="line-3">into <span class="highlight">/</span></span>
                    </h1>

                    <p class="hero-subdeck">
                        The definitive URL shortening platform. Where every link becomes a precision instrument
                        for tracking, sharing, and understanding digital presence.
                    </p>
                </div>

                <!-- Quick Shorten Form -->
                <div class="hero-action">
                    <form class="theater-form" @submit.prevent="shorten">
                        <div class="form-stage">
                            <input v-model="url" type="url" placeholder="Paste your long URL here..." required
                                class="theater-input" />
                            <button type="submit" class="theater-btn" :disabled="loading">
                                <span v-if="loading">Processing</span>
                                <span v-else>Shorten</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <div v-if="error" class="form-message form-message--error">{{ error }}</div>

                        <div v-if="result" class="form-message form-message--success">
                            <div class="result-main">
                                <a :href="result.short_url" target="_blank" class="result-link">{{ result.short_url
                                }}</a>
                                <button type="button" @click="copy" class="copy-mini">
                                    {{ copied ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                            <div class="result-qr">
                                <img :src="`/guest/qr/${result.short_code}`" alt="QR Code" class="qr-image" />
                                <span class="qr-label">Scan to open</span>
                            </div>
                        </div>
                    </form>

                    <p v-if="!$page.props.auth?.user" class="hero-footnote">
                        <Link :href="route('register')" class="accent-link">Create an account</Link>
                        for saved links & advanced analytics
                    </p>

                    <div class="hero-actions-row">
                        <Link :href="route('links.bulk')" class="editorial-btn editorial-btn--accent">
                            Bulk Shorten
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Floating Stats Cards -->
            <div class="hero-stats-float">
                <div class="stat-float-card">
                    <span class="stat-float-num">10M+</span>
                    <span class="stat-float-label">Links Created</span>
                </div>
                <div class="stat-float-card">
                    <span class="stat-float-num">99.9%</span>
                    <span class="stat-float-label">Uptime</span>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <button class="scroll-hint" @click="scrollToSection('manifesto')">
                <span class="scroll-hint-text">Discover</span>
                <span class="scroll-hint-line"></span>
            </button>
        </section>

        <!-- BRIDGE: white section under dark hero -->
        <section class="bridge-section">
            <div class="bridge-inner">
                <div class="bridge-item">
                    <svg class="bridge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                    </svg>
                    <div>
                        <strong>Sub-50ms Redirects</strong>
                        <span>Global edge network</span>
                    </div>
                </div>
                <div class="bridge-divider"></div>
                <div class="bridge-item">
                    <svg class="bridge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                        <path d="M22 12A10 10 0 0 0 12 2v10z" />
                    </svg>
                    <div>
                        <strong>Real-time Analytics</strong>
                        <span>Every click tracked</span>
                    </div>
                </div>
                <div class="bridge-divider"></div>
                <div class="bridge-item">
                    <svg class="bridge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <div>
                        <strong>Enterprise Security</strong>
                        <span>HTTPS by default</span>
                    </div>
                </div>
                <div class="bridge-divider"></div>
                <div class="bridge-item">
                    <svg class="bridge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="3" />
                        <path
                            d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" />
                    </svg>
                    <div>
                        <strong>Custom Aliases</strong>
                        <span>Your brand, your links</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- MANIFESTO SECTION -->
        <section id="manifesto" class="manifesto-section">
            <div class="manifesto-backdrop">
                <div class="noise-overlay"></div>
            </div>

            <div class="manifesto-content">
                <span class="section-marker">I.</span>
                <h2 class="manifesto-headline">The Art of Digital Brevity</h2>
                <div class="manifesto-columns">
                    <p class="manifesto-text">
                        In an age of information overload, the ability to condense matters.
                        We believe that every URL should be memorable, trackable, and beautiful.
                        Not merely a redirect, but a statement of intent.
                    </p>
                    <p class="manifesto-text">
                        Since MMXXV, we've been building the infrastructure for a more elegant web.
                        Edge-cached globally. Analytics that matter. Privacy by design.
                    </p>
                </div>

                <blockquote class="manifesto-quote">
                    <span class="quote-mark">"</span>
                    The shortest distance between two points is now a single character.
                </blockquote>
            </div>
        </section>

        <!-- CAPABILITIES SECTION -->
        <section class="capabilities-section">
            <div class="capabilities-header">
                <span class="section-marker">II.</span>
                <h2>Capabilities</h2>
                <p>Everything you need. Nothing you don't.</p>
            </div>

            <div class="capabilities-grid">
                <div class="cap-card">
                    <div class="cap-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <div class="cap-meta">&lt; 50ms</div>
                    <h3>Instant Redirection</h3>
                    <p>Sub-50ms response times through our global edge network. Your visitors never wait.</p>
                </div>

                <div class="cap-card">
                    <div class="cap-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                            <path d="M22 12A10 10 0 0 0 12 2v10z" />
                        </svg>
                    </div>
                    <div class="cap-meta">Live Data</div>
                    <h3>Real-time Analytics</h3>
                    <p>Geographic data, device types, referrers, and time-series all in one dashboard.</p>
                </div>

                <div class="cap-card">
                    <div class="cap-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>
                    <div class="cap-meta">Always On</div>
                    <h3>Enterprise Security</h3>
                    <p>HTTPS by default, link expiration, password protection, and abuse detection.</p>
                </div>

                <div class="cap-card">
                    <div class="cap-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                    </div>
                    <div class="cap-meta">Your Brand</div>
                    <h3>Custom Aliases</h3>
                    <p>Memorable short URLs that reflect your brand identity and speak your language.</p>
                </div>

                <div class="cap-card">
                    <div class="cap-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                    <div class="cap-meta">No Lock-in</div>
                    <h3>Open API</h3>
                    <p>Full REST API access. Integrate ShortLink into your stack with comprehensive docs.</p>
                </div>

                <div class="cap-card cap-card--accent">
                    <div class="cap-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                        </svg>
                    </div>
                    <div class="cap-meta">Free Forever</div>
                    <h3>Get Started Now</h3>
                    <p>No credit card required. Essential tools accessible to all, without compromise.</p>
                    <Link :href="route('register')" class="cap-link">Create Account →</Link>
                </div>
            </div>
        </section>

        <!-- STATS SECTION -->
        <section class="stats-section">
            <div class="stats-backdrop">
                <div class="stats-grid"></div>
            </div>

            <div class="stats-content">
                <div class="stat-item">
                    <span class="stat-number">{{ count1.toLocaleString() }}+</span>
                    <span class="stat-label">Links Shortened</span>
                    <span class="stat-roman">I.</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ count2 }}</span>
                    <span class="stat-label">Countries Reached</span>
                    <span class="stat-roman">II.</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ count3 }}%</span>
                    <span class="stat-label">Uptime Guarantee</span>
                    <span class="stat-roman">III.</span>
                </div>
            </div>

            <div class="stats-footnote">
                <p>Trusted by independent creators and enterprise teams alike</p>
            </div>
        </section>

        <!-- TESTIMONIALS SECTION -->
        <section class="testimonials-section">
            <div class="testimonials-header">
                <span class="section-marker">III.</span>
                <h2>Endorsements</h2>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-quote">
                        "The analytics alone are worth it. Finally, a URL shortener that treats data visualization as
                        art."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">MK</div>
                        <div class="author-info">
                            <span class="author-name">Maya Kowalski</span>
                            <span class="author-title">Editor-in-Chief, Design Quarterly</span>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card testimonial-card--featured">
                    <div class="testimonial-quote">
                        "We migrated 50,000+ links from Bitly. The API is elegant, the dashboard is beautiful, and the
                        team actually responds to feedback."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">JL</div>
                        <div class="author-info">
                            <span class="author-name">James Li</span>
                            <span class="author-title">CTO, Meridian Digital</span>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-quote">
                        "Free for personal use but built like an enterprise tool. The custom aliases feature is perfect
                        for our brand campaigns."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">SP</div>
                        <div class="author-info">
                            <span class="author-name">Sofia Petrov</span>
                            <span class="author-title">Marketing Director, Atelier Studio</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section class="cta-section">
            <div class="cta-backdrop">
                <div class="cta-noise"></div>
            </div>

            <div class="cta-content">
                <h2 class="cta-headline">Ready to shorten?</h2>
                <p class="cta-sub">Join thousands who've already compressed their digital footprint.</p>

                <div class="cta-actions">
                    <template v-if="!$page.props.auth?.user">
                        <Link :href="route('register')" class="cta-btn cta-btn--primary">
                            Create Free Account
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </Link>
                        <Link :href="route('login')" class="cta-btn cta-btn--ghost">Sign In</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('links.create')" class="cta-btn cta-btn--primary">
                            Create Your First Link
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </Link>
                        <Link :href="route('dashboard')" class="cta-btn cta-btn--ghost">Go to Dashboard</Link>
                    </template>
                </div>

                <p class="cta-footnote">No credit card required. Free plan includes unlimited links.</p>
            </div>
        </section>

        <EditorialFooter :donation-enabled="donationEnabled" :donation-button-id="donationButtonId" />
    </div>
</template>

<style scoped>
/* ── EDITORIAL MAGAZINE ─ THEATER EDITION ───────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,500;0,600;1,400&family=Oswald:wght@400;500;600;700&display=swap');

* {
    box-sizing: border-box;
}

.editorial {
    font-family: 'Crimson Pro', serif;
    background: #fafafa;
    color: #1a1a1a;
    min-height: 100vh;
    overflow-x: hidden;
}

/* ── SECTION MARKER UTILITY ───────────────────────────────── */
.section-marker {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #e74c3c;
    display: block;
    margin-bottom: 16px;
}

/* ── HERO THEATER ───────────────────────────────────────────── */
.hero-theater {
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #0a0a0a;
}

.hero-backdrop {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 50% at 20% 50%, rgba(231, 76, 60, 0.15) 0%, transparent 50%),
        radial-gradient(ellipse 60% 40% at 80% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
        linear-gradient(180deg, #0a0a0a 0%, #1a1a1a 100%);
}

.hero-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 100px 100px;
    mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 0%, transparent 70%);
}

.hero-content {
    position: relative;
    z-index: 10;
    text-align: center;
    max-width: 900px;
    padding: 140px 40px 80px;
    animation: heroFadeIn 1.2s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes heroFadeIn {
    from {
        opacity: 0;
        transform: translateY(40px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-typography {
    animation: heroFadeIn 1.2s 0.15s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.hero-action {
    animation: heroFadeIn 1.2s 0.3s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.hero-meta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 40px;
}

.hero-issue,
.hero-season {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #888;
}

.hero-divider {
    width: 60px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
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
    font-size: clamp(48px, 10vw, 120px);
    color: #fff;
}

.hero-headline .line-2 {
    display: block;
    font-size: clamp(36px, 8vw, 80px);
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

/* Hero Action Form */
.hero-action {
    max-width: 600px;
    margin: 0 auto;
}

.theater-form {
    position: relative;
}

.form-stage {
    display: flex;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    padding: 4px;
    border-radius: 4px;
}

.theater-input {
    flex: 1;
    background: transparent;
    border: none;
    padding: 20px 24px;
    font-family: 'Crimson Pro', serif;
    font-size: 18px;
    color: #fff;
    outline: none;
}

.theater-input::placeholder {
    color: #666;
    font-style: italic;
}

.theater-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 16px 28px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s;
    border-radius: 2px;
}

.theater-btn svg {
    width: 18px;
    height: 18px;
}

.theater-btn:hover:not(:disabled) {
    background: #c0392b;
    transform: translateX(4px);
}

.theater-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Editorial Button Styles */
.hero-actions-row {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.editorial-btn {
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

.editorial-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}

.editorial-btn--accent {
    background: #e74c3c;
    border-color: #e74c3c;
}

.editorial-btn--accent:hover {
    background: #c0392b;
    border-color: #c0392b;
}

.form-message {
    margin-top: 16px;
    padding: 16px 20px;
    border-radius: 4px;
    font-size: 15px;
}

.form-message--error {
    background: rgba(231, 76, 60, 0.15);
    border: 1px solid rgba(231, 76, 60, 0.3);
    color: #e74c3c;
}

.form-message--success {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(212, 175, 55, 0.3);
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 24px;
}

.result-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    width: 100%;
}

.result-qr {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    width: 100%;
}

.qr-image {
    width: 140px;
    height: 140px;
    background: #fff;
    padding: 12px;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.qr-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
}

.result-link {
    color: #d4af37;
    text-decoration: none;
    font-size: 18px;
    font-weight: 600;
    word-break: break-all;
}

.result-link:hover {
    text-decoration: underline;
    color: #e5c158;
}

.copy-mini {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid #d4af37;
    color: #d4af37;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
}

.copy-mini:hover {
    background: #d4af37;
    color: #0a0a0a;
}

.hero-footnote {
    margin-top: 20px;
    font-size: 15px;
    color: #666;
}

.accent-link {
    color: #d4af37;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.3s;
}

.accent-link:hover {
    border-bottom-color: #d4af37;
}

/* Floating Stats */
.hero-stats-float {
    position: absolute;
    bottom: 120px;
    left: 60px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    z-index: 5;
}

.stat-float-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 16px 24px;
    backdrop-filter: blur(10px);
}

.stat-float-num {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #d4af37;
    letter-spacing: 1px;
}

.stat-float-label {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
}

/* Scroll Hint */
.scroll-hint {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: #666;
    transition: color 0.3s;
}

.scroll-hint:hover {
    color: #d4af37;
}

.scroll-hint-text {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
}

.scroll-hint-line {
    width: 1px;
    height: 40px;
    background: linear-gradient(180deg, currentColor, transparent);
    animation: linePulse 2s ease-in-out infinite;
}

@keyframes linePulse {

    0%,
    100% {
        opacity: 1;
        transform: scaleY(1);
    }

    50% {
        opacity: 0.5;
        transform: scaleY(0.7);
    }
}

/* ── BRIDGE SECTION ─────────────────────────────────────────── */
.bridge-section {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    padding: 0;
}

.bridge-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: stretch;
    padding: 0 60px;
}

.bridge-item {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 32px 24px;
    transition: background 0.2s;
}

.bridge-item:hover {
    background: #fafafa;
}

.bridge-item strong {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #1a1a1a;
    margin-bottom: 3px;
}

.bridge-item span {
    font-size: 13px;
    color: #888;
}

.bridge-icon {
    width: 28px;
    height: 28px;
    flex-shrink: 0;
    color: #e74c3c;
}

.bridge-divider {
    width: 1px;
    background: #eee;
    align-self: stretch;
    margin: 16px 0;
}

/* ── MANIFESTO SECTION ─────────────────────────────────────── */
.manifesto-section {
    position: relative;
    padding: 160px 60px;
    background: #1a1a1a;
    overflow: hidden;
}

.manifesto-backdrop {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 50% 30% at 70% 70%, rgba(231, 76, 60, 0.1) 0%, transparent 60%);
}

.noise-overlay {
    position: absolute;
    inset: 0;
    opacity: 0.03;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}

.manifesto-content {
    position: relative;
    z-index: 2;
    max-width: 1000px;
    margin: 0 auto;
}

.manifesto-headline {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 700;
    color: #fff;
    letter-spacing: -1px;
    margin: 0 0 40px;
    line-height: 1.1;
}

.manifesto-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    margin-bottom: 48px;
}

.manifesto-text {
    font-size: 18px;
    line-height: 1.8;
    color: #888;
    margin: 0;
}

.manifesto-quote {
    position: relative;
    padding-left: 40px;
    border-left: 3px solid #d4af37;
    font-size: 28px;
    font-style: italic;
    color: #ccc;
    line-height: 1.5;
    margin: 0;
}

.quote-mark {
    font-family: 'Oswald', sans-serif;
    font-size: 80px;
    color: #e74c3c;
    position: absolute;
    top: -30px;
    left: -20px;
    opacity: 0.3;
    line-height: 1;
}

/* ── CAPABILITIES SECTION ───────────────────────────────────── */
.capabilities-section {
    padding: 140px 60px;
    background: #fafafa;
}

.capabilities-header {
    text-align: center;
    margin-bottom: 80px;
}

.capabilities-header h2 {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(32px, 4vw, 48px);
    font-weight: 700;
    letter-spacing: -1px;
    margin: 0 0 16px;
}

.capabilities-header p {
    font-size: 18px;
    color: #666;
    margin: 0;
}

.capabilities-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    max-width: 1200px;
    margin: 0 auto;
    border: 1px solid #eee;
    background: #eee;
}

.cap-card {
    background: #fff;
    padding: 48px 40px;
    position: relative;
    transition: all 0.25s;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cap-card:hover {
    background: #fafafa;
    z-index: 2;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
}

.cap-card--accent {
    background: #1a1a1a;
    color: #fff;
}

.cap-card--accent:hover {
    background: #111;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.cap-card--accent .cap-icon {
    color: #e74c3c;
}

.cap-card--accent h3 {
    color: #fff;
}

.cap-card--accent p {
    color: #888;
}

.cap-card--accent .cap-meta {
    color: #d4af37;
}

.cap-card--accent .cap-link {
    color: #d4af37;
}

.cap-icon {
    width: 48px;
    height: 48px;
    color: #e74c3c;
}

.cap-icon svg {
    width: 100%;
    height: 100%;
}

.cap-card h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 17px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin: 0;
    color: #1a1a1a;
}

.cap-card p {
    font-size: 15px;
    line-height: 1.65;
    color: #666;
    margin: 0;
    flex: 1;
}

.cap-meta {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: #d4af37;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0;
}

.cap-link {
    display: inline-block;
    margin-top: 24px;
    color: #d4af37;
    text-decoration: none;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 1px;
    transition: transform 0.3s;
}

.cap-link:hover {
    transform: translateX(8px);
}

/* ── STATS SECTION ─────────────────────────────────────────── */
.stats-section {
    position: relative;
    padding: 140px 60px;
    background: #f5f5f5;
    text-align: center;
}

.stats-backdrop {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.stats-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(0, 0, 0, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
    background-size: 60px 60px;
    opacity: 0.5;
}

.stats-content {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: center;
    gap: 80px;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
    position: relative;
}

.stat-number {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: clamp(48px, 6vw, 72px);
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -2px;
    line-height: 1;
}

.stat-label {
    display: block;
    font-size: 14px;
    color: #666;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-top: 12px;
}

.stat-roman {
    position: absolute;
    top: -20px;
    left: -20px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: #e74c3c;
    opacity: 0.5;
}

.stats-footnote {
    margin-top: 60px;
    font-size: 15px;
    color: #888;
    font-style: italic;
}

/* ── TESTIMONIALS SECTION ─────────────────────────────────── */
.testimonials-section {
    padding: 140px 60px;
    background: #fafafa;
}

.testimonials-header {
    text-align: center;
    margin-bottom: 80px;
}

.testimonials-header h2 {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(32px, 4vw, 48px);
    font-weight: 700;
    letter-spacing: -1px;
    margin: 0;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.testimonial-card {
    background: #fff;
    border: 1px solid #eee;
    padding: 48px;
    position: relative;
}

.testimonial-card--featured {
    background: #1a1a1a;
    border-color: #2a2a2a;
    color: #fff;
    transform: translateY(-20px);
}

.testimonial-quote {
    font-size: 18px;
    line-height: 1.7;
    font-style: italic;
    color: #555;
    margin: 0 0 32px;
}

.testimonial-card--featured .testimonial-quote {
    color: #ccc;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 16px;
}

.author-avatar {
    width: 48px;
    height: 48px;
    background: #e74c3c;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 1px;
}

.author-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.author-name {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 1px;
    color: #1a1a1a;
}

.testimonial-card--featured .author-name {
    color: #fff;
}

.author-title {
    font-size: 13px;
    color: #888;
}

/* ── CTA SECTION ───────────────────────────────────────────── */
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

/* ── RESPONSIVE ────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .capabilities-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .bridge-inner {
        padding: 0 24px;
    }

    .manifesto-columns {
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .testimonials-grid {
        grid-template-columns: 1fr;
    }

    .testimonial-card--featured {
        transform: none;
    }
}

@media (max-width: 768px) {
    .hero-stats-float {
        display: none;
    }

    .hero-content {
        padding: 120px 24px 60px;
    }

    .form-stage {
        flex-direction: column;
    }

    .theater-btn {
        width: 100%;
        justify-content: center;
    }

    .manifesto-section,
    .capabilities-section,
    .stats-section,
    .testimonials-section,
    .cta-section {
        padding: 100px 24px;
    }

    .capabilities-grid {
        grid-template-columns: 1fr;
    }

    .bridge-inner {
        flex-direction: column;
        padding: 0;
    }

    .bridge-divider {
        width: 100%;
        height: 1px;
        margin: 0;
        align-self: auto;
    }

    .stats-content {
        gap: 40px;
    }

    .testimonial-card {
        padding: 32px;
    }
}

@media (max-width: 480px) {
    .hero-meta {
        flex-direction: column;
        gap: 8px;
    }

    .hero-divider {
        width: 40px;
    }

    .cta-actions {
        flex-direction: column;
    }

    .cta-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
