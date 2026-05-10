<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    donationEnabled: { type: Boolean, default: false },
    donationButtonId: { type: String, default: '' },
});

const page = usePage();
const appName = computed(() => page.props.settings?.app_name ?? 'Super Url Shortener');
const footerText = computed(() => page.props.settings?.footer_text ?? `© ${new Date().getFullYear()} Super Url Shortener. All rights reserved.`);
</script>

<template>
    <footer class="editorial-footer">
        <!-- Main Footer Grid -->
        <div class="footer__grid">
            <!-- Column 1: Logo / About -->
            <div class="footer__col">
                <div class="footer__brand">
                    <span class="footer__logo-mark">/</span>
                    <span class="footer__logo-text">{{ appName }}</span>
                </div>
                <div class="footer__rule"></div>
                <p class="footer__about">
                    A comprehensive study in digital brevity. Transforming the way we share,
                    track, and understand online presence since MMXXV.
                </p>
                <div class="footer__social">
                    <a href="#" class="footer__social-link" aria-label="Twitter">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="GitHub">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                        </svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="LinkedIn">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Column 2: Links -->
            <div class="footer__col">
                <h4 class="footer__heading">
                    <span class="footer__num">I.</span>
                    Navigation
                </h4>
                <ul class="footer__list">
                    <li>
                        <Link :href="route('welcome')" class="footer__link">
                            <span class="footer__bullet">—</span>Home
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('dashboard')" class="footer__link">
                            <span class="footer__bullet">—</span>Dashboard
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('links.index')" class="footer__link">
                            <span class="footer__bullet">—</span>My Links
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('links.create')" class="footer__link">
                            <span class="footer__bullet">—</span>Create Link
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Column 3: Legal -->
            <div class="footer__col">
                <h4 class="footer__heading">
                    <span class="footer__num">II.</span>
                    Legal
                </h4>
                <ul class="footer__list">
                    <li>
                        <Link :href="route('legal.privacy')" class="footer__link">
                            <span class="footer__bullet">—</span>Privacy Policy
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('legal.terms')" class="footer__link">
                            <span class="footer__bullet">—</span>Terms of Service
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('legal.cookies')" class="footer__link">
                            <span class="footer__bullet">—</span>Cookie Policy
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('legal.gdpr')" class="footer__link">
                            <span class="footer__bullet">—</span>GDPR Compliance
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Column 4: Resources -->
            <div class="footer__col">
                <h4 class="footer__heading">
                    <span class="footer__num">III.</span>
                    Resources
                </h4>
                <ul class="footer__list">
                    <li>
                        <Link :href="route('help.center')" class="footer__link">
                            <span class="footer__bullet">—</span>Help Center
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('api-docs')" class="footer__link">
                            <span class="footer__bullet">—</span>API Documentation
                        </Link>
                    </li>
                    <li class="footer__contact-item">
                        <span class="footer__label">Email</span>
                        <a href="mailto:hello@shortlink.app" class="footer__contact-link">hello@shortlink.app</a>
                    </li>
                    <li class="footer__contact-item">
                        <span class="footer__label">Status</span>
                        <span class="footer__status">
                            <span class="footer__status-dot"></span>
                            All Systems Operational
                        </span>
                    </li>
                </ul>

                <!-- Donation button if enabled -->
                <form v-if="donationEnabled && donationButtonId" action="https://www.paypal.com/donate" method="post"
                    target="_blank" class="footer__donate">
                    <input type="hidden" name="hosted_button_id" :value="donationButtonId" />
                    <button type="submit" class="footer__donate-btn">
                        <span>Support Us</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="footer__copyright">
            <div class="footer__copyright-content">
                <p class="footer__copyright-text">{{ footerText }}</p>
                <p class="footer__credit">
                    Designed with precision by <a href="https://atiaeno.com" target="_blank" rel="noopener">atiaeno</a>
                </p>
            </div>
            <div class="footer__copyright-accent"></div>
        </div>
    </footer>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

/* ── Main Footer Container ──────────────────────────────────── */
.editorial-footer {
    background: #1a1a1a;
    color: #888;
    font-family: 'Crimson Pro', serif;
}

