<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appName = computed(() => page.props.settings?.app_name ?? 'ShortLink');
const logoUrl = computed(() => page.props.settings?.logo_url ?? '');
</script>

<template>
    <div class="auth-editorial">
        <!-- Subtle Grid Background -->
        <div class="editorial-grid"></div>

        <!-- Main Content Container -->
        <div class="auth-container">
            <!-- Minimal Masthead -->
            <div class="auth-masthead">
                <Link href="/" class="logo-link">
                    <img v-if="logoUrl" :src="logoUrl" :alt="appName" class="logo-image" />
                    <span v-else class="logo-mark">{{ appName }}</span>
                </Link>
            </div>

            <!-- Issue Label -->
            <div class="issue-label">Authentication — Vol. XLVII</div>

            <!-- Auth Card -->
            <div class="auth-card">
                <!-- Content Slot -->
                <div class="auth-content">
                    <slot />
                </div>
            </div>

            <!-- Minimal Footer -->
            <div class="auth-footer">
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Match Welcome.vue editorial aesthetic */
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

.auth-editorial {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #fafafa;
    color: #1a1a1a;
    font-family: 'Crimson Pro', serif;
    position: relative;
    overflow: hidden;
    padding: 2rem;
}

/* Subtle Grid with red accent */
.editorial-grid {
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(220, 38, 38, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(220, 38, 38, 0.03) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}

/* Main Container */
.auth-container {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    max-width: 620px;
}

/* Masthead */
.auth-masthead {
    margin-bottom: 2rem;
}

.logo-link {
    text-decoration: none;
    color: inherit;
    transition: opacity 200ms ease;
}

.logo-link:hover {
    opacity: 0.8;
}

.logo-mark {
    font-family: 'Oswald', sans-serif;
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -1px;
    color: #dc2626;
}

.logo-image {
    height: 32px;
    width: auto;
    object-fit: contain;
}

/* Issue Label - matches Welcome.vue */
.issue-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #dc2626;
    margin-bottom: 2rem;
}

/* Auth Card - minimal, clean */
.auth-card {
    position: relative;
    width: 100%;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-top: 3px solid #dc2626;
    padding: 3rem;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.08);
}

/* Auth Content */
.auth-content {
    position: relative;
    z-index: 1;
}

/* Footer */
.auth-footer {
    margin-top: 2rem;
    text-align: center;
}

.footer-credit {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #dc2626;
}

/* Responsive */
@media (max-width: 640px) {
    .auth-editorial {
        padding: 1.5rem;
    }

    .auth-card {
        padding: 2rem;
    }

    .logo-mark {
        font-size: 24px;
    }
}
</style>
