<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object,
});

const getBanStatusText = (banType) => {
    if (banType === 'soft') return 'Soft Banned';
    if (banType === 'hard') return 'Hard Banned';
    return 'Active';
};

const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');
const banFilter = ref(props.filters?.ban_type || '');

const showBanModal = ref(false);
const banningUser = ref(null);
const showDeleteModal = ref(false);
const deletingUser = ref(null);

const banForm = useForm({
    ban_type: 'soft',
    ban_reason: '',
    ban_duration_days: 7,
});

const roleForm = useForm({
    role: '',
});

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), {
            search: search.value,
            role: roleFilter.value,
            ban_type: banFilter.value,
        }, { preserveState: true });
    }, 300);
});

watch([roleFilter, banFilter], () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: roleFilter.value,
        ban_type: banFilter.value,
    }, { preserveState: true });
});

const updateRole = (user, newRole) => {
    roleForm.role = newRole;
    roleForm.post(route('admin.users.role', user.id), {
        onSuccess: () => {
            // Role updated successfully
        }
    });
};

const banUser = () => {
    banForm.post(route('admin.users.ban', banningUser.value.id), {
        onSuccess: () => {
            showBanModal.value = false;
            banningUser.value = null;
            banForm.reset();
        }
    });
};

const unbanUser = (user) => {
    router.post(route('admin.users.unban', user.id));
};

const deleteUser = () => {
    router.delete(route('admin.users.destroy', deletingUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingUser.value = null;
        }
    });
};

const statItems = computed(() => [
    {
        id: 'total',
        label: 'Total Users',
        value: props.stats?.total ?? 0,
        roman: 'I.',
        icon: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    },
    {
        id: 'admins',
        label: 'Admins',
        value: props.stats?.admins ?? 0,
        roman: 'II.',
        icon: `<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>`,
    },
    {
        id: 'banned',
        label: 'Banned',
        value: props.stats?.banned ?? 0,
        roman: 'III.',
        icon: `<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>`,
    },
]);

const icons = {
    search: `<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>`,
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
    eye: `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`,
    ban: `<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>`,
    unban: `<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>`,
    trash: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
};

const roleColors = {
    admin: 'role--admin',
    user: 'role--user',
    affiliate: 'role--affiliate',
};

