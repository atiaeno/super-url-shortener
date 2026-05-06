<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const activeCategory = ref('getting-started');
const expandedQuestions = ref(new Set());

const categories = [
    { id: 'getting-started', label: 'Getting Started', num: 'I.' },
    { id: 'account', label: 'Account', num: 'II.' },
    { id: 'links', label: 'Links & Shortening', num: 'III.' },
    { id: 'analytics', label: 'Analytics', num: 'IV.' },
    { id: 'api', label: 'API & Integration', num: 'V.' },
];

const faqs = {
    'getting-started': [
        {
            id: 'gs-1',
            question: 'What is ShortLink?',
            answer: 'ShortLink is a comprehensive URL shortening service that transforms long, unwieldy web addresses into concise, memorable links. Beyond simple shortening, we provide detailed analytics, custom aliases, QR code generation, and API access for power users.'
        },
        {
            id: 'gs-2',
            question: 'How do I create my first short link?',
            answer: 'Simply paste your long URL into the input field on our homepage and click "Transform." No registration required for basic shortening. Registered users gain access to link management, analytics, and custom aliases.'
        },
        {
            id: 'gs-3',
            question: 'Is ShortLink free to use?',
            answer: 'Yes, our core service is entirely free. We offer unlimited basic URL shortening, click analytics, and QR codes at no cost. Advanced features like bulk shortening, priority support, and enhanced analytics are available to registered users.'
        },
        {
            id: 'gs-4',
            question: 'Do I need an account?',
            answer: 'No account is necessary for basic link shortening. However, creating an account unlocks powerful features: link history, edit capabilities, detailed analytics, custom aliases, and API access.'
        }
    ],
    'account': [
        {
            id: 'acc-1',
            question: 'How do I create an account?',
            answer: 'Click "Join" in the navigation menu and complete the registration form. You can also sign up using your Google, GitHub, or Twitter account through our OAuth integration.'
        },
        {
            id: 'acc-2',
            question: 'How do I reset my password?',
            answer: 'On the login page, click "Forgot password?" and enter your email address. We\'ll send you a secure link to create a new password. For security, this link expires after 24 hours.'
        },
        {
            id: 'acc-3',
            question: 'Can I change my email address?',
            answer: 'Yes. Navigate to your Profile settings, and under "Account Information," you can update your email. You\'ll need to verify the new address before the change takes effect.'
        },
        {
            id: 'acc-4',
            question: 'How do I delete my account?',
            answer: 'In your Profile settings, scroll to the "Danger Zone" section and select "Delete Account." This action is irreversible and will remove all your data, including saved links and analytics history.'
        }
    ],
    'links': [
        {
            id: 'lnk-1',
            question: 'Can I customize my short link?',
            answer: 'Yes, registered users can create custom aliases (e.g., short.link/my-brand). Custom aliases must be unique and between 3-50 characters, containing only letters, numbers, and hyphens.'
        },
        {
            id: 'lnk-2',
            question: 'How long do links last?',
            answer: 'Shortened links never expire unless you delete them. Guest-created links remain active indefinitely. Registered users can manage and delete their links at any time.'
        },
        {
            id: 'lnk-3',
            question: 'Can I edit a link after creating it?',
            answer: 'Registered users can edit the destination URL of their links. Navigate to "My Links" in your dashboard, find the link, and click "Edit." The short URL remains unchanged while the destination updates instantly.'
        },
        {
            id: 'lnk-4',
            question: 'What is bulk shortening?',
            answer: 'Bulk shortening allows you to create multiple short links simultaneously. Upload a CSV or JSON file with up to 100 URLs, and we\'ll generate short links for each. Results can be exported in your preferred format.'
        },
        {
            id: 'lnk-5',
            question: 'How do QR codes work?',
            answer: 'Every short link automatically generates a QR code. Click the QR icon next to any link to download it as SVG or PNG. QR codes are perfect for print materials, presentations, and physical marketing.'
        }
    ],
    'analytics': [
        {
            id: 'anl-1',
            question: 'What analytics are available?',
            answer: 'Our analytics dashboard provides: total clicks, unique visitors, geographic distribution, referral sources, device types, browsers, operating systems, and click timestamps. Data updates in real-time.'
        },
        {
            id: 'anl-2',
            question: 'How often is analytics data updated?',
            answer: 'Analytics update in real-time with a typical delay of under 30 seconds. You can refresh your dashboard at any time to see the latest statistics.'
        },
        {
            id: 'anl-3',
            question: 'Can I export my analytics?',
            answer: 'Yes, all analytics data can be exported in CSV, JSON, or PDF formats. Use the export button in your dashboard or make an authenticated API request to retrieve structured data.'
        },
        {
            id: 'anl-4',
            question: 'What does "unique clicks" mean?',
            answer: 'Unique clicks represent individual visitors, counted once per IP address within a 24-hour period. This metric helps distinguish between total engagement (total clicks) and actual reach (unique visitors).'
        }
    ],
    'api': [
        {
            id: 'api-1',
            question: 'Do you offer an API?',
            answer: 'Yes, we provide a RESTful API for programmatic access to all ShortLink features. The API supports link creation, retrieval, updates, deletion, and analytics queries.'
        },
        {
            id: 'api-2',
            question: 'How do I get an API key?',
            answer: 'API keys are available to all registered users. Navigate to your Profile settings and select "API Access" to generate your unique key. Keep your key secure—never expose it in client-side code.'
        },
        {
            id: 'api-3',
            question: 'What are the API rate limits?',
            answer: 'Free accounts: 100 requests per hour. This includes all endpoints. Rate limit headers are included in every API response. Contact us if you need higher limits for commercial use.'
        },
        {
            id: 'api-4',
            question: 'Is there API documentation?',
            answer: 'Complete API documentation is available at /api-docs. It includes endpoint specifications, request/response examples, authentication guides, and code samples in multiple programming languages.'
        }
    ]
};

