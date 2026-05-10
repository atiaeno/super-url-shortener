<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const page = usePage();
const seoTitle = computed(() => page.props.settings?.seo_cookies_title || 'Cookie Policy');
const seoDescription = computed(() => page.props.settings?.seo_cookies_description || '');
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
                <h1>Cookie<br><span>Policy</span></h1>
                <p class="deck">Understanding how and why we use cookies and similar technologies.</p>
                <div class="meta-badge">Effective Date: January 1, MMXXV</div>
            </header>

            <div class="legal-body">
                <section class="legal-section">
                    <span class="roman-num">I.</span>
                    <h2>What Are Cookies</h2>
                    <p>Cookies are small text files that are stored on your device when you visit a website. They help
                        websites remember your preferences, understand how you interact with the site, and improve your
                        browsing experience.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">II.</span>
                    <h2>How We Use Cookies</h2>
                    <p>We use cookies for the following purposes:</p>
                    <ul>
                        <li><strong>Essential cookies:</strong> Required for the website to function properly, including
                            authentication and security features.</li>
                        <li><strong>Preference cookies:</strong> Remember your settings and preferences for a better
                            experience.</li>
                        <li><strong>Analytics cookies:</strong> Help us understand how visitors interact with our
                            website.</li>
                        <li><strong>Security cookies:</strong> Protect against fraudulent activity and improve security.
                        </li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">III.</span>
                    <h2>Types of Cookies We Use</h2>
                    <table class="cookie-table">
                        <thead>
                            <tr>
                                <th>Cookie Name</th>
                                <th>Purpose</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>session_id</td>
                                <td>Maintains your login session</td>
                                <td>Session</td>
                            </tr>
                            <tr>
                                <td>XSRF-TOKEN</td>
                                <td>Security - prevents CSRF attacks</td>
                                <td>Session</td>
                            </tr>
                            <tr>
                                <td>remember_web</td>
                                <td>Keeps you logged in (if selected)</td>
                                <td>2 weeks</td>
                            </tr>
                            <tr>
                                <td>_ga</td>
                                <td>Google Analytics - visitor identification</td>
                                <td>2 years</td>
                            </tr>
                            <tr>
                                <td>_gid</td>
                                <td>Google Analytics - session identification</td>
                                <td>24 hours</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="legal-section">
                    <span class="roman-num">IV.</span>
                    <h2>Third-Party Cookies</h2>
                    <p>We may use third-party services that place cookies on your device:</p>
                    <ul>
                        <li><strong>Google Analytics:</strong> For website traffic analysis</li>
                        <li><strong>Payment processors:</strong> For secure donation processing</li>
                        <li><strong>Social media:</strong> For sharing functionality</li>
                    </ul>
                    <p>These third parties have their own privacy policies and cookie practices.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">V.</span>
                    <h2>Managing Cookies</h2>
                    <p>You can control cookies through your browser settings:</p>
                    <ul>
                        <li><strong>Chrome:</strong> Settings → Privacy and security → Cookies and other site data</li>
                        <li><strong>Firefox:</strong> Settings → Privacy & Security → Cookies and Site Data</li>
                        <li><strong>Safari:</strong> Preferences → Privacy → Cookies and website data</li>
                        <li><strong>Edge:</strong> Settings → Cookies and site permissions → Manage cookies</li>
                    </ul>
                    <p>Please note that disabling certain cookies may affect the functionality of our website.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VI.</span>
                    <h2>Consent</h2>
                    <p>By using our website, you consent to the use of cookies as described in this policy. You may
                        withdraw your consent at any time by adjusting your browser settings or contacting us.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VII.</span>
                    <h2>Changes to This Policy</h2>
                    <p>We may update this Cookie Policy from time to time. We will notify you of any changes by posting
                        the new policy on this page with an updated effective date.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VIII.</span>
                    <h2>Contact Us</h2>
                    <p>If you have questions about our use of cookies, please contact us at <a
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

.legal-section strong {
    font-weight: 600;
    color: #1a1a1a;
}

.cookie-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.cookie-table th,
.cookie-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.cookie-table th {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
    background: #f5f5f5;
}

.cookie-table td {
    font-size: 15px;
    color: #555;
}

.cookie-table tr:hover td {
    background: #fafafa;
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

    .cookie-table {
        font-size: 14px;
    }

    .cookie-table th,
    .cookie-table td {
        padding: 8px 12px;
    }
}
</style>
