<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    link: Object,
    user: Object,
    ads: Array,
    otherLinks: Array,
});

const form = useForm({
    destination_url: props.link.destination_url,
    og_title: props.link.og_title || '',
    og_description: props.link.og_description || '',
    ad_override: props.link.ad_override || 'inherit',
    ad_id: props.link.ad_id || '',
    visibility: props.link.visibility,
    password: props.link.password || '',
});

const submit = () => {
    form.put(route('admin.links.update', props.link.id));
};

const icons = {
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
    save: `<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>`,
    link: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    user: `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>`,
};
</script>

<template>

    <Head title="Edit Link" />

    <AdminLayout>
        <template #header-icon>
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
        </template>
        <template #header>Edit Link</template>

        <div class="edit-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Link Management</span>
                    <h1 class="page-header__title">Edit Short Link</h1>
                    <p class="page-header__sub">Modify link details for user <strong>{{ user.name }}</strong></p>
                </div>
                <Link :href="user.id ? route('admin.users.show', user.id) : route('admin.links.index')"
                    class="back-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.arrow" />
                    {{ user.id ? 'Back to User' : 'Back to Links' }}
                </Link>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Link Info Card -->
            <section class="info-section">
                <div class="info-card">
                    <div class="link-preview">
                        <div class="link-preview__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.link" />
                        </div>
                        <div class="link-preview__details">
                            <span class="link-preview__label">Short URL</span>
                            <a :href="`/${link.short_code}`" target="_blank" class="link-preview__url">
                                /{{ link.short_code }}
                            </a>
                        </div>
                        <div class="link-preview__stats">
                            <div class="preview-stat">
                                <span class="preview-stat__label">Clicks</span>
                                <span class="preview-stat__value">{{ link.clicks_count?.toLocaleString() ?? 0 }}</span>
                            </div>
                            <div class="preview-stat">
                                <span class="preview-stat__label">Status</span>
                                <span class="preview-stat__value"
                                    :class="link.is_active ? 'status--active' : 'status--inactive'">
                                    {{ link.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Form -->
            <section class="form-section">
                <div class="form-card">
                    <form @submit.prevent="submit">
                        <div class="form-grid">
                            <div class="field field--full">
                                <label class="field__label">Destination URL <span class="required">*</span></label>
                                <input v-model="form.destination_url" type="url" class="field__input" required />
                                <span v-if="form.errors.destination_url" class="field__error">{{
                                    form.errors.destination_url
                                }}</span>
                            </div>

                            <div class="field">
                                <label class="field__label">Visibility <span class="required">*</span></label>
                                <select v-model="form.visibility" class="field__input" required>
                                    <option value="public">Public</option>
                                    <option value="private">Private (password protected)</option>
                                </select>
                                <span v-if="form.errors.visibility" class="field__error">{{ form.errors.visibility
                                }}</span>
                            </div>

                            <div v-if="form.visibility === 'private'" class="field">
                                <label class="field__label">Password <span class="required">*</span></label>
                                <input v-model="form.password" type="password" class="field__input"
                                    placeholder="Min 6 characters" />
                                <span v-if="form.errors.password" class="field__error">{{ form.errors.password }}</span>
                            </div>

                            <div class="field field--full">
                                <label class="field__label">OG Title</label>
                                <input v-model="form.og_title" type="text" class="field__input"
                                    placeholder="Social share title" />
                                <span v-if="form.errors.og_title" class="field__error">{{ form.errors.og_title }}</span>
                            </div>

                            <div class="field field--full">
                                <label class="field__label">OG Description</label>
                                <textarea v-model="form.og_description" class="field__input field__input--textarea"
                                    rows="3" placeholder="Social share description"></textarea>
                                <span v-if="form.errors.og_description" class="field__error">{{
                                    form.errors.og_description
                                }}</span>
                            </div>

                            <div class="field">
                                <label class="field__label">Ad Override</label>
                                <select v-model="form.ad_override" class="field__input">
                                    <option value="inherit">Inherit from user settings</option>
                                    <option value="disable">Disable ads</option>
                                    <option value="force">Force specific ad</option>
                                </select>
                                <span v-if="form.errors.ad_override" class="field__error">{{ form.errors.ad_override
                                }}</span>
                            </div>

                            <div v-if="form.ad_override === 'force'" class="field">
                                <label class="field__label">Select Ad <span class="required">*</span></label>
                                <select v-model="form.ad_id" class="field__input" required>
                                    <option value="">Choose an ad...</option>
                                    <option v-for="ad in ads" :key="ad.id" :value="ad.id">{{ ad.title }}</option>
                                </select>
                                <span v-if="form.errors.ad_id" class="field__error">{{ form.errors.ad_id }}</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <Link :href="user.id ? route('admin.users.show', user.id) : route('admin.links.index')"
                                class="btn-ghost">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    v-html="icons.save" />
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Other Links Section -->
            <section v-if="otherLinks.length > 0 && user.id" class="other-links-section">
                <div class="section-header">
                    <h2 class="section-header__title">Other Links by {{ user.name }}</h2>
                    <Link :href="route('admin.users.show', user.id)" class="section-header__link">
                        View All
                    </Link>
                </div>
                <div class="other-links-list">
                    <div v-for="otherLink in otherLinks" :key="otherLink.id" class="other-link-item">
                        <div class="other-link__main">
                            <div class="other-link__icon"
                                :class="otherLink.is_active ? 'other-link__icon--active' : 'other-link__icon--inactive'">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    v-html="icons.link" />
                            </div>
                            <div class="other-link__details">
                                <Link :href="route('admin.links.edit', otherLink.id)" class="other-link__code">
                                    /{{ otherLink.short_code }}
                                </Link>
                                <span class="other-link__url" :title="otherLink.destination_url">{{
                                    otherLink.destination_url
                                    }}</span>
                            </div>
                        </div>
                        <div class="other-link__stats">
                            <span class="other-link__clicks">{{ otherLink.clicks_count?.toLocaleString() ?? 0 }}
                                clicks</span>
                            <span class="other-link__date">{{ new Date(otherLink.created_at).toLocaleDateString()
                                }}</span>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </AdminLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

:root {
    --font-display: 'Oswald', sans-serif;
    --font-body: 'Crimson Pro', serif;
    --red: #e74c3c;
    --red-dark: #c0392b;
    --gold: #d4af37;
    --ink: #1a1a1a;
    --ink-soft: #444;
    --muted: #888;
    --border: #e8e5e0;
    --surface: #fff;
    --surface-2: #f5f3ef;
    --radius: 4px;
    --transition: all 0.2s ease;
}

/* ── Page Header ──────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 20px;
}

.page-header__marker {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--red);
    display: block;
    margin-bottom: 8px;
}

.page-header__title {
    font-family: var(--font-display);
    font-size: 24px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 4px;
}

.page-header__sub {
    font-family: var(--font-body);
    font-size: 15px;
    font-style: italic;
    color: var(--muted);
    margin: 0;
}

.page-header__sub strong {
    color: var(--ink);
    font-weight: 600;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    text-decoration: none;
    transition: var(--transition);
}

.back-btn:hover {
    background: var(--surface-2);
}

.back-btn svg {
    width: 14px;
    height: 14px;
}

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Info Section ─────────────────────────── */
.info-section {
    margin-bottom: 24px;
}

.info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
}

.link-preview {
    display: flex;
    align-items: center;
    gap: 16px;
}

.link-preview__icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--red);
    flex-shrink: 0;
}