const currentFaqs = computed(() => faqs[activeCategory.value] || []);

const toggleQuestion = (id) => {
    if (expandedQuestions.value.has(id)) {
        expandedQuestions.value.delete(id);
    } else {
        expandedQuestions.value.add(id);
    }
};

const isExpanded = (id) => expandedQuestions.value.has(id);
</script>

<template>
    <Head title="Help Center — ShortLink" />

    <div class="help-page">
        <Masthead variant="light" :show-nav="true" />

        <main class="help-content">
            <!-- Header -->
            <header class="help-header">
                <div class="issue-label">Support & Documentation</div>
                <h1>Help<br><span>Center</span></h1>
                <p class="deck">Comprehensive answers to common questions. Browse by category or search for specific topics.</p>
            </header>

            <!-- Category Navigation -->
            <nav class="category-nav">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    class="category-btn"
                    :class="{ 'active': activeCategory === cat.id }"
                    @click="activeCategory = cat.id"
                >
                    <span class="category-num">{{ cat.num }}</span>
                    <span class="category-label">{{ cat.label }}</span>
                </button>
            </nav>

            <!-- FAQ Accordion -->
            <div class="faq-container">
                <div
                    v-for="faq in currentFaqs"
                    :key="faq.id"
                    class="faq-item"
                    :class="{ 'expanded': isExpanded(faq.id) }"
                >
                    <button
                        class="faq-question"
                        @click="toggleQuestion(faq.id)"
                    >
                        <span class="faq-text">{{ faq.question }}</span>
                        <span class="faq-toggle">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 9l-7 7-7-7" class="toggle-icon"/>
                            </svg>
                        </span>
                    </button>
                    <div class="faq-answer" v-show="isExpanded(faq.id)">
                        <p>{{ faq.answer }}</p>
                    </div>
                </div>
            </div>

            <!-- Contact CTA -->
            <section class="contact-cta">
                <div class="cta-border"></div>
                <div class="cta-content">
                    <span class="roman-num">VI.</span>
                    <h2>Still Need Help?</h2>
                    <p>Our support team is available to assist with any questions or concerns not covered here.</p>
                    <div class="cta-actions">
                        <a href="mailto:support@shortlink.app" class="cta-btn cta-btn--primary">
                            <span>Email Support</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </a>
                        <Link :href="route('dashboard')" class="cta-btn cta-btn--secondary">
                            <span>Visit Dashboard</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <EditorialFooter />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

