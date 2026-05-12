<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};

const icons = {
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
    save: `<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>`,
    user: `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>`,
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>

    <Head title="Edit User" />

    <AdminLayout>
        <template #header-icon>
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </template>
        <template #header>Edit User</template>

        <div class="edit-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">User Management</span>
                    <h1 class="page-header__title">Edit {{ user.name }}</h1>
                    <p class="page-header__sub">Member since {{ formatDate(user.created_at) }}</p>
                </div>
                <Link :href="route('admin.users.index')" class="back-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.arrow" />
                    Back to Users
                </Link>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Form -->
            <section class="form-section">
                <div class="form-card">
                    <form @submit.prevent="submit">
                        <div class="form-grid">
                            <div class="field">
                                <label class="field__label">Name</label>
                                <input v-model="form.name" type="text" class="field__input" required />
                                <span v-if="form.errors.name" class="field__error">{{ form.errors.name }}</span>
                            </div>

                            <div class="field">
                                <label class="field__label">Email</label>
                                <input v-model="form.email" type="email" class="field__input" required />
                                <span v-if="form.errors.email" class="field__error">{{ form.errors.email }}</span>
                            </div>

                            <div class="field">
                                <label class="field__label">Role</label>
                                <select v-model="form.role" class="field__input" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                    <option value="affiliate">Affiliate</option>
                                </select>
                                <span v-if="form.errors.role" class="field__error">{{ form.errors.role }}</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <Link :href="route('admin.users.index')" class="btn-ghost">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    v-html="icons.save" />
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Current Status -->
            <section class="status-section">
                <div class="section-header">
                    <span class="section-marker">Current Status</span>
                </div>
                <div class="status-grid">
                    <div class="status-item">
                        <span class="status-item__label">Account Status</span>
                        <span class="status-item__value"
                            :class="user.ban_type === 'none' ? 'status--active' : (user.ban_type === 'soft' ? 'status--soft' : 'status--hard')">
                            {{ user.ban_type === 'none' ? 'Active' : (user.ban_type === 'soft' ? 'Soft Banned' : 'Hard Banned') }}
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-item__label">Current Role</span>
                        <span class="status-item__value role-badge" :class="`role--${user.role}`">
                            {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                        </span>
                    </div>
                </div>
            </section>

        </div>
    </AdminLayout>
</template>

<style scoped>
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

.field__label {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
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

/* ── Status Section ────────────────────── */
.status-section {
    margin-bottom: 32px;
}

.section-header {
    margin-bottom: 16px;
}

.section-marker {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.status-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.status-item__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
}

.status-item__value {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 6px 12px;
    border-radius: var(--radius);
    width: fit-content;
}

.status--active {
    background: #f0fdf4;
    color: #16a34a;
}

.status--soft {
    background: #fef9c3;
    color: #ca8a04;
}

.status--hard {
    background: #fef2f2;
    color: var(--red);
}

.role-badge {
    padding: 6px 12px;
    border-radius: var(--radius);
    font-size: 12px;
}

.role--admin {
    background: #fef2f2;
    color: var(--red);
}

.role--user {
    background: #eff6ff;
    color: #3b82f6;
}

.role--affiliate {
    background: #f0fdf4;
    color: #16a34a;
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

    .status-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }
}
</style>

