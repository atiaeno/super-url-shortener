<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();
const gdprEnabled = computed(() => page.props.settings?.features_gdpr === 'true');

const visible = ref(false);

onMounted(() => {
    if (!gdprEnabled.value) return;
    if (localStorage.getItem('gdpr_consent')) return;
    setTimeout(() => { visible.value = true; }, 600);
});

const accept = () => {
    localStorage.setItem('gdpr_consent', 'accepted');
    visible.value = false;
};

const decline = () => {
    localStorage.setItem('gdpr_consent', 'declined');
    visible.value = false;
};
</script>

<template>
    <Transition name="gdpr">
        <div v-if="visible" class="gdpr-banner" role="dialog" aria-label="Cookie consent">
            <div class="gdpr-banner__accent"></div>
            <div class="gdpr-banner__head">
                <div class="gdpr-banner__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                </div>
                <span class="gdpr-banner__title">Privacy & Cookies</span>
                <button @click="decline" class="gdpr-banner__close" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <p class="gdpr-banner__desc">
                We use cookies to improve your experience and analyze traffic.
                See our <Link :href="route('legal.privacy')" class="gdpr-banner__link">Privacy Policy</Link>
                and <Link :href="route('legal.cookies')" class="gdpr-banner__link">Cookie Policy</Link>.
            </p>
            <div class="gdpr-banner__actions">
                <button @click="decline" class="gdpr-btn gdpr-btn--ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                    Decline
                </button>
                <button @click="accept" class="gdpr-btn gdpr-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Accept All
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.gdpr-banner {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9999;
    width: 300px;
    background: #111;
    border: 1px solid #2a2a2a;
    border-radius: 2px;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
    overflow: hidden;
}

/* Red top accent line */
.gdpr-banner__accent {
    height: 3px;
    background: #e74c3c;
}

/* Header row */
.gdpr-banner__head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 16px 0;
}

.gdpr-banner__icon {
    width: 28px;
    height: 28px;
    background: #e74c3c;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
}

.gdpr-banner__icon svg {
    width: 15px;
    height: 15px;
}

.gdpr-banner__title {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #fff;
    flex: 1;
}

.gdpr-banner__close {
    width: 24px;
    height: 24px;
    background: none;
    border: none;
    cursor: pointer;
    color: #555;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: color 0.15s;
}

.gdpr-banner__close:hover {
    color: #fff;
}

.gdpr-banner__close svg {
    width: 14px;
    height: 14px;
}

/* Description */
.gdpr-banner__desc {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #999;
    line-height: 1.6;
    margin: 0;
    padding: 12px 16px;
}

.gdpr-banner__link {
    color: #e74c3c;
    text-decoration: underline;
}

.gdpr-banner__link:hover {
    color: #ff6b5b;
}

/* Actions */
.gdpr-banner__actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-top: 1px solid #1e1e1e;
}

.gdpr-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 10px;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    border: none;
    transition: all 0.15s;
}

.gdpr-btn svg {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
}

.gdpr-btn--ghost {
    background: #1a1a1a;
    color: #666;
    border-right: 1px solid #1e1e1e;
}

.gdpr-btn--ghost:hover {
    background: #222;
    color: #aaa;
}

.gdpr-btn--primary {
    background: #e74c3c;
    color: #fff;
}

.gdpr-btn--primary:hover {
    background: #c0392b;
}

/* Transition */
.gdpr-enter-active,
.gdpr-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.gdpr-enter-from,
.gdpr-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

@media (max-width: 400px) {
    .gdpr-banner {
        right: 12px;
        bottom: 12px;
        width: calc(100vw - 24px);
    }
}
</style>