.help-page {
    font-family: 'Crimson Pro', serif;
    background: #fafafa;
    color: #1a1a1a;
    min-height: 100vh;
}

.help-content {
    padding: 140px 60px 80px;
    max-width: 900px;
    margin: 0 auto;
}

/* ── Header ──────────────────────────────────────────────────── */
.help-header {
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
    max-width: 600px;
}

/* ── Category Navigation ────────────────────────────────────── */
.category-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 48px;
}

.category-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 16px;
    background: #fff;
    border: 1px solid #ddd;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
}

.category-btn:hover {
    border-color: #e74c3c;
    color: #e74c3c;
}

.category-btn.active {
    background: #1a1a1a;
    border-color: #1a1a1a;
    color: #fafafa;
}

.category-num {
    font-size: 11px;
    color: #e74c3c;
    opacity: 0.7;
}

.category-btn.active .category-num {
    color: #d4af37;
    opacity: 1;
}

/* ── FAQ Accordion ──────────────────────────────────────────── */
.faq-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 80px;
}

.faq-item {
    background: #fff;
    border: 1px solid #e5e5e5;
    transition: all 0.3s ease;
}

.faq-item:hover {
    border-color: #ccc;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.faq-item.expanded {
    border-color: #1a1a1a;
}

.faq-question {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 28px;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: 'Crimson Pro', serif;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    transition: color 0.3s;
}

.faq-question:hover {
    color: #e74c3c;
}

.faq-toggle {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-left: 16px;
    color: #999;
    transition: all 0.3s ease;
}

.faq-toggle svg {
    width: 20px;
    height: 20px;
}

.faq-item.expanded .faq-toggle {
    transform: rotate(180deg);
    color: #e74c3c;
}

.toggle-icon {
    transition: transform 0.3s ease;
}

.faq-answer {
    padding: 0 28px 28px;
    border-top: 1px solid #f0f0f0;
}

.faq-answer p {
    font-size: 16px;
    line-height: 1.8;
    color: #555;
    margin: 0;
    padding-top: 20px;
}

/* ── Contact CTA ──────────────────────────────────────────────── */
.contact-cta {
    position: relative;
    background: #1a1a1a;
    padding: 60px;
    color: #888;
}

.cta-border {
    position: absolute;
    top: 0;
    left: 60px;
    right: 60px;
    height: 1px;
    background: linear-gradient(90deg, #d4af37 0%, transparent 100%);
}

.cta-content {
    position: relative;
    z-index: 1;
}

.cta-content .roman-num {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #e74c3c;
    display: block;
    margin-bottom: 16px;
}

.cta-content h2 {
    font-family: 'Oswald', sans-serif;
    font-size: 28px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #fafafa;
    margin-bottom: 16px;
}

.cta-content p {
    font-size: 17px;
    line-height: 1.7;
    color: #666;
    margin-bottom: 32px;
    max-width: 500px;
}

.cta-actions {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.cta-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 28px;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-decoration: none;
    transition: all 0.3s ease;
}

.cta-btn svg {
    width: 18px;
    height: 18px;
}

.cta-btn--primary {
    background: #e74c3c;
    color: #fff;
    border: none;
}

.cta-btn--primary:hover {
    background: #c0392b;
}

.cta-btn--secondary {
    background: transparent;
    color: #888;
    border: 1px solid #444;
}

.cta-btn--secondary:hover {
    border-color: #d4af37;
    color: #d4af37;
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 768px) {
    .help-content {
        padding: 120px 30px 60px;
    }

    h1 {
        font-size: 48px;
    }

    h1 span {
        font-size: 36px;
    }

    .category-nav {
        gap: 8px;
    }

    .category-btn {
        padding: 12px 16px;
        font-size: 11px;
    }

    .faq-question {
        padding: 20px 20px;
        font-size: 16px;
    }

    .faq-answer {
        padding: 0 20px 24px;
    }

    .contact-cta {
        padding: 40px 30px;
    }

    .cta-border {
        left: 30px;
        right: 30px;
    }

    .cta-content h2 {
        font-size: 22px;
    }
}
</style>
