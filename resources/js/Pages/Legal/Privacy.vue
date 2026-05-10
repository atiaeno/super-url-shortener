<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const page = usePage();
const seoTitle = computed(() => page.props.settings?.seo_privacy_title || 'Privacy Policy');
const seoDescription = computed(() => page.props.settings?.seo_privacy_description || '');
</script>

<template>

    <Head :title="`${seoTitle} — ${page.props.settings?.app_name || 'ShortLink'}`">
        <meta v-if="seoDescription" name="description" :content="seoDescription">
    </Head>

    <div class="legal-page">
        <Masthead variant="light" :show-nav="true" />

        <main class="legal-content">
            <header class="legal-header">
                <div class="issue-label">Legal Document</div>
                <h1>Privacy<br><span>Policy</span></h1>
                <p class="deck">Our commitment to protecting your personal data and respecting your privacy rights.</p>
                <div class="meta-badge">Effective Date: January 1, MMXXV</div>
            </header>

            <div class="legal-body">
                <section class="legal-section">
                    <span class="roman-num">I.</span>
                    <h2>Information We Collect</h2>
                    <p>We collect information that you provide directly to us, including:</p>
                    <ul>
                        <li>Account information (name, email address)</li>
                        <li>URLs you shorten through our service</li>
                        <li>Analytics data (click counts, geographic data, referrer information)</li>
                        <li>Payment information (if you choose to donate)</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">II.</span>
                    <h2>How We Use Your Information</h2>
                    <p>We use the collected information to:</p>
                    <ul>
                        <li>Provide and maintain our URL shortening service</li>
                        <li>Generate analytics and reports for your links</li>
                        <li>Communicate with you about your account</li>
                        <li>Improve our services and develop new features</li>
                        <li>Detect and prevent fraudulent or abusive activity</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">III.</span>
                    <h2>Data Retention</h2>
                    <p>We retain your personal information for as long as your account is active or as needed to provide
                        you services. Analytics data for shortened URLs is retained indefinitely unless you delete the
                        link. You may request deletion of your account and associated data at any time.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">IV.</span>
                    <h2>Data Sharing</h2>
                    <p>We do not sell, trade, or rent your personal information to third parties. We may share
                        information only in the following circumstances:</p>
                    <ul>
                        <li>With your consent</li>
                        <li>To comply with legal obligations</li>
                        <li>To protect our rights or property</li>
                        <li>With service providers who assist in our operations (under strict confidentiality
                            agreements)</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">V.</span>
                    <h2>Cookies & Tracking</h2>
                    <p>We use cookies and similar technologies to:</p>
                    <ul>
                        <li>Authenticate users and prevent fraudulent use of accounts</li>
                        <li>Remember your preferences</li>
                        <li>Analyze traffic patterns and improve our service</li>
                    </ul>
                    <p>You can control cookies through your browser settings. However, disabling cookies may limit your
                        ability to use certain features.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VI.</span>
                    <h2>Security</h2>
                    <p>We implement industry-standard security measures to protect your data, including encryption in
                        transit (HTTPS), secure server infrastructure, and regular security audits. However, no method
                        of transmission over the internet is 100% secure, and we cannot guarantee absolute security.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VII.</span>
                    <h2>Your Rights</h2>
                    <p>Depending on your jurisdiction, you may have the following rights:</p>
                    <ul>
                        <li>Access your personal data</li>
                        <li>Correct inaccurate information</li>
                        <li>Request deletion of your data</li>
                        <li>Object to processing of your data</li>
                        <li>Data portability</li>
                        <li>Withdraw consent</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VIII.</span>
                    <h2>Contact Us</h2>
                    <p>If you have questions about this Privacy Policy, please contact us at <a
                            href="mailto:privacy@shortlink.app">privacy@shortlink.app</a>.</p>
                </section>
            </div>
        </main>

        <EditorialFooter />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

.legal-page {
    font-family: 'Crimson Pro', serif;
    background: #fafafa;
    color: #1a1a1a;
    min-height: 100vh;
}

.legal-content {
    padding: 140px 60px 80px;
    max-width: 800px;
    margin: 0 auto;
}

.legal-header {
    margin-bottom: 60px;
    padding-bottom: 40px;
    border-bottom: 1px solid #ddd;
}

.issue-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 20px;
}

h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 72px;
    font-weight: 700;
    line-height: 0.95;
    letter-spacing: -2px;
    margin-bottom: 24px;
}

h1 span {
    font-family: 'Crimson Pro', serif;
    font-size: 48px;
    font-weight: 400;
    font-style: italic;
    color: #e74c3c;
    letter-spacing: 0;
}

.deck {
    font-size: 20px;
    line-height: 1.6;
    color: #666;
    margin-bottom: 24px;
    max-width: 600px;
}

.meta-badge {
    display: inline-block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #d4af37;
    border: 1px solid #d4af37;
    padding: 8px 16px;
}

.legal-body {
    display: flex;
    flex-direction: column;
    gap: 48px;
}

.legal-section {
    position: relative;
    padding-left: 60px;
}

.roman-num {
    position: absolute;
    left: 0;
    top: 0;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #e74c3c;
}

.legal-section h2 {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 16px;
    color: #1a1a1a;
}

.legal-section p {
    font-size: 17px;
    line-height: 1.7;
    color: #555;
    margin-bottom: 16px;
}

.legal-section ul {
    margin: 0;
    padding-left: 24px;
}

.legal-section li {
    font-size: 16px;
    line-height: 1.8;
    color: #555;
    margin-bottom: 8px;
}

.legal-section a {
    color: #e74c3c;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.3s;
}

.legal-section a:hover {
    border-bottom-color: #e74c3c;
}

@media (max-width: 768px) {
    .legal-content {
        padding: 120px 30px 60px;
    }

    h1 {
        font-size: 48px;
    }

    h1 span {
        font-size: 36px;
    }

    .legal-section {
        padding-left: 40px;
    }
}
</style>
