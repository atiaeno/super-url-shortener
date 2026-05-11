<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    domains: Object,
});

const showAddForm = ref(false);
const editingDomain = ref(null);
const form = useForm({
    domain: '',
    is_active: true,
    is_default: false,
    description: '',
});

const resetForm = () => {
    form.reset();
    editingDomain.value = null;
    showAddForm.value = false;
};

const editDomain = (domain) => {
    editingDomain.value = domain;
    form.domain = domain.domain;
    form.is_active = domain.is_active;
    form.is_default = domain.is_default;
    form.description = domain.description || '';
    showAddForm.value = true;
};

const saveDomain = () => {
    if (editingDomain.value) {
        form.put(route('admin.alias-domains.update', editingDomain.value), {
            onSuccess: () => {
                resetForm();
            },
        });
    } else {
        form.post(route('admin.alias-domains.store'), {
            onSuccess: () => {
                resetForm();
            },
        });
    }
};

const deleteDomain = (domain) => {
    if (!confirm(`Are you sure you want to delete "${domain.domain}"?`)) {
        return;
    }

    useForm({}).delete(route('admin.alias-domains.destroy', domain), {
        onSuccess: () => {
            // Page will reload automatically
        },
    });
};

const toggleStatus = (domain) => {
    useForm({}).post(route('admin.alias-domains.toggle', domain), {
        onSuccess: () => {
            window.location.reload();
        },
    });
};

const setDefault = (domain) => {
    useForm({}).post(route('admin.alias-domains.default', domain), {
        onSuccess: () => {
            window.location.reload();
        },
    });
};

const cancelEdit = () => {
    resetForm();
};

const icons = {
    domains: `<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>`,
    add: `<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    toggle: `<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>`,
    star: `<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>`,
    delete: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>`,
};
</script>

<template>

    <Head title="Alias Domains" />
    <AdminLayout>
        <template #header>Alias Domains</template>

        <div class="domains-page">
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Domain Administration</span>
                    <h1 class="page-header__title">Alias Domains</h1>
                    <p class="page-header__sub">Manage multiple domains for short URLs.</p>
                </div>
                <button @click="showAddForm = true" class="btn-primary" v-if="!showAddForm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add Domain
                </button>
            </header>

            <div class="section-rule"></div>

            <div v-if="showAddForm" class="modal-backdrop" @click.self="cancelEdit">
                <div class="modal">
                    <div class="modal__header">
                        <h3 class="modal__title">{{ editingDomain ? 'Edit Domain' : 'Add New Domain' }}</h3>
                        <button @click="cancelEdit" class="modal__close">
                            <span class="material-icons">close</span>
                        </button>
                    </div>
                    <form @submit.prevent="saveDomain">
                        <div class="modal__body">
                            <div class="field">
                                <label class="field__label">Domain *</label>
                                <input v-model="form.domain" type="text" class="field__input"
                                    :class="{ 'field__input--error': form.errors.domain }" placeholder="example.com"
                                    required />
                                <span v-if="form.errors.domain" class="field__error">{{ form.errors.domain }}</span>
                                <small class="field__hint">Enter the domain without protocol (e.g., example.com)</small>
                            </div>

                            <div class="field">
                                <label class="field__label">Description</label>
                                <textarea v-model="form.description" class="field__input field__input--area" rows="3"
                                    placeholder="Optional description for this domain"></textarea>
                                <span v-if="form.errors.description" class="field__error">{{ form.errors.description
                                    }}</span>
                            </div>

                            <div class="form-checkboxes">
                                <label class="checkbox-label">
                                    <input v-model="form.is_active" type="checkbox" class="checkbox-input" />
                                    <span class="checkbox-text">Active</span>
                                </label>

                                <label class="checkbox-label">
                                    <input v-model="form.is_default" type="checkbox" class="checkbox-input" />
                                    <span class="checkbox-text">Default Domain</span>
                                </label>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" @click="cancelEdit" class="btn-ghost">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                {{ form.processing ? 'Saving...' : (editingDomain ? 'Update Domain' : 'Add Domain') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <section class="table-section">
                <div class="table-card">
                    <div v-if="!domains.data?.length" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.domains" />
                        </div>
                        <p class="empty-state__title">No domains configured yet</p>
                        <p class="empty-state__text">Start by adding your first alias domain.</p>
                        <button @click="showAddForm = true" class="btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                class="btn-icon">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            Add First Domain
                        </button>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="domains-table">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Description</th>
                                    <th class="col-center">Links</th>
                                    <th class="col-center">Status</th>
                                    <th class="col-center">Default</th>
                                    <th class="col-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="domain in domains.data" :key="domain.id">
                                    <td>
                                        <div class="domain-cell">
                                            <div class="domain-name">{{ domain.domain }}</div>
                                            <div v-if="domain.is_default" class="domain-badge domain-badge--default">
                                                Default
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span v-if="domain.description" class="domain-description">{{ domain.description
                                            }}</span>
                                        <span v-else class="domain-description domain-description--empty">—</span>
                                    </td>
                                    <td class="col-center">
                                        <span class="domain-count">{{ domain.links_count }}</span>
                                    </td>
                                    <td class="col-center">
                                        <span class="status-badge"
                                            :class="domain.is_active ? 'status-badge--active' : 'status-badge--inactive'">
                                            {{ domain.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <span v-if="domain.is_default" class="domain-badge domain-badge--yes">Yes</span>
                                        <span v-else class="domain-badge domain-badge--no">No</span>
                                    </td>
                                    <td class="col-center">
                                        <div class="actions-cell">
                                            <button @click="editDomain(domain)" class="btn-icon btn-icon--edit"
                                                title="Edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path
                                                        d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </button>

                                            <button @click="toggleStatus(domain)"
                                                :class="['btn-icon', domain.is_active ? 'btn-icon--deactivate' : 'btn-icon--activate']"
                                                :title="domain.is_active ? 'Deactivate' : 'Activate'">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                                                    <line x1="1" y1="10" x2="23" y2="10" />
                                                </svg>
                                            </button>

                                            <button v-if="!domain.is_default" @click="setDefault(domain)"
                                                class="btn-icon btn-icon--star" title="Set as Default">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <polygon
                                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                </svg>
                                            </button>

                                            <button @click="deleteDomain(domain)" class="btn-icon btn-icon--delete"
                                                title="Delete" :disabled="domain.links_count > 0">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
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
    --bg: #fafafa;
    --surface: #fff;
    --surface-2: #f5f3ef;
    --surface-3: #e5e7eb;
    --radius: 4px;
    --shadow: 0 2px 12px rgba(26, 26, 26, 0.07);
    --transition: all 0.2s ease;
}

.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
}

