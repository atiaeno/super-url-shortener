<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const page = usePage();
const seoTitle = computed(() => page.props.settings?.seo_terms_title || 'Terms of Service');
const seoDescription = computed(() => page.props.settings?.seo_terms_description || '');
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
                <h1>Terms of<br><span>Service</span></h1>
                <p class="deck">The rules and guidelines governing your use of our URL shortening platform.</p>
                <div class="meta-badge">Last Updated: January 1, MMXXV</div>
            </header>

            <div class="legal-body">
                <section class="legal-section">
                    <span class="roman-num">I.</span>
                    <h2>Acceptance of Terms</h2>
                    <p>By accessing or using ShortLink, you agree to be bound by these Terms of Service. If you disagree
                        with any part of the terms, you may not access the service. These terms apply to all visitors,
                        users, and others who access or use the service.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">II.</span>
                    <h2>Description of Service</h2>
                    <p>ShortLink provides URL shortening services, allowing users to create shortened versions of long
                        URLs for easier sharing. We also provide analytics related to the shortened links, including
                        click counts, geographic data, and referrer information.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">III.</span>
                    <h2>User Accounts</h2>
                    <p>When you create an account with us, you must provide accurate, complete, and current information.
                        You are responsible for safeguarding the password and for all activities that occur under your
                        account. Notify us immediately of any unauthorized use.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">IV.</span>
                    <h2>Acceptable Use</h2>
                    <p>You agree not to use the service to:</p>
                    <ul>
                        <li>Create links to illegal, harmful, or malicious content</li>
                        <li>Distribute malware, viruses, or phishing attempts</li>
                        <li>Engage in spam or unsolicited communications</li>
                        <li>Impersonate others or misrepresent your identity</li>
                        <li>Interfere with or disrupt the service or servers</li>
                        <li>Circumvent any security measures</li>
                        <li>Violate any applicable laws or regulations</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">V.</span>
                    <h2>Content & Links</h2>
                    <p>You retain ownership of any content you submit through our service. By submitting content, you
                        grant us a limited license to store, process, and display that content solely for the purpose of
                        providing the service.</p>
                    <p>We reserve the right to remove any links that violate these terms or that we determine, in our
                        sole discretion, to be harmful or inappropriate.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VI.</span>
                    <h2>Termination</h2>
                    <p>We may terminate or suspend your account immediately, without prior notice or liability, for any
                        reason, including breach of these Terms. Upon termination, your right to use the service will
                        immediately cease.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VII.</span>
                    <h2>Limitation of Liability</h2>
                    <p>To the maximum extent permitted by law, ShortLink shall not be liable for any indirect,
                        incidental, special, consequential, or punitive damages, including loss of profits, data, or
                        goodwill, arising from your use of the service.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VIII.</span>
                    <h2>Disclaimer</h2>
                    <p>The service is provided on an "AS IS" and "AS AVAILABLE" basis. We make no warranties, express or
                        implied, including but not limited to implied warranties of merchantability, fitness for a
                        particular purpose, and non-infringement.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">IX.</span>
                    <h2>Governing Law</h2>
                    <p>These Terms shall be governed by and construed in accordance with the laws of [Jurisdiction],
                        without regard to its conflict of law provisions.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">X.</span>
                    <h2>Changes to Terms</h2>
                    <p>We reserve the right to modify or replace these Terms at any time. We will provide notice of any
                        changes by posting the new Terms on this page. Your continued use of the service after such
                        changes constitutes acceptance.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">XI.</span>
                    <h2>Contact Information</h2>
                    <p>For questions about these Terms, please contact us at <a
                            href="mailto:legal@shortlink.app">legal@shortlink.app</a>.</p>
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
