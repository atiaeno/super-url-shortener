<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const visibilityHint = computed(() => {
    return form.visibility === 'public' ? 'Anyone can view analytics' : 'Password required to view analytics';
});

const submit = () => {
    form.post(route('links.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Create Link" />

    <AuthenticatedLayout>
        <template #header>🔗 New Link</template>

        <div class="page-content">
            <div class="form-card">
                <form @submit.prevent="submit" class="form">
                    <div class="field">
                        <label class="label">Destination URL <span class="required">*</span></label>
                        <input v-model="form.destination_url" type="url" placeholder="https://example.com/your-long-url"
                            class="input" :class="{ 'input--error': form.errors.destination_url }" autofocus required />
                        <span v-if="form.errors.destination_url" class="error">{{ form.errors.destination_url }}</span>
                    </div>

                    <div class="field">
                        <label class="label">Custom Alias <span class="optional">(optional)</span></label>
                        <div class="input-prefix">
                            <span class="prefix">{{ $page.props.ziggy?.url ?? '' }}/</span>
                            <input v-model="form.custom_alias" type="text" placeholder="my-custom-slug" class="input"
                                :class="{ 'input--error': form.errors.custom_alias }" pattern="[a-zA-Z0-9\-_]+"
                                minlength="4" maxlength="20" />
                        </div>
                        <span v-if="form.errors.custom_alias" class="error">{{ form.errors.custom_alias }}</span>
                        <span class="hint">4-20 characters, letters, numbers, hyphens only</span>
                    </div>

                    <div class="field">
                        <label class="label">Campaign Tag <span class="optional">(optional)</span></label>
                        <input v-model="form.campaign_tag" type="text" placeholder="summer-promo" class="input"
                            maxlength="100" />
                    </div>

                    <div class="field">
                        <label class="label">Visibility</label>
                        <div class="toggle-group">
                            <button type="button" class="toggle-btn"
                                :class="{ 'toggle-btn--active': form.visibility === 'public' }"
                                @click="form.visibility = 'public'">
                                <span class="toggle-icon">🌐</span>
                                <span>Public</span>
                            </button>
                            <button type="button" class="toggle-btn"
                                :class="{ 'toggle-btn--active': form.visibility === 'private' }"
                                @click="form.visibility = 'private'">
                                <span class="toggle-icon">🔒</span>
                                <span>Private</span>
                            </button>
                        </div>
                        <span v-if="form.errors.visibility" class="error">{{ form.errors.visibility }}</span>
                        <span class="hint">{{ visibilityHint }}</span>
                    </div>

                    <div v-if="form.visibility === 'private'" class="field">
                        <label class="label">Password <span class="required">*</span></label>
                        <input v-model="form.password" type="password" placeholder="Enter password (min 6 characters)"
                            class="input" :class="{ 'input--error': form.errors.password }" minlength="6" required />
                        <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
                    </div>

                    <button type="button" class="advanced-toggle" @click="showAdvanced = !showAdvanced">
                        <span>{{ showAdvanced ? '▼' : '▶' }}</span>
                        <span>Advanced (OG / Social Preview)</span>
                    </button>

                    <div v-if="showAdvanced" class="advanced-fields">
                        <div class="field">
                            <label class="label">OG Title</label>
                            <input v-model="form.og_title" type="text" placeholder="Override link preview title"
                                class="input" maxlength="255" />
                        </div>
                        <div class="field">
                            <label class="label">OG Description</label>
                            <textarea v-model="form.og_description" placeholder="Override link preview description"
                                class="textarea" rows="3" />
                        </div>
                    </div>

                    <div class="form-actions">
                        <Link :href="route('links.index')" class="btn btn--secondary">Cancel</Link>
                        <button type="submit" class="btn btn--primary" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Link' }}
                        </button>
                    </div>
                </form>
            </div>

            <aside class="sidebar">
                <h3 class="sidebar-title">Tips</h3>
                <ul class="sidebar-list">
                    <li>Custom aliases make links more memorable</li>
                    <li>Campaign tags help organize your links</li>
                    <li>OG fields control social media previews</li>
                    <li>Private links require a password</li>
                </ul>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');

:root {
    --red: #e74c3c;
    --ink: #1a1a1a;
    --muted: #444;
    --border: #e5e5e5;
    --surface: #fff;
}

.page-content {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 32px;
    max-width: 1000px;
    margin: 0 auto;
    padding: 24px;
}

.form-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 32px;
}

.form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #1a1a1a;
}

.required {
    color: #e74c3c;
}

.optional {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    font-weight: 400;
    text-transform: none;
    color: #666;
}

.input {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 12px 14px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: var(--ink);
    outline: none;
    transition: border-color 0.2s;
    width: 100%;
}

.input:focus {
    border-color: #1a1a1a;
}

.input--error {
    border-color: #e74c3c;
}

.input-prefix {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: 4px;
    overflow: hidden;
}

.input-prefix:focus-within {
    border-color: #1a1a1a;
}

.prefix {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: var(--muted);
    padding-left: 14px;
    white-space: nowrap;
    background: #f5f5f5;
    padding: 12px;
}

.input-prefix .input {
    border: none;
    border-radius: 0;
    flex: 1;
}

.input-prefix .input:focus {
    border: none;
}

.textarea {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 12px 14px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: var(--ink);
    outline: none;
    resize: vertical;
    width: 100%;
}

.error {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #e74c3c;
}

.hint {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    color: #666;
}

.toggle-group {
    display: flex;
    gap: 12px;
}

.toggle-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    color: var(--muted);
}

.toggle-btn:hover {
    border-color: #ccc;
    color: #1a1a1a;
}

.toggle-btn--active {
    border-color: #1a1a1a;
    background: #1a1a1a;
    color: #fff;
}

.toggle-icon {
    font-size: 16px;
}

.advanced-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    cursor: pointer;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #666;
    padding: 8px 0;
}

.advanced-toggle:hover {
    color: #1a1a1a;
}

.advanced-fields {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 16px;
    background: #f9f9f9;
    border-radius: 4px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}

.btn {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    padding: 12px 24px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn--primary {
    background: #e74c3c;
    color: #fff;
    border: none;
}

.btn--primary:hover {
    background: #c0392b;
}

.btn--primary:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.btn--secondary {
    background: transparent;
    color: #666;
    border: 1px solid var(--border);
}

.btn--secondary:hover {
    border-color: #1a1a1a;
    color: #1a1a1a;
}

.sidebar {
    padding: 20px;
    background: #fafafa;
    border: 1px solid var(--border);
    border-radius: 4px;
    height: fit-content;
}

.sidebar-title {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #1a1a1a;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #1a1a1a;
}

.sidebar-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.sidebar-list li {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    color: #444;
    line-height: 1.5;
    padding-left: 16px;
    position: relative;
}

.sidebar-list li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 6px;
    width: 6px;
    height: 6px;
    background: #e74c3c;
    border-radius: 50%;
}

@media (max-width: 768px) {
    .page-content {
        grid-template-columns: 1fr;
        padding: 16px;
    }

    .sidebar {
        display: none;
    }

    .form-card {
        padding: 20px;
    }
}
</style>