const banTypeColors = {
    none: 'status--active',
    soft: 'status--soft',
    hard: 'status--hard',
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>

    <Head title="User Management" />

    <AdminLayout>
        <template #header-icon>
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </template>
        <template #header>Users</template>

        <div class="users-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Management</span>
                    <h1 class="page-header__title">User Directory</h1>
                    <p class="page-header__sub">Manage user accounts, roles, and bans.</p>
                </div>
                <Link :href="route('admin.users.create')" class="create-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Create User
                </Link>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div v-for="item in statItems" :key="item.id" class="stat-card">
                        <div class="stat-card__top">
                            <span class="stat-card__roman">{{ item.roman }}</span>
                            <div class="stat-card__icon-wrap" :class="`stat-card__icon-wrap--${item.id}`">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="item.icon" />
                            </div>
                        </div>
                        <span class="stat-card__value">{{ item.value.toLocaleString() }}</span>
                        <span class="stat-card__label">{{ item.label }}</span>
                    </div>
                </div>
            </section>

            <!-- Filters -->
            <section class="filters-section">
                <div class="filters-grid">
                    <div class="filter-field">
                        <span class="filter-field__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.search" />
                        </span>
                        <input v-model="search" type="text" class="filter-field__input" placeholder="Search users..." />
                    </div>
                    <select v-model="roleFilter" class="filter-select">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="affiliate">Affiliate</option>
                    </select>
                    <select v-model="banFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="none">Active</option>
                        <option value="soft">Soft Banned</option>
                        <option value="hard">Hard Banned</option>
                    </select>
                </div>
            </section>

            <!-- Users Table -->
            <section class="table-section">
                <div class="section-header">
                    <div>
                        <span class="section-marker">User List</span>
                        <p class="section-sub">{{ users.total }} total users</p>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="col-center">Links</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
                                        <div class="user-info">
                                            <span class="user-name">{{ user.name }}</span>
                                            <span class="user-email">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select v-model="user.role" @change="updateRole(user, $event.target.value)"
                                        :disabled="user.id === $page.props.auth.user.id" class="role-select"
                                        :class="roleColors[user.role]">
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                        <option value="affiliate">Affiliate</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="status-badge" :class="banTypeColors[user.ban_type || 'none']">
                                        {{ getBanStatusText(user.ban_type) }}
                                    </span>
                                </td>
                                <td class="col-center">
                                    <Link :href="route('admin.users.show', user.id)" class="links-count">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                        </svg>
                                        <span>{{ user.links_count ?? 0 }}</span>
                                    </Link>
                                </td>
                                <td class="date-cell">{{ formatDate(user.created_at) }}</td>
                                <td>
                                    <div class="actions-cell">
                                        <Link :href="route('admin.users.show', user.id)"
                                            class="btn-action btn-action--view" title="View User">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.eye" />
                                            <span>View</span>
                                        </Link>
                                        <Link :href="route('admin.users.edit', user.id)"
                                            class="btn-action btn-action--edit" title="Edit User">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.edit" />
                                            <span>Edit</span>
                                        </Link>
                                        <button v-if="user.ban_type !== 'none'" @click="unbanUser(user)"
                                            class="btn-action btn-action--unban" title="Unban User">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.unban" />
                                            <span>Unban</span>
                                        </button>
                                        <button v-else @click="showBanModal = true; banningUser = user"
                                            class="btn-action btn-action--ban" title="Ban User">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.ban" />
                                            <span>Ban</span>
                                        </button>
                                        <button @click="showDeleteModal = true; deletingUser = user"
                                            class="btn-action btn-action--delete" title="Delete User">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.trash" />
                                            <span>Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="pagination">
                    <template v-for="(link, i) in users.links" :key="i">
                        <span v-if="!link.url" class="pagination__link pagination__link--disabled"
                            v-html="link.label" />
                        <Link v-else :href="link.url" v-html="link.label" class="pagination__link"
                            :class="{ 'pagination__link--active': link.active }" />
                    </template>
                </div>
            </section>

        </div>

        <!-- Ban Modal -->
        <div v-if="showBanModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Ban User</h3>
                    <button @click="showBanModal = false" class="modal__close">×</button>
                </div>
                <form @submit.prevent="banUser">
                    <div class="modal__body">
                        <p class="modal__text">Ban <strong>{{ banningUser?.name }}</strong>?</p>

                        <div class="field">
                            <label class="field__label">Ban Type</label>
                            <select v-model="banForm.ban_type" class="field__input" required>
                                <option value="soft">Soft Ban (can still login)</option>
                                <option value="hard">Hard Ban (cannot login)</option>
                            </select>
                        </div>

                        <div class="field">
                            <label class="field__label">Reason</label>
                            <textarea v-model="banForm.ban_reason" class="field__input field__input--area" rows="3"
                                placeholder="Reason for banning..." required></textarea>
                        </div>

                        <div class="field" v-if="banForm.ban_type === 'soft'">
                            <label class="field__label">Duration (days)</label>
                            <input v-model.number="banForm.ban_duration_days" type="number" min="1" max="365"
                                class="field__input" required />
                        </div>
                    </div>
                    <div class="modal__footer">
                        <button type="button" @click="showBanModal = false" class="btn-ghost">Cancel</button>
                        <button type="submit" :disabled="banForm.processing" class="btn-danger">
                            {{ banForm.processing ? 'Banning...' : 'Ban User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Delete User</h3>
                    <button @click="showDeleteModal = false" class="modal__close">×</button>
                </div>
                <div class="modal__body">
                    <p class="modal__text">Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong>?
                    </p>
                    <p class="modal__warning">This action cannot be undone. All user data will be permanently removed.
                    </p>
                </div>
                <div class="modal__footer">
                    <button @click="showDeleteModal = false" class="btn-ghost">Cancel</button>
                    <button @click="deleteUser" :disabled="deletingUser?.id === $page.props.auth.user.id"
                        class="btn-danger">
                        Delete User
                    </button>
                </div>
            </div>
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

.create-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    text-decoration: none;
    transition: var(--transition);
    flex-shrink: 0;
}

.create-btn:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.create-btn svg {
    width: 14px;
    height: 14px;
}

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Stats Grid ───────────────────────────── */
.stats-section {
    margin-bottom: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.stat-card {
    background: var(--surface);
    padding: 20px 18px 16px;
    display: flex;
    flex-direction: column;
    transition: background 0.15s ease;
}

.stat-card:hover {
    background: #fdf9f5;
}

.stat-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.stat-card__roman {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    color: var(--red);
}

.stat-card__icon-wrap {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card__icon-wrap svg {
    width: 14px;
    height: 14px;
}

.stat-card__icon-wrap--total {
    background: #fef2f2;
    color: var(--red);
}

.stat-card__icon-wrap--admins {
    background: #eff6ff;
    color: #3b82f6;
}

.stat-card__icon-wrap--banned {
    background: #fef9f0;
    color: var(--gold);
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 26px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-card__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
}

/* ── Filters ────────────────────────────── */
.filters-section {
    margin-bottom: 24px;
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 12px;
    align-items: center;
}

.filter-field {
    position: relative;
    display: flex;
    align-items: center;
}

.filter-field__icon {
    position: absolute;
    left: 12px;
    width: 16px;
    height: 16px;
    color: var(--muted);
    pointer-events: none;
}

.filter-field__icon svg {
    width: 100%;
    height: 100%;
}

.filter-field__input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: var(--transition);
}

.filter-field__input:focus {
    outline: none;
    border-color: var(--red);
}

.filter-select {
    padding: 10px 32px 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    min-width: 140px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--red);
}

/* ── Table Section ──────────────────────── */
.table-section {
    margin-bottom: 32px;
}

.section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 14px;
}

.section-marker {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    display: block;
    margin-bottom: 2px;
}

.section-sub {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--muted);
    margin: 0;
}

