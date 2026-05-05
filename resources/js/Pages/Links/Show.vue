<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    link: { type: Object, required: true },
    analytics: {
        type: Object,
        default: () => ({
            total_clicks: 0,
            period: 'all',
            clicks_by_country: [],
            clicks_by_device: [],
            clicks_by_browser: [],
            clicks_by_referrer: [],
            clicks_over_time: [],
        }),
    },
});

const copied = ref(false);
const embedCopied = ref(false);
const showEmbed = ref(false);
const showReportModal = ref(false);
const reportSubmitted = ref(false);
const reportError = ref('');

const shortUrl = computed(() => props.link.short_url ?? `${window.location.origin}/${props.link.short_code}`);

const embedCode = computed(() =>
    `<a href="${shortUrl.value}" target="_blank" rel="noopener">${shortUrl.value}</a>`
);

const copyUrl = async () => {
    await navigator.clipboard.writeText(shortUrl.value);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const copyEmbed = async () => {
    await navigator.clipboard.writeText(embedCode.value);
    embedCopied.value = true;
    setTimeout(() => { embedCopied.value = false; }, 2000);
};

const deleteLink = () => {
    if (!confirm('Delete this link permanently?')) return;
    router.delete(route('links.destroy', props.link.id));
};

const setPeriod = (p) => {
    router.get(route('links.show', props.link.id), { period: p }, { preserveState: true, preserveScroll: true });
};

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

const topCountries = computed(() => (props.analytics.clicks_by_country || []).slice(0, 8));
const topReferrers = computed(() => (props.analytics.clicks_by_referrer || []).slice(0, 5));

const deviceColors = { desktop: '#d4af37', mobile: '#e74c3c', tablet: '#d4af37', bot: '#e74c3c' };

const periods = [
    { key: 'today', label: 'Today' },
    { key: 'week', label: '7 Days' },
    { key: 'month', label: '30 Days' },
    { key: 'all', label: 'All Time' },
];

const activePeriod = computed(() => props.analytics.period ?? 'all');

// Social share URLs
const twitterUrl = computed(() => `https://twitter.com/intent/tweet?url=${encodeURIComponent(shortUrl.value)}`);
const whatsappUrl = computed(() => `https://wa.me/?text=${encodeURIComponent(shortUrl.value)}`);
const telegramUrl = computed(() => `https://t.me/share/url?url=${encodeURIComponent(shortUrl.value)}`);
const linkedinUrl = computed(() => `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shortUrl.value)}`);
const facebookUrl = computed(() => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shortUrl.value)}`);

// QR download URLs
const qrSvgUrl = computed(() => route('links.qr', { link: props.link.id, format: 'svg' }));
const qrPngUrl = computed(() => route('links.qr', { link: props.link.id, format: 'png' }));

// Max value for bar charts
const maxDevice = computed(() => Math.max(1, ...(props.analytics.clicks_by_device || []).map(d => d.total)));
const maxCountry = computed(() => Math.max(1, ...(props.analytics.clicks_by_country || []).map(d => d.total)));
const maxRef = computed(() => Math.max(1, ...(topReferrers.value).map(d => d.total)));

// Report form
const reportForm = useForm({
    reason: 'spam',
    details: '',
});

const reasonOptions = [
    { value: 'spam', label: 'Spam / Unwanted content' },
    { value: 'phishing', label: 'Phishing / Fraud' },
    { value: 'malware', label: 'Malware / Virus' },
    { value: 'violence', label: 'Violence / Harmful content' },
    { value: 'other', label: 'Other' },
];

const submitReport = () => {
    reportError.value = '';
    reportSubmitted.value = false;

    reportForm.post(route('links.report', props.link.id), {
        preserveScroll: true,
        onSuccess: () => {
            reportSubmitted.value = true;
            reportForm.reset();
            setTimeout(() => {
                showReportModal.value = false;
                reportSubmitted.value = false;
            }, 2000);
        },
        onError: (errors) => {
            reportError.value = errors.error || 'Failed to submit report. Please try again.';
        },
    });
};
</script>

<template>

    <Head :title="`${link.short_code} — Analytics`" />

    <AuthenticatedLayout>
        <template #header>{{ link.short_code }} — Analytics</template>

        <div class="editorial">
            <!-- Hero: Link Identity -->
            <section class="hero-split">
                <div class="hero-left">
                    <div class="issue-label">Analytics Report</div>
                    <h1>/<span>{{ link.short_code }}</span></h1>
                    <p class="deck">Comprehensive click analysis and engagement metrics for this shortened URL. Track
                        performance across devices, locations, and referrers.</p>

                    <div class="short-url-display">
                        <a :href="shortUrl" target="_blank" class="short-anchor">{{ shortUrl }}</a>
                        <button @click="copyUrl" class="copy-btn">{{ copied ? '✓ Copied' : 'Copy' }}</button>
                    </div>

                    <a :href="link.destination_url" target="_blank" rel="noopener" class="dest-anchor">{{
                        link.destination_url }}</a>

                    <div class="link-meta">
                        <span class="meta-badge"
                            :class="link.is_active ? 'meta-badge--active' : 'meta-badge--inactive'">
                            {{ link.is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span v-if="link.campaign_tag" class="meta-badge meta-badge--tag">{{ link.campaign_tag }}</span>
                        <span class="meta-created">Created {{ formatDate(link.created_at) }}</span>
                    </div>

                    <div class="hero-actions">
                        <Link :href="route('links.edit', link.id)" class="btn-primary">Edit Link</Link>
                        <button @click="deleteLink" class="btn-danger">Delete</button>
                        <button @click="showReportModal = true" class="btn-text">Report</button>
                    </div>
                </div>

                <div class="hero-right">
                    <div class="blueprint">
                        <h3>Link Specifications</h3>
                        <p>Real-time performance metrics with granular tracking across geographic regions, device types,
                            and
                            traffic sources.</p>

                        <div class="spec-list">
                            <div class="spec-item">
                                <span class="spec-num">01</span>
                                <span class="spec-label">Total Clicks</span>
                                <span class="spec-val">{{ (analytics.total_clicks ?? 0).toLocaleString() }}</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-num">02</span>
                                <span class="spec-label">Countries Reached</span>
                                <span class="spec-val">{{ topCountries.length }}</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-num">03</span>
                                <span class="spec-label">Referrers</span>
                                <span class="spec-val">{{ topReferrers.length }}</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-num">04</span>
                                <span class="spec-label">Expires</span>
                                <span class="spec-val">{{ link.expires_at ? formatDate(link.expires_at) : 'Never'
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Share & Distribution -->
            <section class="share-section">
                <div class="share-header">
                    <span class="roman-num">I.</span>
                    <h3>Distribution Channels</h3>
                </div>

                <div class="share-grid">
                    <div class="share-group">
                        <span class="share-label">Social</span>
                        <div class="share-buttons">
                            <a :href="twitterUrl" target="_blank" rel="noopener" class="share-btn"
                                title="Twitter/X">𝕏</a>
                            <a :href="whatsappUrl" target="_blank" rel="noopener" class="share-btn"
                                title="WhatsApp">W</a>
                            <a :href="telegramUrl" target="_blank" rel="noopener" class="share-btn"
                                title="Telegram">T</a>
                            <a :href="linkedinUrl" target="_blank" rel="noopener" class="share-btn"
                                title="LinkedIn">L</a>
                            <a :href="facebookUrl" target="_blank" rel="noopener" class="share-btn"
                                title="Facebook">F</a>
                        </div>
                    </div>

                    <div class="share-group">
                        <span class="share-label">QR Code</span>
                        <div class="share-buttons">
                            <a :href="qrSvgUrl" class="qr-btn">SVG</a>
                            <a :href="qrPngUrl" class="qr-btn">PNG</a>
                        </div>
                    </div>

                    <div class="share-group">
                        <span class="share-label">Embed</span>
                        <button @click="showEmbed = !showEmbed" class="qr-btn">{{ showEmbed ? 'Hide' : 'Get Code'
                            }}</button>
                    </div>
                </div>

                <!-- Embed panel -->
                <div v-if="showEmbed" class="embed-panel">
                    <code class="embed-code">{{ embedCode }}</code>
                    <button @click="copyEmbed" class="copy-btn">{{ embedCopied ? '✓ Copied' : 'Copy' }}</button>
                </div>
            </section>

            <!-- Period filter -->
            <section class="period-section">
                <div class="period-bar">
                    <span class="period-label">Time Period</span>
                    <button v-for="p in periods" :key="p.key" @click="setPeriod(p.key)" class="period-btn"
                        :class="activePeriod === p.key ? 'period-btn--active' : ''">{{ p.label }}</button>
                </div>
            </section>

            <!-- Analytics grid with editorial spec-item style -->
            <section class="analytics-section">
                <div class="analytics-grid">
                    <!-- Devices -->
                    <div class="analytics-card">
                        <div class="card-header">
                            <span class="roman-num">II.</span>
                            <h3 class="card-title">Device Analytics</h3>
                        </div>
                        <div v-if="!analytics.clicks_by_device?.length" class="no-data">No data yet</div>
                        <div v-else class="spec-list">
                            <div v-for="(row, index) in analytics.clicks_by_device" :key="row.device_type"
                                class="spec-item">
                                <span class="spec-num">{{ String(index + 1).padStart(2, '0') }}</span>
                                <span class="spec-label">{{ row.device_type || 'Unknown' }}</span>
                                <span class="spec-val">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Countries with flag emojis (Story 3.10) -->
                    <div class="analytics-card">
                        <div class="card-header">
                            <span class="roman-num">III.</span>
                            <h3 class="card-title">Geographic Reach</h3>
                        </div>
                        <div v-if="!analytics.clicks_by_country?.length" class="no-data">No data yet</div>
                        <div v-else class="spec-list">
                            <div v-for="(row, index) in topCountries" :key="row.country_code" class="spec-item">
                                <span class="spec-num">{{ String(index + 1).padStart(2, '0') }}</span>
                                <span class="spec-label"><span class="country-flag">{{ row.country_emoji }}</span> {{
                                    row.country_name }}</span>
                                <span class="spec-val">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Referrers -->
                    <div class="analytics-card">
                        <div class="card-header">
                            <span class="roman-num">IV.</span>
                            <h3 class="card-title">Traffic Sources</h3>
                        </div>
                        <div v-if="!topReferrers.length" class="no-data">No data yet</div>
                        <div v-else class="spec-list">
                            <div v-for="(row, index) in topReferrers" :key="row.referrer_domain || 'Direct'"
                                class="spec-item">
                                <span class="spec-num">{{ String(index + 1).padStart(2, '0') }}</span>
                                <span class="spec-label">{{ row.referrer_domain || 'Direct / None' }}</span>
                                <span class="spec-val">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Browsers -->
                    <div class="analytics-card">
                        <div class="card-header">
                            <span class="roman-num">V.</span>
                            <h3 class="card-title">Browser Analytics</h3>
                        </div>
                        <div v-if="!analytics.clicks_by_browser?.length" class="no-data">No data yet</div>
                        <div v-else class="spec-list">
                            <div v-for="(row, index) in analytics.clicks_by_browser" :key="row.browser_name"
                                class="spec-item">
                                <span class="spec-num">{{ String(index + 1).padStart(2, '0') }}</span>
                                <span class="spec-label">{{ row.browser_name }}</span>
                                <span class="spec-val">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Time series -->
            <section v-if="analytics.clicks_over_time?.length" class="time-section">
                <div class="time-header">
                    <span class="roman-num">VI.</span>
                    <h3>Engagement Timeline</h3>
                </div>
                <div class="time-chart">
                    <div v-for="point in analytics.clicks_over_time" :key="point.date" class="time-bar"
                        :style="{ height: `${Math.max(20, (point.clicks / Math.max(...analytics.clicks_over_time.map(p => p.clicks))) * 160)}px` }"
                        :title="`${point.date}: ${point.clicks} clicks`">
                        <span class="time-value">{{ point.clicks }}</span>
                    </div>
                </div>
                <div class="time-labels">
                    <span v-for="point in analytics.clicks_over_time" :key="point.date" class="time-label">
                        {{ point.date }}
                    </span>
                </div>
            </section>

            <!-- Editorial Footer -->
            <footer class="editorial-footer">
                <p> MMXXV ShortLink Analytics — Link #{{ link.id }}</p>
                <Link :href="route('dashboard')" class="footer-link">Return to Dashboard</Link>
            </footer>

            <!-- Report Modal -->
            <div v-if="showReportModal" class="modal-overlay">
                <div class="modal-panel">
                    <h3 class="modal-title">Report Link</h3>

                    <div v-if="reportSubmitted" class="modal-success">
                        Thank you for your report. We'll review it shortly.
                    </div>

                    <form v-else @submit.prevent="submitReport">
                        <p class="modal-desc">
                            Report this link if it contains spam, malware, phishing, or harmful content.
                        </p>

                        <div class="modal-field">
                            <label>Reason</label>
                            <select v-model="reportForm.reason" class="editorial-select">
                                <option v-for="opt in reasonOptions" :key="opt.value" :value="opt.value">{{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div class="modal-field">
                            <label>Details (optional)</label>
                            <textarea v-model="reportForm.details" rows="3" class="editorial-textarea"
                                placeholder="Provide additional context..."></textarea>
                        </div>

                        <p v-if="reportError" class="modal-error">{{ reportError }}</p>

                        <div class="modal-actions">
                            <button type="button" @click="showReportModal = false" class="btn-ghost">Cancel</button>
                            <button type="submit" :disabled="reportForm.processing" class="btn-danger">
                                {{ reportForm.processing ? 'Submitting...' : 'Submit Report' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ── DECONSTRUCTIVIST EDITORIAL ───────────────────────────── */

@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

* {
    box-sizing: border-box;
}

.editorial {
    font-family: 'Crimson Pro', serif;
    background: #fafafa;
    color: #1a1a1a;
    min-height: 100vh;
}

/* ── Masthead Navigation ───────────────────────────────────── */
.masthead {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    padding: 20px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    mix-blend-mode: difference;
    z-index: 100;
    color: #fff;
}

.masthead-logo {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 8px;
    text-transform: uppercase;
}

.masthead-nav {
    display: flex;
    gap: 40px;
}

.masthead-nav a {
    color: inherit;
    text-decoration: none;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: 'Oswald', sans-serif;
    position: relative;
}

.masthead-nav a::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 1px;
    background: currentColor;
    transition: width 0.3s;
}

.masthead-nav a:hover::after {
    width: 100%;
}

/* ── Split Hero Layout ────────────────────────────────────── */
.hero-split {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    position: relative;
}

.hero-left {
    padding: 200px 80px 80px;
    position: relative;
}

.issue-label {
    position: absolute;
    top: 120px;
    left: 80px;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    transform: rotate(-90deg);
    transform-origin: left top;
}

h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 120px;
    font-weight: 700;
    line-height: 0.9;
    letter-spacing: -4px;
    margin-bottom: 30px;
}

h1 span {
    display: block;
    font-family: 'Crimson Pro', serif;
    font-size: 60px;
    font-weight: 400;
    font-style: italic;
    letter-spacing: 0;
    margin-top: 20px;
    color: #e74c3c;
}

.deck {
    font-size: 22px;
    line-height: 1.5;
    max-width: 400px;
    margin-bottom: 40px;
    color: #666;
}

/* ── Short URL Display ────────────────────────────────────── */
.short-url-display {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
}

.short-anchor {
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #e74c3c;
    text-decoration: none;
    letter-spacing: -0.01em;
}

.short-anchor:hover {
    text-decoration: underline;
}

.copy-btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid #1a1a1a;
    color: #1a1a1a;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
}

.copy-btn:hover {
    background: #1a1a1a;
    color: #fff;
}

.dest-anchor {
    display: block;
    font-size: 15px;
    color: #666;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 480px;
    margin-bottom: 20px;
}

.dest-anchor:hover {
    color: #e74c3c;
}

/* ── Link Meta ────────────────────────────────────────────── */
.link-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 40px;
}

.meta-badge {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 4px 12px;
    border: 1px solid;
}

.meta-badge--active {
    color: #27ae60;
    border-color: #27ae60;
}

.meta-badge--inactive {
    color: #e74c3c;
    border-color: #e74c3c;
}

.meta-badge--tag {
    color: #d4af37;
    border-color: #d4af37;
}

.meta-created {
    font-size: 13px;
    color: #999;
    font-style: italic;
}

/* ── Hero Actions ──────────────────────────────────────────── */
.hero-actions {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: #e74c3c;
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #c0392b;
}

.btn-danger {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: transparent;
    color: #e74c3c;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: 1px solid #e74c3c;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-danger:hover {
    background: #e74c3c;
    color: #fff;
}

.btn-text {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: transparent;
    color: #666;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    transition: color 0.3s;
}

.btn-text:hover {
    color: #1a1a1a;
}

/* ── Right Panel / Blueprint ───────────────────────────────── */
.hero-right {
    background: #1a1a1a;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 80px;
}

.hero-right::before {
    content: '';
    position: absolute;
    top: 60px;
    right: 60px;
    bottom: 60px;
    left: 60px;
    border: 1px solid #333;
}

.blueprint {
    width: 100%;
    max-width: 400px;
    position: relative;
    z-index: 1;
    color: #888;
}

.blueprint h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 20px;
}

.blueprint>p {
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 40px;
}

/* ── Spec List (Numbered Rows with Gold Values) ───────────── */
.spec-list {
    border-top: 1px solid #333;
    padding-top: 20px;
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 12px 0;
    border-bottom: 1px solid #222;
}

.spec-num {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #555;
    width: 30px;
}

.spec-label {
    flex: 1;
    font-size: 14px;
    color: #888;
}

.spec-val {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    color: #d4af37;
    letter-spacing: 2px;
}

/* ── Share Section ────────────────────────────────────────── */
.share-section {
    background: #f0f0f0;
    padding: 80px 60px;
}

.share-header {
    display: flex;
    align-items: baseline;
    gap: 16px;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.share-header h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0;
}

.roman-num {
    font-size: 12px;
    font-weight: 700;
    color: #e74c3c;
    letter-spacing: 1px;
}

.share-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 40px;
}

.share-group {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.share-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #999;
}

.share-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.share-btn {
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #ddd;
    color: #1a1a1a;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.share-btn:hover {
    background: #1a1a1a;
    color: #fff;
    border-color: #1a1a1a;
}

.qr-btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: #fff;
    border: 1px solid #ddd;
    color: #1a1a1a;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.qr-btn:hover {
    background: #1a1a1a;
    color: #fff;
    border-color: #1a1a1a;
}

/* ── Embed Panel ───────────────────────────────────────────── */
.embed-panel {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border: 1px solid #ddd;
    padding: 16px 20px;
    margin-top: 24px;
}

.embed-code {
    flex: 1;
    font-size: 13px;
    color: #666;
    font-family: 'JetBrains Mono', monospace;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Period Section ────────────────────────────────────────── */
.period-section {
    background: #fafafa;
    padding: 40px 60px;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.period-bar {
    display: flex;
    align-items: center;
    gap: 20px;
}

.period-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #999;
}

.period-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 10px 20px;
    background: transparent;
    border: 1px solid #ddd;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}

.period-btn:hover {
    border-color: #1a1a1a;
    color: #1a1a1a;
}

.period-btn--active {
    background: #1a1a1a;
    border-color: #1a1a1a;
    color: #fff;
}

/* ── Analytics Section ────────────────────────────────────── */
.analytics-section {
    background: #fafafa;
    padding: 80px 60px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.analytics-card {
    background: #1a1a1a;
    padding: 30px;
    position: relative;
}

.analytics-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: #e74c3c;
}

.card-header {
    display: flex;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 30px;
}

.card-title {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #fff;
    margin: 0;
}

.no-data {
    font-size: 15px;
    color: #555;
    font-style: italic;
    padding: 20px 0;
    text-align: center;
}

/* ── Country Flag ──────────────────────────────────────────── */
.country-flag {
    font-size: 16px;
    margin-right: 8px;
}

/* ── Time Section ──────────────────────────────────────────── */
.time-section {
    background: #f0f0f0;
    padding: 80px 60px;
}

.time-header {
    display: flex;
    align-items: baseline;
    gap: 16px;
    margin-bottom: 40px;
}

.time-header h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0;
}

.time-chart {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    height: 200px;
    margin-bottom: 16px;
}

.time-bar {
    flex: 1;
    background: #1a1a1a;
    min-height: 20px;
    position: relative;
    transition: all 0.3s;
}

.time-bar:hover {
    background: #e74c3c;
}

.time-value {
    position: absolute;
    top: -24px;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    color: #666;
}

.time-labels {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #999;
    font-family: 'Oswald', sans-serif;
    letter-spacing: 1px;
}

/* ── Editorial Footer ──────────────────────────────────────── */
.editorial-footer {
    background: #1a1a1a;
    color: #666;
    padding: 60px;
    text-align: center;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: 'Oswald', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
}

.footer-link {
    color: #888;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-link:hover {
    color: #d4af37;
}

/* ── Modal Styles ──────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 200;
    padding: 20px;
}

.modal-panel {
    background: #fafafa;
    max-width: 480px;
    width: 100%;
    padding: 40px;
    position: relative;
}

.modal-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: #e74c3c;
}

.modal-title {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0 0 24px;
}

.modal-success {
    padding: 20px;
    background: rgba(39, 174, 96, 0.1);
    border-left: 3px solid #27ae60;
    color: #27ae60;
}

.modal-desc {
    font-size: 16px;
    line-height: 1.6;
    color: #666;
    margin-bottom: 24px;
}

.modal-field {
    margin-bottom: 20px;
}

.modal-field label {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 8px;
}

.editorial-select,
.editorial-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #ddd;
    background: #fff;
    font-family: 'Crimson Pro', serif;
    font-size: 16px;
    color: #1a1a1a;
    outline: none;
    transition: border-color 0.2s;
}

.editorial-select:focus,
.editorial-textarea:focus {
    border-color: #1a1a1a;
}

.modal-error {
    color: #e74c3c;
    font-size: 14px;
    margin-bottom: 20px;
}

.modal-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 968px) {
    .masthead {
        padding: 20px 30px;
    }

    .hero-split {
        grid-template-columns: 1fr;
    }

    .hero-left {
        padding: 140px 30px 60px;
    }

    .issue-label {
        top: 100px;
        left: 30px;
    }

    h1 {
        font-size: 60px;
    }

    h1 span {
        font-size: 36px;
    }

    .hero-right {
        padding: 60px 30px;
    }

    .share-section,
    .period-section,
    .analytics-section,
    .time-section {
        padding: 60px 30px;
    }

    .share-grid {
        grid-template-columns: 1fr;
    }

    .analytics-grid {
        grid-template-columns: 1fr;
    }

    .period-bar {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 48px;
    }

    .masthead-nav {
        gap: 20px;
    }

    .hero-actions {
        flex-direction: column;
    }

    .btn-primary,
    .btn-danger,
    .btn-text {
        width: 100%;
        justify-content: center;
    }
}
</style>
