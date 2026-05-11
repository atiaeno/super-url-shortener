<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    createdLink: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    destination_url: '',
    domain_id: null,
    custom_alias: '',
    campaign_tag: '',
    og_title: '',
    og_description: '',
    visibility: 'public',
    password: '',
});

const showAdvanced = ref(false);
const showModal = ref(false);
const modalLink = ref(null);
const copied = ref(false);

const visibilityHint = computed(() => {
    return form.visibility === 'public' ? 'Anyone can view analytics' : 'Password required to view analytics';
});

const qrUrl = computed(() => {
    if (!modalLink.value) return '';
    return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(modalLink.value.short_url)}`;
});

watch(() => props.createdLink, (val) => {
    if (val) {
        modalLink.value = val;
        showModal.value = true;
        form.reset();
    }
}, { immediate: true });

const submit = () => {
    form.post(route('links.store'));
};

const copyLink = async () => {
    if (!modalLink.value) return;
    await navigator.clipboard.writeText(modalLink.value.short_url);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const copyQrLink = async () => {
    if (!modalLink.value) return;
    await navigator.clipboard.writeText(qrUrl.value);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const shareLink = () => {
    if (!modalLink.value) return;
    if (navigator.share) {
        navigator.share({ title: 'Short Link', url: modalLink.value.short_url });
    } else {
        copyLink();
    }
};

const closeModal = () => {
    showModal.value = false;
    modalLink.value = null;
};

const createAnother = () => {
    closeModal();
};
</script>

<template>

    <Head title="Create Link" />

    <AuthenticatedLayout>
        <div class="create-link-page">
            <div class="page-header">
                <h1 class="page-title">Create New Link</h1>
                <p class="page-subtitle">Shorten your long URLs for easier sharing</p>
            </div>

            <div class="page-content">
                <div class="main-form">
                    <form @submit.prevent="submit" class="link-form">

                        <!-- 01 Destination -->
                        <div class="form-section">
                            <div class="section-header">
                                <span class="section-number">01</span>
                                <h2 class="section-title">Destination</h2>
                            </div>
                            <div class="form-card">
                                <div class="field">
                                    <label class="label">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5">
                                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                        </svg>
                                        Destination URL
                                        <span class="required">*</span>
                                    </label>
                                    <input v-model="form.destination_url" type="url"
                                        placeholder="https://example.com/your-long-url" class="input"
                                        :class="{ 'input--error': form.errors.destination_url }" autofocus required />
                                    <span v-if="form.errors.destination_url" class="error">{{
                                        form.errors.destination_url }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 02 Link Options -->
                        <div class="form-section">
                            <div class="section-header">
                                <span class="section-number">02</span>
                                <h2 class="section-title">Link Options</h2>
                            </div>
                            <div class="form-card">
                                <div class="field-row">
                                    <div class="field" v-if="$page.props.domains && $page.props.domains.length > 0">
                                        <label class="label">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <circle cx="12" cy="12" r="10" />
                                                <line x1="2" y1="12" x2="22" y2="12" />
                                                <path
                                                    d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                            </svg>
                                            Domain
                                        </label>
                                        <select v-model="form.domain_id" class="select"
                                            :class="{ 'select--error': form.errors.domain_id }">
                                            <option :value="null">Default Domain</option>
                                            <option v-for="domain in $page.props.domains" :key="domain.id"
                                                :value="domain.id">
                                                {{ domain.domain }} {{ domain.is_default ? '(Default)' : '' }}
                                            </option>
                                        </select>
                                        <span v-if="form.errors.domain_id" class="error">{{ form.errors.domain_id
                                            }}</span>
                                    </div>

                                    <div class="field">
                                        <label class="label">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                            </svg>
                                            Custom Alias
                                        </label>
                                        <div class="input-prefix">
                                            <span class="prefix">{{ $page.props.ziggy?.url ?? '' }}/</span>
                                            <input v-model="form.custom_alias" type="text" placeholder="my-slug"
                                                class="input" :class="{ 'input--error': form.errors.custom_alias }"
                                                pattern="[a-zA-Z0-9\-_]+" minlength="4" maxlength="20" />
                                        </div>
                                        <span v-if="form.errors.custom_alias" class="error">{{ form.errors.custom_alias
                                            }}</span>
                                        <span class="hint">4–20 chars, letters, numbers, hyphens</span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5">
                                            <path
                                                d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                                            <line x1="7" y1="7" x2="7.01" y2="7" />
                                        </svg>
                                        Campaign Tag
                                    </label>
                                    <input v-model="form.campaign_tag" type="text" placeholder="summer-promo"
                                        class="input" maxlength="100" />
                                    <span class="hint">Optional — for organizing your links</span>
                                </div>
                            </div>
                        </div>

                        <!-- 03 Visibility -->
                        <div class="form-section">
                            <div class="section-header">
                                <span class="section-number">03</span>
                                <h2 class="section-title">Visibility</h2>
                            </div>
                            <div class="form-card">
                                <div class="field">
                                    <label class="label">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        Who can view this link?
                                    </label>
                                    <div class="visibility-options">
                                        <label class="visibility-option"
                                            :class="{ 'visibility-option--active': form.visibility === 'public' }">
                                            <input type="radio" v-model="form.visibility" value="public"
                                                class="sr-only" />
                                            <div class="option-content">
                                                <span class="option-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.5">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <line x1="2" y1="12" x2="22" y2="12" />
                                                        <path
                                                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                                    </svg>
                                                </span>
                                                <div class="option-text">
                                                    <span class="option-title">Public</span>
                                                    <span class="option-desc">Anyone with the link</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="visibility-option"
                                            :class="{ 'visibility-option--active': form.visibility === 'private' }">
                                            <input type="radio" v-model="form.visibility" value="private"
                                                class="sr-only" />
                                            <div class="option-content">
                                                <span class="option-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.5">
                                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                                    </svg>
                                                </span>
                                                <div class="option-text">
                                                    <span class="option-title">Private</span>
                                                    <span class="option-desc">Password required</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="form.visibility === 'private'" class="field field--animate">
                                    <label class="label">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        Password <span class="required">*</span>
                                    </label>
                                    <input v-model="form.password" type="password"
                                        placeholder="Enter password (min 6 characters)" class="input"
                                        :class="{ 'input--error': form.errors.password }" minlength="6" required />
                                    <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced -->
                        <div class="form-section">
                            <button type="button" class="advanced-toggle" @click="showAdvanced = !showAdvanced">
                                <div class="toggle-header">
                                    <svg class="toggle-chevron" :class="{ 'toggle-chevron--open': showAdvanced }"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6" />
                                    </svg>
                                    <span class="toggle-text">Advanced Settings</span>
                                </div>
                                <span class="toggle-hint">OG Tags, Social Preview</span>
                            </button>

                            <div v-if="showAdvanced" class="advanced-fields">
                                <div class="form-card">
                                    <div class="field">
                                        <label class="label">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <line x1="17" y1="10" x2="3" y2="10" />
                                                <line x1="21" y1="6" x2="3" y2="6" />
                                                <line x1="21" y1="14" x2="3" y2="14" />
                                                <line x1="17" y1="18" x2="3" y2="18" />
                                            </svg>
                                            OG Title
                                        </label>
                                        <input v-model="form.og_title" type="text"
                                            placeholder="Override link preview title" class="input" maxlength="255" />
                                        <span class="hint">Custom title for social media previews</span>
                                    </div>
                                    <div class="field">
                                        <label class="label">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                <polyline points="14 2 14 8 20 8" />
                                                <line x1="16" y1="13" x2="8" y2="13" />
                                                <line x1="16" y1="17" x2="8" y2="17" />
                                                <polyline points="10 9 9 9 8 9" />
                                            </svg>
                                            OG Description
                                        </label>
                                        <textarea v-model="form.og_description"
                                            placeholder="Override link preview description" class="textarea" rows="3" />
                                        <span class="hint">Custom description for social media previews</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <Link :href="route('links.index')" class="btn btn--secondary">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                Cancel
                            </Link>
                            <button type="submit" class="btn btn--primary" :disabled="form.processing">
                                <svg v-if="!form.processing" class="btn-icon" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                <svg v-else class="btn-icon spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                </svg>
                                {{ form.processing ? 'Creating...' : 'Create Link' }}
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-header">
                            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <h3 class="sidebar-title">Quick Tips</h3>
                        </div>
                        <ul class="sidebar-list">
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Custom aliases make links memorable</span>
                            </li>
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Campaign tags help organize links</span>
                            </li>
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>OG fields control social previews</span>
                            </li>
                            <li>
                                <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Private links require a password</span>
                            </li>
                        </ul>
                    </div>

                    <div class="sidebar-card sidebar-card--accent">
                        <div class="sidebar-header">
                            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                            </svg>
                            <h3 class="sidebar-title">Pro Tip</h3>
                        </div>
                        <p class="sidebar-text">
                            Short, descriptive aliases are easier to remember and share.
                            Avoid complex combinations.
                        </p>
                    </div>
                </aside>
            </div>
        </div>

        <!-- Success Modal -->
        <Transition name="modal">
            <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
                <div class="modal">
                    <div class="modal-header">
                        <div class="modal-title-group">
                            <span class="modal-check">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <h2 class="modal-title">Link Created</h2>
                        </div>
                        <button class="modal-close" @click="closeModal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Short URL copy row -->
                        <div class="copy-row">
                            <span class="copy-url">{{ modalLink?.short_url }}</span>
                            <button class="copy-btn" @click="copyLink">
                                <svg v-if="!copied" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.5">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                {{ copied ? 'Copied!' : 'Copy' }}
                            </button>
                        </div>

                        <!-- QR Code -->
                        <div class="qr-section">
                            <div class="qr-wrapper">
                                <img :src="qrUrl" alt="QR Code" class="qr-image" />
                            </div>
                            <button class="qr-copy-btn" @click="copyQrLink">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                </svg>
                                Copy QR Link
                            </button>
                        </div>

                        <!-- Action buttons -->
                        <div class="modal-actions">
                            <button class="modal-btn modal-btn--share" @click="shareLink">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="18" cy="5" r="3" />
                                    <circle cx="6" cy="12" r="3" />
                                    <circle cx="18" cy="19" r="3" />
                                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                                </svg>
                                Share
                            </button>
                            <button class="modal-btn modal-btn--another" @click="createAnother">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                Create Another
                            </button>
                            <Link :href="route('links.index')" class="modal-btn modal-btn--view">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <line x1="8" y1="6" x2="21" y2="6" />
                                    <line x1="8" y1="12" x2="21" y2="12" />
                                    <line x1="8" y1="18" x2="21" y2="18" />
                                    <line x1="3" y1="6" x2="3.01" y2="6" />
                                    <line x1="3" y1="12" x2="3.01" y2="12" />
                                    <line x1="3" y1="18" x2="3.01" y2="18" />
                                </svg>
                                View All Links
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Uses global theme vars: --font-display (Oswald), --font-body (Crimson Pro), --font-sidebar (DM Sans) */
/* Colors: --red, --red-dark, --ink, --ink-soft, --muted, --border, --bg, --surface, --surface-2 */

.create-link-page {
    max-width: 1100px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.page-title {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0 0 4px;
}

.page-subtitle {
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--muted);
    margin: 0;
}

.page-content {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 28px;
    align-items: start;
}

.main-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 20px;
}

.section-header {
    display: flex;
    align-items: baseline;
    gap: 12px;
}

.section-number {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 2px;
    color: var(--red);
    text-transform: uppercase;
}

.section-title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink-soft);
    margin: 0;
}

.form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.field--animate {
    animation: slideDown 0.25s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.label {
    font-family: var(--font-sidebar);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 6px;
}

.icon {
    width: 13px;
    height: 13px;
    flex-shrink: 0;
    color: var(--muted);
}

.required {
    color: var(--red);
}

.input-wrapper {
    position: relative;
}

.input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-sidebar);
    font-size: 13px;
    color: var(--ink);
    background: var(--surface);
    transition: var(--transition);
    box-sizing: border-box;
}

.input::placeholder {
    color: var(--muted);
}

.input:focus {
    outline: none;
    border-color: var(--ink);
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.07);
}

.input--error {
    border-color: var(--red);
    box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.1);
}

.select {
    width: 100%;
    padding: 10px 36px 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-sidebar);
    font-size: 13px;
    color: var(--ink);
    background: var(--surface);
    transition: var(--transition);
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
}

.select:focus {
    outline: none;
    border-color: var(--ink);
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.07);
}

.select--error {
    border-color: var(--red);
}

.input-prefix {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    transition: var(--transition);
}

.input-prefix:focus-within {
    border-color: var(--ink);
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.07);
}

.prefix {
    font-family: var(--font-sidebar);
    font-size: 12px;
    color: var(--muted);
    padding: 10px 10px;
    background: var(--surface-2);
    white-space: nowrap;
    border-right: 1px solid var(--border);
    flex-shrink: 0;
}

.input-prefix .input {
    border: none;
    border-radius: 0;
    flex: 1;
    box-shadow: none !important;
}

.textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-sidebar);
    font-size: 13px;
    color: var(--ink);
    background: var(--surface);
    transition: var(--transition);
    resize: vertical;
    min-height: 80px;
    box-sizing: border-box;
}

.textarea::placeholder {
    color: var(--muted);
}

.textarea:focus {
    outline: none;
    border-color: var(--ink);
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.07);
}

.error {
    font-family: var(--font-sidebar);
    font-size: 11px;
    color: var(--red);
}

.hint {
    font-family: var(--font-sidebar);
    font-size: 11px;
    color: var(--muted);
}

/* ── Visibility Options ─────────────────────────── */
.visibility-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.visibility-option {
    cursor: pointer;
}

.visibility-option input {
    display: none;
}

.option-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
    background: var(--surface);
}

.visibility-option:hover .option-content {
    background: var(--surface-2);
}

.visibility-option--active .option-content {
    border-color: var(--ink);
    background: var(--surface-2);
}

.option-icon {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    border-radius: var(--radius);
    flex-shrink: 0;
}

.option-icon svg {
    width: 16px;
    height: 16px;
    color: var(--ink-soft);
}

.visibility-option--active .option-icon {
    background: var(--ink);
}

.visibility-option--active .option-icon svg {
    color: #fff;
}

.option-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.option-title {
    font-family: var(--font-sidebar);
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
}

.option-desc {
    font-family: var(--font-sidebar);
    font-size: 11px;
    color: var(--muted);
}

/* ── Advanced Toggle ────────────────────────────── */
.advanced-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 16px;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
}

.advanced-toggle:hover {
    border-color: var(--ink-soft);
    background: var(--surface);
}

.toggle-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toggle-chevron {
    width: 14px;
    height: 14px;
    color: var(--muted);
    transition: transform 0.2s ease;
    flex-shrink: 0;
}

.toggle-chevron--open {
    transform: rotate(90deg);
}

.toggle-text {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink-soft);
}

.toggle-hint {
    font-family: var(--font-sidebar);
    font-size: 11px;
    color: var(--muted);
}

.advanced-fields {
    animation: slideDown 0.25s ease-out;
}

.advanced-fields .form-card {
    background: var(--surface-2);
}

/* ── Form Actions ───────────────────────────────── */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn {
    font-family: var(--font-sidebar);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    padding: 10px 22px;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.btn-icon {
    width: 13px;
    height: 13px;
    flex-shrink: 0;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.spin {
    animation: spin 0.8s linear infinite;
}

.btn--primary {
    background: var(--red);
    color: #fff;
    border: none;
}

.btn--primary:hover {
    background: var(--red-dark);
}

.btn--primary:disabled {
    background: var(--muted);
    cursor: not-allowed;
}

.btn--secondary {
    background: var(--surface);
    color: var(--ink-soft);
    border: 1px solid var(--border);
}

.btn--secondary:hover {
    border-color: var(--ink-soft);
    color: var(--ink);
}

/* ── Sidebar ────────────────────────────────────── */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 66px;
}

.sidebar-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px;
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.sidebar-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    color: var(--muted);
}

.sidebar-title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
}

.sidebar-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-list li {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink-soft);
    line-height: 1.5;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.tip-icon {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
    margin-top: 2px;
    color: var(--red);
}

.sidebar-card--accent {
    background: var(--ink);
    border-color: var(--ink);
}

.sidebar-card--accent .sidebar-header {
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

.sidebar-card--accent .sidebar-title {
    color: rgba(255, 255, 255, 0.9);
}

.sidebar-card--accent .sidebar-icon {
    color: rgba(255, 255, 255, 0.7);
}

.sidebar-text {
    font-family: var(--font-body);
    font-size: 14px;
    color: rgba(255, 255, 255, 0.75);
    line-height: 1.6;
    margin: 0;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

@media (max-width: 900px) {
    .page-content {
        grid-template-columns: 1fr;
    }

    .sidebar {
        position: static;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .sidebar-card {
        flex: 1;
        min-width: 240px;
    }
}

@media (max-width: 640px) {
    .field-row {
        grid-template-columns: 1fr;
    }

    .visibility-options {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}

/* ── Success Modal ──────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 26, 0.5);
    backdrop-filter: blur(3px);
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 40px rgba(26, 26, 26, 0.15);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 20px 16px;
    border-bottom: 1px solid var(--border);
}

.modal-title-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-check {
    width: 28px;
    height: 28px;
    background: var(--red);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.modal-check svg {
    width: 14px;
    height: 14px;
    color: #fff;
}

.modal-title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
}

.modal-close {
    width: 28px;
    height: 28px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    transition: var(--transition);
}

.modal-close svg {
    width: 14px;
    height: 14px;
}

.modal-close:hover {
    border-color: var(--ink-soft);
    color: var(--ink);
}

.modal-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.copy-row {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.copy-url {
    flex: 1;
    font-family: var(--font-sidebar);
    font-size: 13px;
    color: var(--ink);
    padding: 10px 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    background: var(--surface-2);
}

.copy-btn {
    font-family: var(--font-sidebar);
    font-size: 12px;
    font-weight: 600;
    padding: 10px 14px;
    background: var(--ink);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: var(--transition);
    white-space: nowrap;
    flex-shrink: 0;
}

.copy-btn svg {
    width: 12px;
    height: 12px;
}

.copy-btn:hover {
    background: var(--ink-soft);
}

.qr-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.qr-wrapper {
    padding: 8px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.qr-image {
    display: block;
    width: 160px;
    height: 160px;
}

.qr-copy-btn {
    font-family: var(--font-sidebar);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3px;
    padding: 7px 14px;
    background: var(--surface);
    color: var(--ink-soft);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: var(--transition);
}

.qr-copy-btn svg {
    width: 12px;
    height: 12px;
}

.qr-copy-btn:hover {
    border-color: var(--ink-soft);
    color: var(--ink);
}

.modal-actions {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
}

.modal-btn {
    font-family: var(--font-sidebar);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3px;
    padding: 9px 12px;
    border-radius: var(--radius);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: var(--transition);
    text-decoration: none;
    border: 1px solid var(--border);
}

.modal-btn svg {
    width: 12px;
    height: 12px;
}

.modal-btn--share {
    background: var(--surface-2);
    color: var(--ink-soft);
}

.modal-btn--share:hover {
    border-color: var(--ink-soft);
    color: var(--ink);
}

.modal-btn--another {
    background: var(--red);
    color: #fff;
    border-color: var(--red);
}

.modal-btn--another:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.modal-btn--view {
    background: var(--surface-2);
    color: var(--ink-soft);
}

.modal-btn--view:hover {
    border-color: var(--ink-soft);
    color: var(--ink);
}

/* Modal transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-active .modal,
.modal-leave-active .modal {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal,
.modal-leave-to .modal {
    transform: translateY(-12px);
    opacity: 0;
}
</style>
