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
    users: 'people',
    search: 'search',
    eye: 'visibility',
    ban: 'block',
    unban: 'check_circle',
    trash: 'delete',
    edit: 'edit',
    plus: 'add',
    admin: 'admin_panel_settings',
    user: 'person',
    affiliate: 'handshake',
};

const statCards = computed(() => [
    { label: 'Total Users', value: props.stats?.total ?? 0 },
    { label: 'Admins', value: props.stats?.admins ?? 0 },
    { label: 'Banned', value: props.stats?.banned ?? 0 },
    { label: 'Affiliates', value: props.stats?.affiliates ?? 0 },
]);

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
        <template #header><span class="material-icons">{{ icons.users }}</span> User Management</template>

        <div class="page-content">

            <!-- Stats Row -->
            <div class="stats-row">
                <div v-for="stat in statCards" :key="stat.label" class="stat-box">
                    <span class="stat-value">{{ stat.value.toLocaleString() }}</span>
                    <span class="stat-label">{{ stat.label }}</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-field">
                        <span class="filter-field__icon">
                            <span class="material-icons">{{ icons.search }}</span>
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
                        <span class="material-icons btn-icon">{{ icons.plus }}</span>Create User
                    </Link>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">

                <!-- Users List -->
                <div class="chart-card chart-card--full">
                    <div class="chart-header">
                        <span class="header-icon"><span class="material-icons">{{ icons.users }}</span></span>
                        <h3>User Directory</h3>
                        <div class="section-info">{{ users.total }} total users</div>
                    </div>
                    <div class="chart-body">
                        <div v-if="!users.data?.length" class="no-data">No users found</div>
                        <div v-else class="data-list">
                            <div v-for="user in users.data" :key="user.id" class="data-row">
                                <div class="data-label">
                                    <div class="user-info">
                                        <span class="user-name">{{ user.name }}</span>
                                        <span class="user-email">{{ user.email }}</span>
                                    </div>
                                    <span class="data-format">{{ formatDate(user.created_at) }}</span>
                                </div>
                                <div class="data-value">
                                    <select v-model="user.role" @change="updateRole(user, $event.target.value)"
                                        :disabled="user.id === $page.props.auth.user.id" class="role-select"
                                        :class="roleColors[user.role]">
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                        <option value="affiliate">Affiliate</option>
                                    </select>
                                    <span class="status-badge" :class="banTypeColors[user.ban_type || 'none']">
                                        {{ getBanStatusText(user.ban_type) }}
                                    </span>
                                    <div class="actions-cell">
                                        <Link :href="route('admin.users.show', user.id)" class="btn-action">
                                            <span class="material-icons">{{ icons.eye }}</span>
                                        </Link>
                                        <Link :href="route('admin.users.edit', user.id)" class="btn-action">
                                            <span class="material-icons">{{ icons.edit }}</span>
                                        </Link>
                                        <button v-if="user.ban_type !== 'none'" @click="unbanUser(user)"
                                            class="btn-action">
                                            <span class="material-icons">{{ icons.unban }}</span>
                                        </button>
                                        <button v-else @click="showBanModal = true; banningUser = user"
                                            class="btn-action btn-danger">
                                            <span class="material-icons">{{ icons.ban }}</span>
                                        </button>
                                        <button @click="showDeleteModal = true; deletingUser = user"
                                            class="btn-action btn-danger">
                                            <span class="material-icons">{{ icons.trash }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="pagination">
                <template v-for="(link, i) in users.links" :key="i">
                    <span v-if="!link.url" class="pagination__link pagination__link--disabled" v-html="link.label" />
                    <Link v-else :href="link.url" v-html="link.label" class="pagination__link"
                        :class="{ 'pagination__link--active': link.active }" />
                </template>
            </div>
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
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

:root {
    --font-display: 'Oswald', sans-serif;
    --red: #e74c3c;
    --ink: #000000;
    --muted: #333333;
    --border: #e5e5e5;
    --surface: #fff;
    --radius: 4px;
}

.page-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 24px;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    text-align: center;
}

.stat-value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 600;
    color: var(--ink);
    display: block;
    margin-bottom: 4px;
}

.stat-label {
    font-family: var(--font-display);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
}

/* Filters Section */
.filters-section {
    margin-bottom: 24px;
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

.filter-field__input {
    width: 100%;
    padding: 8px 12px 8px 40px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    transition: all 0.2s;
}

.filter-field__input:focus {
    outline: none;
    border-color: var(--red);
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
    border-color: var(--red);
}

.btn-create {
    background: var(--red);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-create:hover {
    background: #c0392b;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.chart-card--full {
    grid-column: 1 / -1;
}

.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.chart-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-icon {
    display: flex;
    align-items: center;
    color: var(--red);
}

.chart-header h3 {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--ink);
    margin: 0;
    flex: 1;
}

.section-info {
    font-family: var(--font-display);
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
}

.chart-body {
    padding: 20px;
    min-height: 180px;
}

.no-data {
    text-align: center;
    color: var(--muted);
    font-size: 14px;
    padding: 40px 0;
}

/* Data List */
.data-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.data-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.data-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.data-label {
    font-size: 12px;
    color: var(--ink);
    font-family: 'DM Sans', sans-serif;
    display: flex;
    flex-direction: column;
    gap: 4px;
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
    font-size: 11px;
    color: var(--muted);
}

.data-format {
    font-size: 10px;
    color: var(--muted);
    font-family: var(--font-display);
    text-transform: uppercase;
}

.data-value {
    display: flex;
    align-items: center;
    gap: 8px;
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
    border-color: var(--red);
}

.role-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.role--admin {
    background-color: #fef2f2;
    color: var(--red);
    border-color: #fecaca;
}

.role--user {
    background-color: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
}

.role--affiliate {
    background-color: #f0fdf4;
    color: #16a34a;
    border-color: #bbf7d0;
}

/* Status Badge */
.status-badge {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 2px;
    letter-spacing: 0.5px;
}

.status--active {
    background: #d4edda;
    color: #155724;
}

.status--soft {
    background: #fff3cd;
    color: #856404;
}

.status--hard {
    background: #f8d7da;
    color: #721c24;
}

/* Actions */
.actions-cell {
    display: flex;
    gap: 4px;
}

.btn-action {
    background: transparent;
    border: none;
    padding: 4px;
    border-radius: var(--radius);
    font-size: 10px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
}

.btn-action:hover {
    background: transparent;
    opacity: 0.7;
}

.btn-danger {
    color: var(--red);
}

.btn-danger:hover {
    color: #c0392b;
}

/* Material Icons */
.material-icons {
    font-family: 'Material Icons';
    font-weight: normal;
    font-style: normal;
    font-size: 18px;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    -webkit-font-feature-settings: 'liga';
    -webkit-font-smoothing: antialiased;
}

.btn-action .material-icons {
    font-size: 16px;
}

.btn-icon {
    font-size: 16px;
    margin-right: 6px;
    vertical-align: middle;
}

/* Modal Styles */
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
}

.modal__warning {
    color: var(--red);
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    transition: all 0.2s;
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
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
    background: var(--red);
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
    background: #c0392b;
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-top: 24px;
}

.pagination__link {
    padding: 6px 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination__link:hover {
    background: var(--surface-2);
}

.pagination__link--active {
    background: var(--red);
    color: white;
    border-color: var(--red);
}

.pagination__link--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: var(--surface-2);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }

    .page-content {
        padding: 16px;
    }
}
</style>