/* ── Grid Layout ───────────────────────────────────────────── */
.footer__grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
    gap: 60px;
    padding: 80px 60px;
    max-width: 1400px;
    margin: 0 auto;
}

/* ── Column Styling ────────────────────────────────────────── */
.footer__col {
    display: flex;
    flex-direction: column;
}

/* ── Brand Column ───────────────────────────────────────────── */
.footer__brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.footer__logo-mark {
    font-family: 'Oswald', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: #e74c3c;
    line-height: 1;
}

.footer__logo-text {
    font-family: 'Oswald', sans-serif;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: #fafafa;
}

.footer__rule {
    width: 60px;
    height: 2px;
    background: linear-gradient(90deg, #d4af37 0%, transparent 100%);
    margin-bottom: 20px;
}

.footer__about {
    font-size: 15px;
    line-height: 1.7;
    color: #666;
    margin-bottom: 24px;
    max-width: 280px;
}

/* ── Social Links ───────────────────────────────────────────── */
.footer__social {
    display: flex;
    gap: 12px;
}

.footer__social-link {
    width: 40px;
    height: 40px;
    border: 1px solid #333;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.3s ease;
}

.footer__social-link svg {
    width: 18px;
    height: 18px;
}

.footer__social-link:hover {
    border-color: #d4af37;
    color: #d4af37;
    transform: translateY(-2px);
}

/* ── Column Headings ────────────────────────────────────────── */
.footer__heading {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #fafafa;
    margin: 0 0 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.footer__num {
    font-size: 11px;
    color: #e74c3c;
    font-weight: 700;
}

/* ── Link Lists ──────────────────────────────────────────────── */
.footer__list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer__link {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #888;
    text-decoration: none;
    font-size: 15px;
    transition: all 0.3s ease;
    padding: 4px 0;
}

.footer__bullet {
    font-family: 'Oswald', sans-serif;
    color: #444;
    transition: color 0.3s;
}

.footer__link:hover {
    color: #d4af37;
}

.footer__link:hover .footer__bullet {
    color: #e74c3c;
}

/* ── Contact Items ──────────────────────────────────────────── */
.footer__contact-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 8px 0;
}

.footer__label {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #555;
}

.footer__contact-link {
    color: #888;
    text-decoration: none;
    font-size: 15px;
    transition: color 0.3s;
}

.footer__contact-link:hover {
    color: #d4af37;
}

.footer__status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #27ae60;
}

.footer__status-dot {
    width: 8px;
    height: 8px;
    background: #27ae60;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

/* ── Donate Button ──────────────────────────────────────────── */
.footer__donate {
    margin-top: 20px;
}

.footer__donate-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    background: transparent;
    border: 1px solid #e74c3c;
    color: #e74c3c;
    padding: 12px 20px;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s ease;
}

.footer__donate-btn svg {
    width: 16px;
    height: 16px;
}

.footer__donate-btn:hover {
    background: #e74c3c;
    color: #fff;
}

/* ── Copyright Bar ───────────────────────────────────────────── */
.footer__copyright {
    background: #111;
    position: relative;
}

.footer__copyright-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 60px;
    max-width: 1400px;
    margin: 0 auto;
    flex-wrap: wrap;
    gap: 16px;
}

.footer__copyright-text {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #555;
    margin: 0;
}

.footer__copyright-year {
    color: #d4af37;
}

.footer__credit {
    font-size: 13px;
    color: #444;
    margin: 0;
    font-style: italic;
}

.footer__credit a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s;
}

.footer__credit a:hover {
    color: #d4af37;
}

.footer__copyright-accent {
    position: absolute;
    top: 0;
    left: 60px;
    width: 100px;
    height: 1px;
    background: linear-gradient(90deg, #d4af37 0%, transparent 100%);
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .footer__grid {
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        padding: 60px 40px;
    }

    .footer__col:first-child {
        grid-column: 1 / -1;
    }

    .footer__about {
        max-width: 100%;
    }
}

@media (max-width: 640px) {
    .footer__grid {
        grid-template-columns: 1fr;
        padding: 40px 24px;
        gap: 32px;
    }

    .footer__copyright-content {
        flex-direction: column;
        text-align: center;
        padding: 20px 24px;
    }

    .footer__copyright-accent {
        left: 24px;
    }
}
</style>