.page-header__left {
    flex: 1;
}

.page-header__marker {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--red);
    display: block;
    margin-bottom: 6px;
}

.page-header__title {
    font-family: var(--font-display);
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--ink);
    margin: 0 0 6px;
    line-height: 1.1;
}

.page-header__sub {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

.section-rule {
    height: 1px;
    background: var(--border);
    margin: 0 0 24px;
}

.btn-primary {
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: var(--radius);
    border: none;
    background: var(--red);
    color: white;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    background: var(--red-dark);
}

.btn-ghost {
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: transparent;
    color: var(--ink);
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--muted);
    cursor: pointer;
    transition: var(--transition);
}

.btn-icon svg {
    width: 14px;
    height: 14px;
}

.btn-icon:hover {
    background: var(--surface-3);
    color: var(--ink);
}

.btn-icon--edit:hover {
    background: var(--border);
    color: var(--red);
}

.btn-icon--activate:hover {
    background: var(--border);
    color: #10b981;
}

.btn-icon--deactivate:hover {
    background: var(--border);
    color: #f59e0b;
}

.btn-icon--star:hover {
    background: var(--border);
    color: #8b5cf6;
}

.btn-icon--delete:hover {
    background: var(--border);
    color: var(--red);
}

.btn-icon:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal {
    background: var(--surface);
    border-radius: var(--radius);
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.modal__title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.modal__close {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius);
    transition: var(--transition);
}

.modal__close .material-icons {
    font-size: 20px;
}

.modal__close:hover {
    color: var(--ink);
    background: var(--surface-2);
}

.modal__body {
    padding: 20px;
}

.modal__body .field {
    margin-bottom: 16px;
}

.modal__body .field:last-child {
    margin-bottom: 0;
}

.modal__body .form-checkboxes {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}

.modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    background: var(--surface-2);
}

.field {
    display: flex;
    flex-direction: column;
}

.field__label {
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
    margin-bottom: 8px;
}

.field__input {
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font-body);
    font-size: 14px;
    background: var(--surface);
    color: var(--ink);
    transition: var(--transition);
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.field__input--error {
    border-color: var(--red);
}

.field__input--area {
    resize: vertical;
    min-height: 80px;
}

.field__error {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--red);
    margin-top: 6px;
}

.field__hint {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--muted);
    margin-top: 6px;
}

.form-checkboxes {
    display: flex;
    gap: 24px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-input {
    width: 16px;
    height: 16px;
    accent-color: var(--red);
}

.checkbox-text {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink);
}

.table-section {
    margin-bottom: 24px;
}

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.table-wrapper {
    overflow-x: auto;
}

.domains-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-body);
    font-size: 14px;
}

.domains-table th {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    background: var(--surface-2);
}

.domains-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}

.domains-table tr:hover {
    background: var(--surface-2);
}

.col-center {
    text-align: center;
}

.domain-cell {
    display: flex;
    align-items: center;
    gap: 8px;
}

.domain-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    color: var(--ink);
}

.domain-description {
    font-size: 13px;
    color: var(--muted);
}

.domain-description--empty {
    font-style: italic;
}

.domain-count {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    color: var(--ink);
}

.domain-badge {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 6px;
    border-radius: var(--radius);
    white-space: nowrap;
}

.domain-badge--default {
    background: var(--red);
    color: white;
}

.domain-badge--yes {
    background: #d1fae5;
    color: #065f46;
}

.domain-badge--no {
    background: var(--surface-2);
    color: var(--muted);
}

.status-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 8px;
    border-radius: var(--radius);
    white-space: nowrap;
}

.status-badge--active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge--inactive {
    background: #fee2e2;
    color: #991b1b;
}

.actions-cell {
    display: flex;
    gap: 6px;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 48px 24px;
}

.empty-state__icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 20px;
    color: var(--muted);
    opacity: 0.5;
}

.empty-state__icon svg {
    width: 100%;
    height: 100%;
}

.empty-state__title {
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 8px;
}

.empty-state__text {
    font-family: var(--font-body);
    font-size: 14px;
    font-style: italic;
    color: var(--muted);
    margin: 0 0 24px;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .domains-table {
        font-size: 13px;
    }

    .domains-table th,
    .domains-table td {
        padding: 10px 12px;
    }
}
</style>
