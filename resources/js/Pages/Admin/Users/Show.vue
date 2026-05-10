<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
    stats: Object,
});

const icons = {
    arrow: `<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    link: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    chart: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    external: `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>`,
    mail: `<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>`,
    calendar: `<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>`,
    power: `<path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/>`,
    trash: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>`,
};

const statItems = [
    { id: 'links', label: 'Total Links', value: props.stats?.totalLinks ?? 0, roman: 'I.', icon: icons.link },
    { id: 'clicks', label: 'Total Clicks', value: props.stats?.totalClicks ?? 0, roman: 'II.', icon: icons.chart },
    { id: 'active', label: 'Active Links', value: props.stats?.activeLinks ?? 0, roman: 'III.', icon: icons.link },
];

const getBanStatusText = (banType) => {
    if (banType === 'soft') return 'Soft Banned';
    if (banType === 'hard') return 'Hard Banned';
    return 'Active';
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Delete modal state
const showDeleteModal = ref(false);
const linkToDelete = ref(null);

const toggleLink = (link) => {
    router.patch(route('links.toggle', link.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openDeleteModal = (link) => {
    linkToDelete.value = link;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    linkToDelete.value = null;
};

const confirmDelete = () => {
    if (linkToDelete.value) {
        router.delete(route('links.destroy', linkToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                closeDeleteModal();
            },
        });
    }
};
</script>

<template>

    <Head :title="`User: ${user.name}`" />

    <AdminLayout>
        <template #header-icon>
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </template>
        <template #header>User Profile</template>

        <div class="show-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">User Details</span>
                    <h1 class="page-header__title">{{ user.name }}</h1>
                    <p class="page-header__sub">View user profile and link activity.</p>
                </div>
                <div class="page-header__actions">
                    <Link :href="route('admin.users.analytics', user.id)" class="btn-secondary">
                        View Analytics
                    </Link>
                    <Link :href="route('admin.users.index')" class="back-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            v-html="icons.arrow" />
                        Back
                    </Link>
                    <Link :href="route('admin.users.edit', user.id)" class="edit-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            v-html="icons.edit" />
                        Edit User
                    </Link>
                </div>
            </header>

            <!-- Divider -->
            <div class="section-rule"></div>

            <!-- User Info Card -->
            <section class="info-section">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-card__header">
                            <div class="user-avatar-large">{{ user.name.charAt(0).toUpperCase() }}</div>
                            <div class="user-meta">
                                <h2 class="user-meta__name">{{ user.name }}</h2>
                                <span class="user-meta__email">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        v-html="icons.mail" />
                                    {{ user.email }}
                                </span>
                            </div>
                        </div>
                        <div class="info-card__body">
                            <div class="info-row">
                                <span class="info-row__label">Role</span>
                                <span class="role-badge" :class="`role--${user.role}`">
                                    {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-row__label">Status</span>
                                <span class="status-badge"
                                    :class="user.ban_type === 'none' ? 'status--active' : (user.ban_type === 'soft' ? 'status--soft' : 'status--hard')">
                                    {{ getBanStatusText(user.ban_type) }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-row__label">Joined</span>
                                <span class="info-row__value">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        v-html="icons.calendar" />
                                    {{ formatDate(user.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="stats-card">
                        <div v-for="item in statItems" :key="item.id" class="stat-item">
                            <div class="stat-item__top">
                                <span class="stat-item__roman">{{ item.roman }}</span>
                                <div class="stat-item__icon" :class="`stat-item__icon--${item.id}`">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        v-html="item.icon" />
                                </div>
                            </div>
                            <span class="stat-item__value">{{ typeof item.value === 'number' ?
                                item.value.toLocaleString() :
                                item.value }}</span>
                            <span class="stat-item__label">{{ item.label }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Links Section -->
            <section class="links-section">
                <div class="section-header">
                    <div>
                        <span class="section-marker">User Links</span>
                        <p class="section-sub">{{ user.links.total }} total links (showing {{ user.links.per_page }} per
                            page)
                        </p>
                    </div>
                </div>

                <div v-if="user.links.data.length === 0" class="empty-state">
                    <div class="empty-state__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.link" />
                    </div>
                    <p class="empty-state__title">No links yet</p>
                    <p class="empty-state__text">This user hasn't created any short links.</p>
                </div>

                <div v-else class="table-wrapper">
                    <table class="links-table">
                        <thead>
                            <tr>
                                <th>Short URL</th>
                                <th>Destination</th>
                                <th class="col-center">Clicks</th>
                                <th class="col-center">Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="link in user.links.data" :key="link.id">
                                <td>
                                    <a :href="`/${link.short_code}`" target="_blank" class="short-link">
                                        <span class="short-link__slash">/</span>{{ link.short_code }}
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            class="short-link__ext" v-html="icons.external" />
                                    </a>
                                </td>
                                <td>
                                    <span class="dest-cell" :title="link.destination_url">{{ link.destination_url
                                    }}</span>
                                </td>
                                <td class="col-center">
                                    <span class="clicks-cell">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                            v-html="icons.chart" />
                                        {{ link.clicks_count?.toLocaleString() ?? 0 }}
                                    </span>
                                </td>
                                <td class="col-center">
                                    <span class="link-status"
                                        :class="link.is_active ? 'link-status--active' : 'link-status--inactive'">
                                        {{ link.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="link-actions">
                                        <a :href="`/${link.short_code}`" target="_blank" class="btn-icon btn-icon--view"
                                            title="Visit Link">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.external" />
                                        </a>
                                        <button @click="toggleLink(link)" class="btn-icon"
                                            :class="link.is_active ? 'btn-icon--disable' : 'btn-icon--enable'"
                                            :title="link.is_active ? 'Deactivate Link' : 'Activate Link'">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.power" />
                                        </button>
                                        <Link :href="route('admin.links.edit', link.id)" class="btn-icon btn-icon--edit"
                                            title="Edit Link">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.edit" />
                                        </Link>
                                        <button @click="openDeleteModal(link)" class="btn-icon btn-icon--delete"
                                            title="Delete Link">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.trash" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Links Pagination -->
                    <div v-if="user.links.last_page > 1" class="pagination">
                        <template v-for="(link, i) in user.links.links" :key="i">
                            <span v-if="!link.url" class="pagination__link pagination__link--disabled"
                                v-html="link.label" />
                            <Link v-else :href="link.url" v-html="link.label" class="pagination__link"
                                :class="{ 'pagination__link--active': link.active }" />
                        </template>
                    </div>
                </div>
            </section>

        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
                <div class="modal" @click.stop>
                    <div class="modal__header">
                        <div class="modal__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6" />
                                <path
                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                        </div>
                        <h3 class="modal__title">Delete Link</h3>
                    </div>
                    <div class="modal__body">
                        <p class="modal__text">
                            Are you sure you want to delete the link
                            <code class="modal__code" v-if="linkToDelete">/{{ linkToDelete.short_code }}</code>?
                        </p>
                        <p class="modal__warning">This action cannot be undone.</p>
                    </div>
                    <div class="modal__actions">
                        <button @click="closeDeleteModal" class="modal__btn modal__btn--ghost">Cancel</button>
                        <button @click="confirmDelete" class="modal__btn modal__btn--danger">Delete Link</button>
                    </div>
                </div>
            </div>
        </Teleport>

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

.page-header__left {
    flex: 1;
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

.page-header__actions {
    display: flex;
    gap: 12px;
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

.edit-btn {
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
}

.edit-btn:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.edit-btn svg {
    width: 14px;
    height: 14px;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    text-decoration: none;
    font-size: 13px;
    transition: var(--transition);
}

.btn-secondary:hover {
    background: var(--surface-2);
}

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

/* ── Info Section ───────────────────────── */
.info-section {
    margin-bottom: 32px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.info-card__header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
}

.user-avatar-large {
    width: 56px;
    height: 56px;
    background: var(--red);
    color: var(--surface);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 22px;
    flex-shrink: 0;
}

.user-meta__name {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 6px;
}

.user-meta__email {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--muted);
}

.user-meta__email svg {
    width: 14px;
    height: 14px;
}

.info-card__body {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.info-row__label {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
}

.info-row__value {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-display);
    font-size: 13px;
    color: var(--ink);
}

.info-row__value svg {
    width: 14px;
    height: 14px;
    color: var(--muted);
}

.role-badge {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 6px 12px;
    border-radius: var(--radius);
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

.status-badge {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 6px 12px;
    border-radius: var(--radius);
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

/* ── Stats Card ────────────────────────── */
.stats-card {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.stat-item {
    background: var(--surface);
    padding: 20px;
    display: flex;
    flex-direction: column;
    transition: background 0.2s ease;
}

.stat-item:nth-child(1) {
    background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
}

.stat-item:nth-child(2) {
    background: linear-gradient(135deg, #eff6ff 0%, #fff 100%);
}

.stat-item:nth-child(3) {
    background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);
}

.stat-item__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.stat-item__roman {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 700;
    color: var(--red);
}

.stat-item__icon {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-item__icon svg {
    width: 14px;
    height: 14px;
}

.stat-item__icon--links {
    background: #fef2f2;
    color: var(--red);
}

.stat-item__icon--clicks {
    background: #eff6ff;
    color: #3b82f6;
}

.stat-item__icon--active {
    background: #f0fdf4;
    color: #16a34a;
}

.stat-item__value {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-item__label {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 500;
    text-transform: uppercase;
    color: var(--muted);
}

/* ── Links Section ─────────────────────── */
.links-section {
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

/* ── Empty State ───────────────────────── */
.empty-state {
    background: var(--surface);
    border: 1px dashed var(--border);
    border-radius: var(--radius);
    padding: 48px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-state__icon {
    width: 48px;
    height: 48px;
    background: var(--surface-2);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    color: var(--muted);
}

.empty-state__icon svg {
    width: 22px;
    height: 22px;
}

.empty-state__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0 0 6px;
}

.empty-state__text {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

/* ── Table ─────────────────────────────── */
.table-wrapper {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.links-table {
    width: 100%;
    border-collapse: collapse;
}

.links-table th {
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

.links-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}

.links-table tr:last-child td {
    border-bottom: none;
}

.col-center {
    text-align: center;
}

.short-link {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--red);
    text-decoration: none;
    transition: color 0.15s;
}

.short-link__slash {
    color: var(--border);
    font-weight: 400;
}

.short-link__ext {
    width: 12px;
    height: 12px;
    opacity: 0;
    margin-left: 4px;
    transition: opacity 0.15s;
}

.short-link:hover {
    color: var(--red-dark);
}

.short-link:hover .short-link__ext {
    opacity: 1;
}

.dest-cell {
    font-size: 13px;
    color: var(--muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
    max-width: 300px;
}

.clicks-cell {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
}

.clicks-cell svg {
    width: 14px;
    height: 14px;
    color: #16a34a;
}

.link-status {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: var(--radius);
}

.link-status--active {
    background: #f0fdf4;
    color: #16a34a;
}

.link-status--inactive {
    background: #f1f5f9;
    color: var(--muted);
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--muted);
    text-decoration: none;
    transition: var(--transition);
}

.btn-icon:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.btn-icon svg {
    width: 14px;
    height: 14px;
}

/* ── Link Actions ─────────────────────── */
.link-actions {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

.btn-icon--view {
    background: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
}

.btn-icon--view:hover {
    background: #dbeafe;
    color: #2563eb;
}

.btn-icon--enable {
    background: #f0fdf4;
    color: #16a34a;
    border-color: #86efac;
}

.btn-icon--enable:hover {
    background: #dcfce7;
    color: #15803d;
}

.btn-icon--disable {
    background: #fef9c3;
    color: #ca8a04;
    border-color: #fde047;
}

.btn-icon--disable:hover {
    background: #fef08a;
    color: #a16207;
}

.btn-icon--edit {
    background: #fef9f0;
    color: var(--gold);
    border-color: #fde68a;
}

.btn-icon--edit:hover {
    background: #fef3c7;
    color: #d97706;
}

.btn-icon--delete {
    background: #fef2f2;
    color: var(--red);
    border-color: #fecaca;
}

.btn-icon--delete:hover {
    background: #fee2e2;
    color: var(--red-dark);
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
    background: var(--surface);
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

/* ── Responsive ────────────────────────── */
@media (max-width: 1024px) {
    .info-grid {
        grid-template-columns: 1fr;
    }

    .stats-card {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .page-header__actions {
        width: 100%;
    }

    .stats-card {
        grid-template-columns: 1fr;
    }

    .dest-cell {
        max-width: 150px;
    }
}

/* ── Delete Modal ──────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    animation: modalEnter 0.2s ease;
}

@keyframes modalEnter {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal__header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 24px 24px 0;
}

.modal__icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fef2f2;
    border-radius: var(--radius);
    color: var(--red);
}

.modal__icon svg {
    width: 20px;
    height: 20px;
}

.modal__title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.modal__body {
    padding: 16px 24px 24px;
}

.modal__text {
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--ink-soft);
    margin: 0 0 8px;
    line-height: 1.5;
}

.modal__code {
    font-family: monospace;
    font-size: 13px;
    background: var(--surface-2);
    padding: 2px 6px;
    border-radius: 3px;
    color: var(--ink);
}

.modal__warning {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--red);
    margin: 0;
}

.modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background: var(--surface-2);
    border-top: 1px solid var(--border);
}

.modal__btn {
    padding: 10px 20px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition);
    border: 1px solid transparent;
}

.modal__btn--ghost {
    background: var(--surface);
    border-color: var(--border);
    color: var(--ink);
}

.modal__btn--ghost:hover {
    background: var(--border);
}

.modal__btn--danger {
    background: var(--red);
    border-color: var(--red);
    color: var(--surface);
}

.modal__btn--danger:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}
</style>
