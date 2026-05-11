<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    link: { type: Object, required: true },
    ads: { type: Array, default: () => [] },
});

const form = useForm({
    destination_url: props.link.destination_url ?? '',
    campaign_tag: props.link.campaign_tag ?? '',
    og_title: props.link.og_title ?? '',
    og_description: props.link.og_description ?? '',
    is_active: props.link.is_active ?? true,
});

const showAdvanced = ref(!!(props.link.og_title || props.link.og_description));

const submit = () => {
    form.patch(route('links.update', props.link.id));
};
</script>

<template>

    <Head :title="`Edit Link`" />

    <AuthenticatedLayout>
        <template #header><span class="material-icons">edit</span> Edit Link</template>

        <div class="page-content">
            <!-- Link Info Card -->
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Short Link</span>
                    <a :href="link.domain ? `https://${link.domain.domain}/${link.short_code}` : `/${link.short_code}`"
                        target="_blank" class="info-link">
                        <span v-if="link.domain">{{ link.domain.domain }}/</span>{{ link.short_code }}
                        <span class="material-icons">open_in_new</span>
                    </a>
                </div>
                <div v-if="link.custom_alias" class="info-row">
                    <span class="info-label">Custom Alias</span>
                    <span class="info-value">{{ link.custom_alias }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created</span>
                    <span class="info-value">{{ new Date(link.created_at).toLocaleDateString() }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span :class="['status-badge', link.is_active ? 'active' : 'inactive']">
                        <span class="material-icons">{{ link.is_active ? 'check_circle' : 'cancel' }}</span>
                        {{ link.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="form-card">
                <form @submit.prevent="submit" class="form">
                    <div class="field">
                        <label class="label">Destination URL <span class="required">*</span></label>
                        <input v-model="form.destination_url" type="url" placeholder="https://example.com/your-long-url"
                            class="input" :class="{ 'input--error': form.errors.destination_url }" required />
                        <span v-if="form.errors.destination_url" class="error">{{ form.errors.destination_url }}</span>
                    </div>

                    <div class="field">
                        <label class="label">Campaign Tag <span class="optional">(optional)</span></label>
                        <input v-model="form.campaign_tag" type="text" placeholder="summer-promo" class="input"
                            maxlength="100" />
                    </div>

                    <div class="field">
                        <label class="label">Link Status</label>
                        <div class="toggle-group">
                            <button type="button" class="toggle-btn" :class="{ 'toggle-btn--active': form.is_active }"
                                @click="form.is_active = true">
                                <span class="toggle-icon">✓</span>
                                <span>Active</span>
                            </button>
                            <button type="button" class="toggle-btn" :class="{ 'toggle-btn--active': !form.is_active }"
                                @click="form.is_active = false">
                                <span class="toggle-icon">✕</span>
                                <span>Inactive</span>
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Toggle -->
                    <button type="button" class="advanced-toggle" @click="showAdvanced = !showAdvanced">
                        <span class="material-icons">{{ showAdvanced ? 'expand_less' : 'expand_more' }}</span>
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

                    <!-- Actions -->
                    <div class="form-actions">
                        <Link :href="route('links.show', link.id)" class="btn-secondary">Cancel</Link>
                        <button type="submit" class="btn-primary" :disabled="form.processing || !form.isDirty">
                            <span v-if="form.processing">Saving…</span>
                            <span v-else><span class="material-icons">save</span> Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600&display=swap');

.page-content {
    max-width: 600px;
    margin: 0 auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 16px 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #666;
}

.info-value {
    font-size: 14px;
    color: #333;
}

.info-link {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: var(--red);
    text-decoration: none;
    font-weight: 500;
}

.info-link:hover {
    text-decoration: underline;
}

.info-link .material-icons {
    font-size: 14px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge .material-icons {
    font-size: 14px;
}

.status-badge.active {
    background: #dcfce7;
    color: #16a34a;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #dc2626;
}

.form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 24px;
}

.form {
    display: flex;
    flex-direction: column;
    gap: 20px;
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
    color: #333;
}

.required {
    color: #dc2626;
}

.optional {
    color: #888;
    font-weight: 400;
}

.input {
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: var(--ink);
    background: #fff;
    outline: none;
    transition: border-color 200ms;
}

.input:focus {
    outline: none;
    border-color: var(--red);
}

.input--error {
    border-color: #dc2626;
}

.textarea {
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    color: #1a1a1a;
    background: #fff;
    resize: vertical;
    font-family: inherit;
}

.textarea:focus {
    outline: none;
    border-color: var(--red);
}

.error {
    font-size: 12px;
    color: #dc2626;
}

.toggle-group {
    display: flex;
    gap: 8px;
}

.toggle-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border: 1px solid var(--border);
    border-radius: 4px;
    background: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-btn:hover {
    background: #f5f5f5;
}

.toggle-btn--active {
    background: var(--red);
    border-color: var(--red);
    color: #fff;
}

.toggle-icon {
    font-size: 14px;
}

.advanced-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 0;
    background: none;
    border: none;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #666;
    cursor: pointer;
    transition: color 200ms;
}

.advanced-toggle:hover {
    color: #333;
}

.advanced-toggle .material-icons {
    font-size: 20px;
}

.advanced-fields {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 16px;
    background: #fafafa;
    border-radius: 8px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 8px;
}

.btn-primary {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 12px 24px;
    background: var(--red);
    color: #fff;
    border: none;
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary .material-icons {
    font-size: 18px;
}

.btn-secondary {
    padding: 12px 24px;
    background: #fff;
    color: #666;
    border: 1px solid var(--border);
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 200ms;
}

.btn-secondary:hover {
    background: #f5f5f5;
    color: #333;
}

/* Material Icons */
.material-icons {
    font-size: 20px;
    vertical-align: middle;
    margin-right: 4px;
}
</style>
