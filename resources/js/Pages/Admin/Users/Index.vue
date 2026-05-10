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

const icons = {
    users: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    search: `<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>`,
    eye: `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`,
    ban: `<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>`,
    unban: `<polyline points="20 6 9 17 4 12"/>`,
    trash: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    plus: `<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>`,
    admin: `<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>`,
    user: `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>`,
    affiliate: `<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>`,
};

const statItems = computed(() => [
    { id: 'total', label: 'Total Users', value: props.stats?.total ?? 0, roman: 'I.', icon: icons.users, color: 'blue' },
    { id: 'admins', label: 'Admins', value: props.stats?.admins ?? 0, roman: 'II.', icon: icons.admin, color: 'red' },
    { id: 'banned', label: 'Banned', value: props.stats?.banned ?? 0, roman: 'III.', icon: icons.ban, color: 'yellow' },
    { id: 'affiliates', label: 'Affiliates', value: props.stats?.affiliates ?? 0, roman: 'IV.', icon: icons.affiliate, color: 'green' },
]);

const roleColors = {
    admin: 'role--admin',
    user: 'role--user',
    affiliate: 'role--affiliate',
};

const banTypeColors = {
    none: 'status-badge--active',
    soft: 'status-badge--soft',
    hard: 'status-badge--hard',
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
        <template #header>User Management</template>

        <div class="users-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">User Administration</span>
                    <h1 class="page-header__title">All Users</h1>
                    <p class="page-header__sub">Manage user accounts, roles, and permissions.</p>
                </div>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- Stats Grid -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div v-for="item in statItems" :key="item.id" class="stat-card" :class="`stat-card--${item.color}`">
                        <div class="stat-card__top">
                            <span class="stat-card__roman">{{ item.roman }}</span>
                            <div class="stat-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="item.icon" />
                            </div>
                        </div>
                        <div class="stat-card__value">{{ item.value }}</div>
                        <div class="stat-card__label">{{ item.label }}</div>
                    </div>
                </div>
            </section>

            <!-- Filters Section -->
            <section class="filters-section">
                <div class="filters-grid">
                    <div class="filter-field">
                        <span class="filter-field__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.35-4.35" />
                            </svg>
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
                    <Link :href="route('admin.users.create')" class="btn-create">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Create User
                    </Link>
                </div>
            </section>

            <!-- Users Table -->
            <section class="table-section">
                <div class="table-card">
                    <div v-if="!users.data?.length" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.users" />
                        </div>
                        <p class="empty-state__title">No users found</p>
                        <p class="empty-state__text">There are no users matching the current filters.</p>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th class="col-center">Status</th>
                                    <th class="col-center">Created</th>
                                    <th class="col-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id">
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                {{ user.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </div>
                                            <div class="user-info">
                                                <div class="user-name">{{ user.name }}</div>
                                                <div class="user-email">{{ user.email }}</div>
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
                                    <td class="col-center">
                                        <span class="status-badge" :class="banTypeColors[user.ban_type || 'none']">
                                            {{ getBanStatusText(user.ban_type) }}
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <span class="date-cell">{{ formatDate(user.created_at) }}</span>
                                    </td>
                                    <td class="col-center">
                                        <div class="actions-cell">
                                            <Link :href="route('admin.users.show', user.id)"
                                                class="btn-icon btn-icon--view" title="View User">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </Link>
                                            <Link :href="route('admin.users.edit', user.id)"
                                                class="btn-icon btn-icon--edit" title="Edit User">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path
                                                        d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </Link>
                                            <button v-if="user.ban_type !== 'none'" @click="unbanUser(user)"
                                                class="btn-icon btn-icon--unban" title="Unban User">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            </button>
                                            <button v-else @click="showBanModal = true; banningUser = user"
                                                class="btn-icon btn-icon--ban" title="Ban User">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                                </svg>
                                            </button>
                                            <button @click="showDeleteModal = true; deletingUser = user"
                                                class="btn-icon btn-icon--delete" title="Delete User">
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

                        <!-- Pagination -->
                        <div v-if="users.last_page > 1" class="pagination">
                            <template v-for="(link, i) in users.links" :key="i">
                                <span v-if="!link.url" class="pagination__link pagination__link--disabled"
                                    v-html="link.label" />
                                <Link v-else :href="link.url" v-html="link.label" class="pagination__link"
                                    :class="{ 'pagination__link--active': link.active }" />
                            </template>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <!-- Ban Modal -->
        <div v-if="showBanModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Ban User</h3>
                    <button @click="showBanModal = false" class="modal__close">
                        <span class="material-icons">close</span>
                    </button>
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
                    <button @click="showDeleteModal = false" class="modal__close">
                        <span class="material-icons">close</span>
                    </button>
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
    color: var(--primary);
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

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--primary) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Stats Grid ───────────────────────────── */
.stats-section {
    margin-bottom: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    transition: background 0.2s ease;
}

.stat-card--blue {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
}

.stat-card--red {
    background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
}

.stat-card--yellow {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
}

.stat-card--green {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
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
    color: var(--primary);
}

.stat-card__icon {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card--blue .stat-card__icon {
    background: var(--surface-2);
    color: var(--muted);
}

.stat-card--red .stat-card__icon {
    background: #fee2e2;
    color: var(--primary);
}

.stat-card--yellow .stat-card__icon {
    background: var(--surface-2);
    color: var(--muted);
}

.stat-card--green .stat-card__icon {
    background: var(--surface-2);
    color: var(--muted);
}

.stat-card__icon svg {
    width: 14px;
    height: 14px;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 2px;
}

.stat-card__label {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--muted);
}

/* ── Filters Section ─────────────────────── */
.filters-section {
    margin-bottom: 20px;
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr auto auto auto;
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
    display: flex;
    align-items: center;
    justify-content: center;
}

.filter-field__icon svg {
    width: 100%;
    height: 100%;
}

.filter-field__input {
    width: 100%;
    padding: 8px 12px 8px 40px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: all 0.2s;
}

.filter-field__input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 120px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
}

.btn-create {
    background: var(--primary) !important;
    color: white !important;
    border: none !important;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-create:hover {
    background: var(--primary-dark) !important;
}

.btn-icon {
    width: 16px;
    height: 16px;
}

/* ── Table Section ───────────────────────── */
.table-section {
    margin-bottom: 32px;
}

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
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
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-body);
    font-size: 14px;
}