/* ── Table ──────────────────────────────── */
.table-wrapper {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    text-align: left;
    padding: 12px 16px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
}

.users-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}

.users-table tr:last-child td {
    border-bottom: none;
}

.users-table tbody tr:hover {
    background: #fdf9f5;
}

/* ── User Cell ─────────────────────────── */
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: var(--red);
    color: var(--surface);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    color: var(--ink);
}

.user-email {
    font-size: 12px;
    color: var(--muted);
}

/* ── Role Select ────────────────────────── */
.role-select {
    appearance: none;
    -webkit-appearance: none;
    font-size: 11px;
    padding: 8px 32px 8px 14px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    font-family: var(--font-display);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: var(--transition);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    min-width: 110px;
}

.role-select:hover {
    border-color: #ccc;
    transform: translateY(-1px);
}

.role-select:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.15);
}

.role-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.role--admin {
    background-color: #fef2f2;
    color: var(--red);
    border-color: #fecaca;
}

.role--admin:hover:not(:disabled) {
    background-color: #fee2e2;
    border-color: #fca5a5;
}

.role--user {
    background-color: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
}

.role--user:hover:not(:disabled) {
    background-color: #dbeafe;
    border-color: #93c5fd;
}

.role--affiliate {
    background-color: #f0fdf4;
    color: #16a34a;
    border-color: #bbf7d0;
}

.role--affiliate:hover:not(:disabled) {
    background-color: #dcfce7;
    border-color: #86efac;
}

/* ── Status Badge ──────────────────────── */
.status-badge {
    font-size: 11px;
    padding: 5px 10px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-weight: 500;
    text-transform: uppercase;
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

/* ── Links Count ──────────────────────── */
.links-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: var(--radius);
    color: #0284c7;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}

.links-count:hover {
    background: #e0f2fe;
    border-color: #7dd3fc;
    color: #0369a1;
}

.links-count svg {
    width: 14px;
    height: 14px;
}

.col-center {
    text-align: center;
}

/* ── Date Cell ─────────────────────────── */
.date-cell {
    font-family: var(--font-display);
    font-size: 11px;
    color: var(--muted);
    text-transform: uppercase;
}

/* ── Actions ───────────────────────────── */
.actions-cell {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--muted);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    white-space: nowrap;
}

.btn-action svg {
    width: 14px;
    height: 14px;
}

.btn-action:hover {
    transform: translateY(-1px);
}

.btn-action--view {
    background: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
}

.btn-action--view:hover {
    background: #dbeafe;
    color: #2563eb;
}

.btn-action--edit {
    background: #fef9f0;
    color: var(--gold);
    border-color: #fde68a;
}

.btn-action--edit:hover {
    background: #fef3c7;
    color: #d97706;
}

.btn-action--ban {
    background: #fef9c3;
    color: #ca8a04;
    border-color: #fde047;
    width: 80px;
    justify-content: center;
}

.btn-action--ban:hover {
    background: #fef08a;
    color: #a16207;
}

.btn-action--unban {
    background: #f0fdf4;
    color: #16a34a;
    border-color: #86efac;
    width: 80px;
    justify-content: center;
}

.btn-action--unban:hover {
    background: #dcfce7;
    color: #15803d;
}

.btn-action--delete {
    background: #fef2f2;
    color: var(--red);
    border-color: #fecaca;
}

.btn-action--delete:hover {
    background: #fee2e2;
    color: var(--red-dark);
}

/* ── Pagination ────────────────────────── */
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-top: 24px;
}

.pagination__link {
    padding: 8px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}

.pagination__link:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.pagination__link--active {
    background: var(--red);
    color: var(--surface);
    border-color: var(--red);
}

.pagination__link--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: var(--surface-2);
}

/* ── Modal ─────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 26, 0.5);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
}

.modal__title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
}

.modal__close {
    width: 32px;
    height: 32px;
    background: transparent;
    border: none;
    color: var(--muted);
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius);
    transition: var(--transition);
}

.modal__close:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.modal__body {
    padding: 24px;
}

.modal__text {
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--ink);
    margin-bottom: 20px;
}

.modal__text strong {
    font-family: var(--font-display);
    font-weight: 600;
}

.modal__warning {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--muted);
    margin: 0;
}

.modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid var(--border);
}

/* ── Field ─────────────────────────────── */
.field {
    margin-bottom: 16px;
}

.field__label {
    display: block;
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    margin-bottom: 6px;
}

.field__input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: var(--transition);
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
}

.field__input--area {
    resize: vertical;
    min-height: 80px;
}

.btn-ghost {
    padding: 10px 20px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-danger {
    padding: 10px 20px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-danger:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

/* ── Responsive ────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .users-table th,
    .users-table td {
        padding: 10px 12px;
    }

    .actions-cell {
        flex-direction: column;
        gap: 4px;
    }

    .btn-ghost-sm,
    .btn-danger-sm {
        width: 100%;
    }
}
</style>
