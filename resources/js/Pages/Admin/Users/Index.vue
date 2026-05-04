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

const banTypeColors = {
    none: 'bg-green-100 text-green-800',
    soft: 'bg-yellow-100 text-yellow-800',
    hard: 'bg-red-100 text-red-800',
};

const roleColors = {
    admin: 'bg-purple-100 text-purple-800',
    user: 'bg-blue-100 text-blue-800',
    affiliate: 'bg-green-100 text-green-800',
};
</script>

<template>

    <Head title="User Management" />
    <AdminLayout>
        <div class="admin-page">
            <h1 class="admin-page__title">User Management</h1>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats.total }}</span>
                    <span class="stat-card__label">Total Users</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats.admins }}</span>
                    <span class="stat-card__label">Admins</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats.banned }}</span>
                    <span class="stat-card__label">Banned Users</span>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="admin-section">
                <div class="filters-row">
                    <input v-model="search" type="text" placeholder="Search users..." class="field__input" />
                    <select v-model="roleFilter" class="field__input">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="affiliate">Affiliate</option>
                    </select>
                    <select v-model="banFilter" class="field__input">
                        <option value="">All Status</option>
                        <option value="none">Active</option>
                        <option value="soft">Soft Banned</option>
                        <option value="hard">Hard Banned</option>
                    </select>
                </div>

                <!-- Users Table -->
                <div class="table-wrapper">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">
                                            {{ user.name.charAt(0).toUpperCase() }}
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
                                <td>
                                    <span class="status-badge" :class="banTypeColors[user.ban_type || 'none']">
                                        {{ getBanStatusText(user.ban_type) }}
                                    </span>
                                </td>
                                <td class="date-cell">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                <td>
                                    <div class="actions-cell">
                                        <button v-if="user.ban_type" @click="unbanUser(user)"
                                            class="btn-ghost-sm">Unban</button>
                                        <button v-else @click="showBanModal = true; banningUser = user"
                                            class="btn-ghost-sm">Ban</button>
                                        <button @click="showDeleteModal = true; deletingUser = user"
                                            class="btn-danger-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links?.length > 3" class="pagination">
                    <Link v-for="link in users.links" :key="link.label" :href="link.url" class="pagination__link"
                        :class="{ 'pagination__link--active': link.active }" v-html="link.label" />
                </div>
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
                            <p class="mb-4">Ban <strong>{{ banningUser?.name }}</strong>?</p>

                            <div class="field">
                                <label class="field__label">Ban Type</label>
                                <select v-model="banForm.ban_type" class="field__input" required>
                                    <option value="soft">Soft Ban (can still login)</option>
                                    <option value="hard">Hard Ban (cannot login)</option>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Reason</label>
                                <textarea v-model="banForm.ban_reason" class="field__input" rows="3"
                                    placeholder="Reason for banning..." required></textarea>
                            </div>

                            <div class="field" v-if="banForm.ban_type === 'soft'">
                                <label class="field__label">Duration (days)</label>
                                <input v-model.number="banForm.ban_duration_days" type="number" min="1" max="365"
                                    class="field__input" />
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" @click="showBanModal = false" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-danger" :disabled="banForm.processing">
                                Ban User
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
                        <p>Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong>?</p>
                        <p class="text-red-600 text-sm mt-2">This action cannot be undone.</p>
                    </div>
                    <div class="modal__footer">
                        <button @click="showDeleteModal = false" class="btn-ghost">Cancel</button>
                        <button @click="deleteUser" class="btn-danger">Delete Account</button>
                    </div>
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

.admin-page {
    max-width: 1200px;
}

.admin-page__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--ink);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-page__title::before {
    content: 'ADMIN';
    font-size: 10px;
    color: var(--red);
    letter-spacing: 1px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    text-align: center;
}

.stat-card__value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 4px;
}

.stat-card__label {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
}

.admin-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.filters-row {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.field__input {
    padding: 8px 12px;
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
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.table-wrapper {
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid var(--border);
}

.users-table td {
    padding: 16px 12px;
    border-bottom: 1px solid var(--border);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: var(--red);
    color: var(--surface);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 14px;
}

.user-info {
    flex: 1;
}

.user-name {
    font-family: var(--font-display);
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 2px;
}

.user-email {
    font-size: 12px;
    color: var(--muted);
}

.role-select {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    font-weight: 600;
}

.status-badge {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: var(--radius);
    font-weight: 600;
}

.date-cell {
    font-size: 13px;
    color: var(--ink-soft);
}

.actions-cell {
    display: flex;
    gap: 8px;
}

.btn-ghost-sm {
    padding: 4px 8px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost-sm:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.btn-danger-sm {
    padding: 4px 8px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-danger-sm:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

/* Modal Styles */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 26, 0.5);
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

.modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid var(--border);
}

.field {
    margin-bottom: 16px;
}

.field__label {
    display: block;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 6px;
}

.btn-ghost {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-danger {
    padding: 8px 16px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-danger:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-top: 24px;
}

.pagination__link {
    padding: 6px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-size: 12px;
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
</style>
