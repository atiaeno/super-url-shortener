<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    variant: { type: String, default: 'light' }, // 'light' | 'dark' | 'blend'
    showNav: { type: Boolean, default: true },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const canLogin = computed(() => page.props.canLogin ?? true);
const canRegister = computed(() => page.props.canRegister ?? true);
const features = computed(() => page.props.features ?? { affiliate: true, ads: true, gdpr: true });
const affiliateEnabled = computed(() => features.value.affiliate !== false);

const scrolled = ref(false);
const handleScroll = () => { scrolled.value = window.scrollY > 60; };
onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <header class="masthead" :class="[`masthead--${variant}`, { 'masthead--scrolled': scrolled }]">
        <div class="masthead__brand">
            <Link :href="route('welcome')" class="masthead__logo">
                <span class="masthead__logo-mark">/</span>
                <span class="masthead__logo-text">ShortLink</span>
            </Link>
            <div class="masthead__rule"></div>
        </div>

        <nav v-if="showNav" class="masthead__nav">
            <template v-if="user">
                <Link :href="route('dashboard')" class="masthead__link">
                    <span class="masthead__link-num">I.</span>
                    Dashboard
                </Link>
                <Link :href="route('links.index')" class="masthead__link">
                    <span class="masthead__link-num">II.</span>
                    My Links
                </Link>
                <Link v-if="affiliateEnabled" href="/partners" class="masthead__link">
                    <span class="masthead__link-num">III.</span>
                    Affiliate
                </Link>
                <Link :href="route('profile.edit')" class="masthead__link">
                    <span class="masthead__link-num">IV.</span>
                    Profile
                </Link>
            </template>
            <template v-else>
                <Link v-if="affiliateEnabled" href="/partners" class="masthead__link">
                    <span class="masthead__link-num">I.</span>
                    Affiliate
                </Link>
                <Link v-if="canLogin" :href="route('login')" class="masthead__link">
                    <span class="masthead__link-num">II.</span>
                    Enter
                </Link>
                <Link v-if="canRegister" :href="route('register')" class="masthead__link masthead__link--cta">
                    <span class="masthead__link-num">III.</span>
                    Join
                </Link>
            </template>
        </nav>

        <div class="masthead__accent"></div>
    </header>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

.masthead {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    padding: 24px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 100;
    transition: all 0.3s ease;
}

/* Light variant - for light backgrounds */
.masthead--light {
    background: rgba(250, 250, 250, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    color: #1a1a1a;
}

/* Dark variant - for dark backgrounds */
.masthead--dark {
    background: rgba(26, 26, 26, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    color: #fafafa;
}

/* Blend variant - mix-blend-mode for editorial effect */
.masthead--blend {
    mix-blend-mode: difference;
    color: #fff;
    background: transparent;
}

/* Scrolled state - solid background */
.masthead--blend.masthead--scrolled {
    mix-blend-mode: normal;
    background: rgba(10, 10, 10, 0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    color: #fafafa;
}

/* Brand section */
.masthead__brand {
    display: flex;
    align-items: center;
    gap: 20px;
}

.masthead__logo {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: inherit;
}

.masthead__logo-mark {
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #e74c3c;
    line-height: 1;
}

.masthead__logo-text {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 6px;
    text-transform: uppercase;
}

.masthead__rule {
    width: 40px;
    height: 1px;
    background: currentColor;
    opacity: 0.3;
}

/* Navigation */
.masthead__nav {
    display: flex;
    gap: 32px;
    align-items: center;
}

.masthead__link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: inherit;
    text-decoration: none;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    position: relative;
    padding: 8px 0;
    transition: color 0.3s;
}

.masthead__link-num {
    font-size: 10px;
    color: #e74c3c;
    opacity: 0.7;
    transition: opacity 0.3s;
}

.masthead__link:hover .masthead__link-num {
    opacity: 1;
}

.masthead__link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 1px;
    background: #e74c3c;
    transition: width 0.3s ease;
}

.masthead__link:hover::after {
    width: 100%;
}

/* CTA variant */
.masthead__link--cta {
    background: #e74c3c;
    color: #fff;
    padding: 12px 24px;
    border: none;
}

.masthead__link--cta .masthead__link-num {
    color: rgba(255, 255, 255, 0.7);
}

.masthead__link--cta::after {
    display: none;
}

.masthead__link--cta:hover {
    background: #c0392b;
}

/* Decorative accent */
.masthead__accent {
    position: absolute;
    bottom: -1px;
    left: 60px;
    width: 120px;
    height: 2px;
    background: linear-gradient(90deg, #e74c3c 0%, #d4af37 100%);
}

.masthead--blend .masthead__accent {
    background: linear-gradient(90deg, #fff 0%, transparent 100%);
}

/* Responsive */
@media (max-width: 768px) {
    .masthead {
        padding: 16px 24px;
    }

    .masthead__logo-text {
        font-size: 12px;
        letter-spacing: 3px;
    }

    .masthead__rule {
        display: none;
    }

    .masthead__nav {
        gap: 16px;
    }

    .masthead__link {
        font-size: 11px;
        letter-spacing: 1px;
    }

    .masthead__link-num {
        display: none;
    }

    .masthead__accent {
        left: 24px;
        width: 80px;
    }
}

@media (max-width: 480px) {
    .masthead__nav {
        gap: 12px;
    }

    .masthead__link {
        font-size: 10px;
    }
}
</style>