.users-table th {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    padding: 14px 16px;
    text-align: left;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

.users-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.users-table tr:last-child td {
    border-bottom: none;
}

.users-table tr:hover td {
    background: var(--surface-2);
}

.col-center {
    text-align: center;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: var(--surface-2);
    color: var(--ink);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
}

.user-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 13px;
    color: var(--ink);
    margin-bottom: 2px;
}

.user-email {
    font-size: 12px;
    color: var(--muted);
}

/* Role Select */
.role-select {
    appearance: none;
    -webkit-appearance: none;
    font-size: 10px;
    padding: 4px 8px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    font-family: var(--font-display);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 80px;
}

.role-select:focus {
    outline: none;
    border-color: var(--primary);
}

.role-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.role--admin {
    background-color: #fef2f2;
    color: var(--primary);
    border-color: #fecaca;
}

.role--user {
    background-color: var(--surface-2);
    color: var(--ink);
    border-color: var(--border);
}

.role--affiliate {
    background-color: var(--surface-2);
    color: var(--ink);
    border-color: var(--border);
}

/* Status Badge */
.status-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: var(--radius);
    white-space: nowrap;
}

.status-badge--active {
    background: var(--surface-2);
    color: var(--ink);
}

.status-badge--soft {
    background: #fef3c7;
    color: #92400e;
}

.status-badge--hard {
    background: #fee2e2;
    color: var(--primary);
}

.date-cell {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
}

.actions-cell {
    display: flex;
    gap: 6px;
    justify-content: center;
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-icon:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.btn-icon svg {
    width: 14px;
    height: 14px;
}

.btn-icon--view {
    background: var(--surface-2);
    color: var(--ink-soft);
    border-color: var(--border);
}

.btn-icon--view:hover {
    background: var(--border);
    color: var(--ink);
}

.btn-icon--edit {
    background: var(--surface-2);
    color: var(--ink-soft);
    border-color: var(--border);
}

.btn-icon--edit:hover {
    background: var(--border);
    color: var(--ink);
}

.btn-icon--unban {
    background: var(--surface-2);
    color: var(--ink-soft);
    border-color: var(--border);
}

.btn-icon--unban:hover {
    background: var(--border);
    color: var(--ink);
}

.btn-icon--ban {
    background: #fef2f2;
    color: var(--primary);
    border-color: #fecaca;
}

.btn-icon--ban:hover {
    background: #fee2e2;
    color: var(--primary-dark);
}

.btn-icon--delete {
    background: #fef2f2;
    color: var(--primary);
    border-color: #fecaca;
}

.btn-icon--delete:hover {
    background: #fee2e2;
    color: var(--primary-dark);
}

/* ── Pagination ────────────────────────── */
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    padding: 16px;
    border-top: 1px solid var(--border);
    background: var(--surface-2);
}

.pagination__link {
    min-width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}

.pagination__link:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.pagination__link--active {
    background: var(--primary);
    color: var(--surface);
    border-color: var(--primary);
}

.pagination__link--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: var(--surface-2);
}

/* ── Modal Styles ──────────────────────── */
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
}

.modal {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal__header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.modal__close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--muted);
    padding: 4px;
    border-radius: var(--radius);
    transition: all 0.2s;
}

.modal__close:hover {
    color: var(--ink);
    background: var(--surface-2);
}

.modal__body {
    padding: 20px;
}

.modal__footer {
    padding: 20px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.modal__text {
    margin-bottom: 20px;
    font-size: 14px;
    font-family: var(--font-body);
}

.modal__warning {
    color: var(--primary);
    font-size: 13px;
    margin-top: 10px;
}

.field {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.field__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink);
    margin-bottom: 6px;
}

.field__input {
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: all 0.2s;
}

.field__input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.field__input--area {
    resize: vertical;
    min-height: 80px;
}

.btn-ghost {
    background: transparent;
    border: 1px solid var(--border);
    padding: 6px 12px;
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-danger {
    background: var(--primary);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background: var(--primary-dark);
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ── Responsive ────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
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

    .filters-grid {
        grid-template-columns: 1fr;
    }

    .actions-cell {
        flex-wrap: wrap;
    }
}
</style>