.link-preview__icon svg {
    width: 20px;
    height: 20px;
}

.link-preview__details {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
}

.link-preview__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
}

.link-preview__url {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 500;
    color: var(--red);
    text-decoration: none;
}

.link-preview__url:hover {
    color: var(--red-dark);
    text-decoration: underline;
}

.link-preview__stats {
    display: flex;
    gap: 24px;
}

.preview-stat {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.preview-stat__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
}

.preview-stat__value {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 500;
    color: var(--ink);
}

.status--active {
    color: #16a34a;
}

.status--inactive {
    color: var(--red);
}

/* ── Form Section ────────────────────────── */
.form-section {
    margin-bottom: 32px;
}

.form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 32px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 32px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.field--full {
    grid-column: 1 / -1;
}

.field__label {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
}

.required {
    color: var(--red);
}

.field__input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 15px;
    transition: var(--transition);
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
}

.field__input--textarea {
    resize: vertical;
    min-height: 80px;
}

.field__error {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--red);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
}

.btn-ghost {
    padding: 12px 24px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition);
}

.btn-primary:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.btn-primary svg {
    width: 14px;
    height: 14px;
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* ── Other Links Section ───────────────── */
.other-links-section {
    margin-bottom: 32px;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.section-header__title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
}

.section-header__link {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--red);
    text-decoration: none;
}

.section-header__link:hover {
    color: var(--red-dark);
}

.other-links-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.other-link-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
}

.other-link-item:hover {
    border-color: var(--red);
}

.other-link__main {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.other-link__icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius);
    flex-shrink: 0;
}

.other-link__icon--active {
    background: #f0fdf4;
    color: #16a34a;
}

.other-link__icon--inactive {
    background: #fef2f2;
    color: var(--red);
}

.other-link__icon svg {
    width: 14px;
    height: 14px;
}

.other-link__details {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.other-link__code {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 500;
    color: var(--red);
    text-decoration: none;
}

.other-link__code:hover {
    text-decoration: underline;
}

.other-link__url {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 300px;
}

.other-link__stats {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    flex-shrink: 0;
}

.other-link__clicks {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--ink);
}

.other-link__date {
    font-family: var(--font-body);
    font-size: 11px;
    font-style: italic;
    color: var(--muted);
}

/* ── Responsive ────────────────────────── */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .link-preview {
        flex-direction: column;
        align-items: flex-start;
    }

    .link-preview__stats {
        width: 100%;
        justify-content: space-between;
    }

    .preview-stat {
        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column;
    }

    .other-link-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .other-link__stats {
        flex-direction: row;
        align-items: center;
        gap: 12px;
        width: 100%;
    }

    .other-link__url {
        max-width: 100%;
    }
}
</style>
