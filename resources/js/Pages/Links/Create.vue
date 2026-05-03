<!-- Â© Atia Hegazy â€” atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    destination_url: '',
    custom_alias: '',
    campaign_tag: '',
    og_title: '',
    og_description: '',
    visibility: 'public',
    password: '',
});

const showAdvanced = ref(false);

const submit = () => {
    form.post(route('links.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Create Link â€” Editorial" />

    <AuthenticatedLayout>
        <template #header>New Entry</template>

        <div class="editorial-layout">
            <!-- Main Form -->
            <div class="editorial-form-section">
                <div class="form-header">
                    <span class="roman-num">II.</span>
                    <span class="section-label">Composition</span>
                </div>

                <form @submit.prevent="submit" class="editorial-form">
                    <!-- Destination URL -->
                    <div class="field">
                        <label class="field__label">Destination URL <span class="field__required">*</span></label>
                        <input v-model="form.destination_url" type="url" placeholder="https://example.com/your-long-url"
                            class="field__input" :class="{ 'field__input--error': form.errors.destination_url }"
                            autofocus required />
                        <span v-if="form.errors.destination_url" class="field__error">{{ form.errors.destination_url
                            }}</span>
                    </div>

                    <!-- Custom Alias -->
                    <div class="field">
                        <label class="field__label">Custom Alias <span class="field__optional">(optional)</span></label>
                        <div class="field__prefix-wrap">
                            <span class="field__prefix">{{ $page.props.ziggy?.url ?? '' }}/</span>
                            <input v-model="form.custom_alias" type="text" placeholder="my-custom-slug"
                                class="field__input field__input--prefixed"
                                :class="{ 'field__input--error': form.errors.custom_alias }" pattern="[a-zA-Z0-9\-_]+"
                                minlength="4" maxlength="20" />
                        </div>
                        <span v-if="form.errors.custom_alias" class="field__error">{{ form.errors.custom_alias }}</span>
                        <span class="field__hint">4â€“20 characters, letters, numbers, hyphens only</span>
                    </div>

                    <!-- Campaign Tag -->
                    <div class="field">
                        <label class="field__label">Campaign Tag <span class="field__optional">(optional)</span></label>
                        <input v-model="form.campaign_tag" type="text" placeholder="summer-promo" class="field__input"
                            maxlength="100" />
                    </div>

                    <!-- Visibility Toggle -->
                    <div class="field">
                        <label class="field__label">Visibility</label>
                        <div class="visibility-toggle">
                            <button type="button" class="visibility-btn"
                                :class="{ 'visibility-btn--active': form.visibility === 'public' }"
                                @click="form.visibility = 'public'">
                                <span class="visibility-icon">ðŸŒ</span>
                                <span class="visibility-label">Public</span>
                            </button>
                            <button type="button" class="visibility-btn"
                                :class="{ 'visibility-btn--active': form.visibility === 'private' }"
                                @click="form.visibility = 'private'">
                                <span class="visibility-icon">ðŸ”’</span>
                                <span class="visibility-label">Private</span>
                            </button>
                        </div>
                        <span v-if="form.errors.visibility" class="field__error">{{ form.errors.visibility }}</span>
                        <span class="field__hint">{{ form.visibility === 'public' ? 'Anyone can view analytics for this link' : 'Password required to view analytics' }}</span>
                    </div>

                    <!-- Password Field (shown only for private links) -->
                    <div v-if="form.visibility === 'private'" class="field">
                        <label class="field__label">Password <span class="field__required">*</span></label>
                        <input v-model="form.password" type="password"
                            placeholder="Enter password for private link (min 6 characters)" class="field__input"
                            :class="{ 'field__input--error': form.errors.password }" minlength="6" required />
                        <span v-if="form.errors.password" class="field__error">{{ form.errors.password }}</span>
                        <span class="field__hint">Minimum 6 characters required</span>
                    </div>

                    <!-- Advanced OG toggle -->
                    <button type="button" class="toggle-advanced" @click="showAdvanced = !showAdvanced">
                        <span class="roman-num-small">{{ showAdvanced ? 'â–²' : 'â–¼' }}</span>
                        <span>Advanced (OG / Social Preview)</span>
                    </button>

                    <div v-if="showAdvanced" class="advanced-fields">
                        <div class="field">
                            <label class="field__label">OG Title</label>
                            <input v-model="form.og_title" type="text" placeholder="Override link preview title"
                                class="field__input" maxlength="255" />
                        </div>
                        <div class="field">
                            <label class="field__label">OG Description</label>
                            <textarea v-model="form.og_description" placeholder="Override link preview description"
                                class="field__textarea" rows="3" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <Link :href="route('links.index')" class="btn-secondary">Cancel</Link>
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            <span v-if="form.processing">Processingâ€¦</span>
                            <span v-else>Create Entry</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Editorial Sidebar -->
            <aside class="editorial-sidebar">
                <div class="sidebar-rule"></div>
                <h3 class="sidebar-title">Editorial Notes</h3>
                <ul class="sidebar-list">
                    <li>Custom aliases make your links more recognizable and memorable.</li>
                    <li>Campaign tags let you group and filter links in analytics.</li>
                    <li>OG fields control how links appear when shared on social media.</li>
                    <li>Private links require a password â€” only those with the password can view analytics.</li>
                    <li>Public links allow anyone to view their analytics statistics.</li>
                </ul>

                <div class="sidebar-rule"></div>

                <div class="sidebar-quote">
                    <p>"The most elegant solution is often the one that says less."</p>
                    <span class="quote-attribution">â€” ShortLink Editorial</span>
                </div>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

:root {
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
}

/* â”€â”€ Editorial Layout â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.editorial-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 32px;
    max-width: 1000px;
}

/* â”€â”€ Form Section â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.editorial-form-section {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 32px;
}

.form-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.roman-num {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 700;
    color: var(--red);
    letter-spacing: 1px;
}

.roman-num-small {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    color: #888;
}

.section-label {
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--ink);
}

.editorial-form {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

/* â”€â”€ Fields â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.field {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.field__label {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--muted);
}

.field__required {
    color: #e74c3c;
    margin-left: 4px;
}

.field__optional {
    font-family: 'Crimson Pro', serif;
    font-size: 12px;
    font-style: italic;
    text-transform: none;
    letter-spacing: 0;
    color: #aaa;
    font-weight: 400;
}

.field__input {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 10px 12px;
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink);
    outline: none;
    transition: border-color 0.2s;
    width: 100%;
}

.field__input::placeholder {
    color: #bbb;
    font-style: italic;
}

.field__input:focus {
    border-color: var(--red);
}

.field__input--error {
    border-color: var(--red);
}

.field__textarea {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 10px 12px;
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink);
    outline: none;
    transition: border-color 0.2s;
    resize: vertical;
    min-height: 80px;
}

.field__textarea:focus {
    border-color: #e74c3c;
}

.field__prefix-wrap {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: border-color 0.2s;
}

.field__prefix-wrap:focus-within {
    border-color: var(--red);
}

.field__prefix {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--muted);
    white-space: nowrap;
    padding-left: 12px;
}

.field__input--prefixed {
    border: none;
    border-radius: 0;
    flex: 1;
}

.field__input--prefixed:focus {
    border: none;
}

.field__error {
    font-family: 'Crimson Pro', serif;
    font-size: 13px;
    font-style: italic;
    color: #e74c3c;
}

.field__hint {
    font-family: 'Crimson Pro', serif;
    font-size: 12px;
    font-style: italic;
    color: #aaa;
}

/* â”€â”€ Visibility Toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.visibility-toggle {
    display: flex;
    gap: 12px;
}

.visibility-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.2s;
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--muted);
}

.visibility-btn:hover {
    border-color: #ccc;
    color: var(--ink-soft);
}

.visibility-btn--active {
    background: var(--red);
    border-color: var(--red);
    color: #fff;
}

.visibility-btn--active:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
    color: #fff;
}

.visibility-icon {
    font-size: 14px;
}

.visibility-label {
    font-weight: 500;
}

/* â”€â”€ Advanced Toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.toggle-advanced {
    display: flex;
    align-items: center;
    gap: 10px;
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--muted);
    text-align: left;
    padding: 0;
    transition: color 0.2s;
}

.toggle-advanced:hover {
    color: #1a1a1a;
}

.advanced-fields {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    border-left: 3px solid var(--gold);
}

/* â”€â”€ Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    margin-top: 8px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 10px 24px;
    background: var(--red);
    color: #fff;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover:not(:disabled) {
    background: #c0392b;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: transparent;
    color: var(--muted);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    text-decoration: none;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    color: #1a1a1a;
    border-color: #1a1a1a;
}

/* â”€â”€ Editorial Sidebar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.editorial-sidebar {
    padding: 24px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    height: fit-content;
}

.sidebar-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--gold), var(--border));
    margin-bottom: 20px;
}

.sidebar-title {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--gold);
    margin: 0 0 16px 0;
}

.sidebar-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-left: 20px;
    margin: 0 0 24px 0;
}

.sidebar-list li {
    font-family: var(--font-body);
    font-size: 13px;
    line-height: 1.5;
    color: var(--muted);
}

.sidebar-quote {
    padding: 20px 0;
}

.sidebar-quote p {
    font-family: var(--font-body);
    font-size: 14px;
    font-style: italic;
    line-height: 1.5;
    color: var(--ink-soft);
    margin: 0 0 8px 0;
}

.quote-attribution {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--muted);
}

/* â”€â”€ Responsive â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@media (max-width: 900px) {
    .editorial-layout {
        grid-template-columns: 1fr;
        gap: 32px;
    }

    .editorial-form-section {
        padding: 24px;
    }

    .editorial-sidebar {
        display: none;
    }
}

@media (max-width: 480px) {
    .form-actions {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>
