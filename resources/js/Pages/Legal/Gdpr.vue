<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const page = usePage();
const seoTitle = computed(() => page.props.settings?.seo_gdpr_title || 'GDPR Compliance');
const seoDescription = computed(() => page.props.settings?.seo_gdpr_description || '');
</script>

<template>

    <Head :title="`${seoTitle} — ${page.props.settings?.app_name || 'ShortLink'}`">
        <meta v-if="seoDescription" name="description" :content="seoDescription">
    </Head>

    <div class="legal-page">
        <Masthead variant="light" :show-nav="true" />

        <main class="legal-content">
            <header class="legal-header">
                <div class="issue-label">Data Protection</div>
                <h1>GDPR<br><span>Compliance</span></h1>
                <p class="deck">Our commitment to the General Data Protection Regulation and your rights as a data
                    subject.</p>
                <div class="meta-badge">EU Regulation 2016/679</div>
            </header>

            <div class="legal-body">
                <section class="legal-section">
                    <span class="roman-num">I.</span>
                    <h2>Data Controller</h2>
                    <p>ShortLink operates as the data controller for personal information collected through our service.
                        For data protection inquiries, contact our Data Protection Officer at <a
                            href="mailto:dpo@shortlink.app">dpo@shortlink.app</a>.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">II.</span>
                    <h2>Legal Basis for Processing</h2>
                    <p>We process personal data based on the following legal grounds:</p>
                    <ul>
                        <li><strong>Consent:</strong> When you explicitly agree to our processing activities</li>
                        <li><strong>Contract:</strong> Processing necessary to fulfill our service agreement with you
                        </li>
                        <li><strong>Legal Obligation:</strong> Compliance with applicable laws and regulations</li>
                        <li><strong>Legitimate Interests:</strong> Improving our services and ensuring security</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">III.</span>
                    <h2>Your Rights Under GDPR</h2>
                    <p>As a data subject, you have the following rights:</p>

                    <div class="rights-grid">
                        <div class="right-item">
                            <h3>Right to Access</h3>
                            <p>Request a copy of your personal data and information about how we process it.</p>
                        </div>
                        <div class="right-item">
                            <h3>Right to Rectification</h3>
                            <p>Request correction of inaccurate or incomplete personal data.</p>
                        </div>
                        <div class="right-item">
                            <h3>Right to Erasure</h3>
                            <p>Request deletion of your personal data ("Right to be Forgotten").</p>
                        </div>
                        <div class="right-item">
                            <h3>Right to Restrict Processing</h3>
                            <p>Request limitation on how we use your personal data.</p>
                        </div>
                        <div class="right-item">
                            <h3>Right to Data Portability</h3>
                            <p>Receive your data in a structured, commonly used format.</p>
                        </div>
                        <div class="right-item">
                            <h3>Right to Object</h3>
                            <p>Object to processing based on legitimate interests or direct marketing.</p>
                        </div>
                    </div>
                </section>

                <section class="legal-section">
                    <span class="roman-num">IV.</span>
                    <h2>Data Transfers</h2>
                    <p>We may transfer your data outside the European Economic Area (EEA). When we do, we ensure
                        appropriate safeguards are in place:</p>
                    <ul>
                        <li>Adequacy decisions by the European Commission</li>
                        <li>Standard contractual clauses approved by the EU</li>
                        <li>Binding corporate rules for intra-group transfers</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">V.</span>
                    <h2>Data Breach Notification</h2>
                    <p>In the event of a personal data breach likely to result in a risk to your rights and freedoms, we
                        will:</p>
                    <ul>
                        <li>Notify the relevant supervisory authority within 72 hours</li>
                        <li>Inform affected individuals without undue delay</li>
                        <li>Provide details about the breach and mitigation measures</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VI.</span>
                    <h2>Data Protection by Design</h2>
                    <p>We implement technical and organizational measures to ensure data protection:</p>
                    <ul>
                        <li>Encryption of data in transit (TLS/SSL) and at rest</li>
                        <li>Pseudonymization of analytics data</li>
                        <li>Regular security assessments and penetration testing</li>
                        <li>Staff training on data protection principles</li>
                        <li>Privacy impact assessments for new features</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VII.</span>
                    <h2>Retention Periods</h2>
                    <p>We retain personal data only as long as necessary:</p>
                    <table class="retention-table">
                        <thead>
                            <tr>
                                <th>Data Category</th>
                                <th>Retention Period</th>
                                <th>Basis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Account information</td>
                                <td>Until account deletion</td>
                                <td>Contract necessity</td>
                            </tr>
                            <tr>
                                <td>Shortened URLs</td>
                                <td>Until link deletion</td>
                                <td>User request</td>
                            </tr>
                            <tr>
                                <td>Analytics data</td>
                                <td>36 months</td>
                                <td>Legitimate interest</td>
                            </tr>
                            <tr>
                                <td>Server logs</td>
                                <td>90 days</td>
                                <td>Security/legal</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="legal-section">
                    <span class="roman-num">VIII.</span>
                    <h2>Exercising Your Rights</h2>
                    <p>To exercise any of your GDPR rights, please:</p>
                    <ul>
                        <li>Email us at <a href="mailto:privacy@shortlink.app">privacy@shortlink.app</a></li>
                        <li>Include "GDPR Request" in the subject line</li>
                        <li>Specify which right you wish to exercise</li>
                        <li>Provide identification to verify your identity</li>
                    </ul>
                    <p>We will respond to your request within one month. Complex requests may require an extension of up
                        to two additional months.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">IX.</span>
                    <h2>Complaints</h2>
                    <p>If you believe we have not handled your data appropriately, you have the right to lodge a
                        complaint with your local supervisory authority. We encourage you to contact us first so we can
                        address your concerns.</p>
                </section>

                <section class="legal-section">
                    <span class="roman-num">X.</span>
                    <h2>Updates to This Notice</h2>
                    <p>We may update this GDPR compliance information to reflect changes in our practices or legal
                        requirements. Check this page periodically for the latest information.</p>
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
    max-width: 900px;
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

.legal-section a {
    color: #e74c3c;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.3s;
}

.legal-section a:hover {
    border-bottom-color: #e74c3c;
}

/* Rights Grid */
.rights-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-top: 24px;
}

.right-item {
    background: #fff;
    border: 1px solid #eee;
    padding: 24px;
    position: relative;
}

.right-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: #d4af37;
}

.right-item h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 8px;
    color: #1a1a1a;
}

.right-item p {
    font-size: 15px;
    line-height: 1.6;
    color: #666;
    margin: 0;
}

/* Retention Table */
.retention-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.retention-table th,
.retention-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.retention-table th {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
    background: #f5f5f5;
}

.retention-table td {
    font-size: 15px;
    color: #555;
}

.retention-table tr:hover td {
    background: #fafafa;
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

    .rights-grid {
        grid-template-columns: 1fr;
    }

    .retention-table {
        font-size: 14px;
    }

    .retention-table th,
    .retention-table td {
        padding: 8px 12px;
    }
}
</style>
