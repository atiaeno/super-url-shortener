<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    links: Object,
    stats: Object,
});

const icons = {
    link: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    chart: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    power: `<path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    trash: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>`,
    external: `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>`,
    user: `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>`,
};

const statItems = [
    { id: 'total', label: 'Total Links', value: props.stats.total, roman: 'I.', icon: icons.link, color: 'red' },
    { id: 'active', label: 'Active', value: props.stats.active, roman: 'II.', icon: icons.power, color: 'green' },
    { id: 'inactive', label: 'Inactive', value: props.stats.inactive, roman: 'III.', icon: icons.power, color: 'yellow' },
    { id: 'clicks', label: 'Total Clicks', value: props.stats.totalClicks.toLocaleString(), roman: 'IV.', icon: icons.chart, color: 'blue' },
];

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

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>

    <Head title="All Links" />

    <AdminLayout>
        <template #header-icon>
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
        </template>
        <template #header>All Links</template>

        <div class="links-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Link Management</span>
                    <h1 class="page-header__title">All Short Links</h1>
                    <p class="page-header__sub">Manage all links across the platform.</p>
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

            <!-- Links Table -->
            <section class="table-section">
                <div class="table-card">
                    <div v-if="links.data.length === 0" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.link" />
                        </div>
                        <p class="empty-state__title">No links yet</p>
                        <p class="empty-state__text">There are no short links in the system.</p>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="links-table">
                            <thead>
                                <tr>
                                    <th>Short URL</th>
                                    <th>Destination</th>
                                    <th>User</th>
                                    <th class="col-center">Clicks</th>
                                    <th class="col-center">Status</th>
                                    <th class="col-center">Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="link in links.data" :key="link.id">
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
                                    <td>
                                        <Link v-if="link.user" :href="route('admin.users.show', link.user.id)"
                                            class="user-link">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" v-html="icons.user" />
                                            {{ link.user.name }}
                                        </Link>
                                        <span v-else class="user-link user-link--guest">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" v-html="icons.user" />
                                            Guest
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <span class="clicks-cell">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" v-html="icons.chart" />
                                            {{ link.clicks_count?.toLocaleString() ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <span class="link-status"
                                            :class="link.is_active ? 'link-status--active' : 'link-status--inactive'">
                                            {{ link.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="col-center">
                                        <span class="date-cell">{{ formatDate(link.created_at) }}</span>
                                    </td>
                                    <td>
                                        <div class="link-actions">
                                            <a :href="`/${link.short_code}`" target="_blank"
                                                class="btn-icon btn-icon--view" title="Visit Link">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" v-html="icons.external" />
                                            </a>
                                            <button @click="toggleLink(link)" class="btn-icon"
                                                :class="link.is_active ? 'btn-icon--disable' : 'btn-icon--enable'"
                                                :title="link.is_active ? 'Deactivate' : 'Activate'">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" v-html="icons.power" />
                                            </button>
                                            <Link :href="route('admin.links.edit', link.id)"
                                                class="btn-icon btn-icon--edit" title="Edit Link">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" v-html="icons.edit" />
                                            </Link>
                                            <button @click="openDeleteModal(link)" class="btn-icon btn-icon--delete"
                                                title="Delete Link">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" v-html="icons.trash" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="links.last_page > 1" class="pagination">
                            <template v-for="(link, i) in links.links" :key="i">
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

.stat-card--red {
    background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
}

.stat-card--green {
    background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);
}

.stat-card--yellow {
    background: linear-gradient(135deg, #fef9c3 0%, #fff 100%);
}

.stat-card--blue {
    background: linear-gradient(135deg, #eff6ff 0%, #fff 100%);
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

.stat-card__icon {
    width: 28px;
    height: 28px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card--red .stat-card__icon {
    background: #fef2f2;
    color: var(--red);
}

.stat-card--green .stat-card__icon {
    background: #dcfce7;
    color: #16a34a;
}

.stat-card--yellow .stat-card__icon {
    background: #fef08a;
    color: #ca8a04;
}

.stat-card--blue .stat-card__icon {
    background: #dbeafe;
    color: #3b82f6;
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

.links-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-body);
    font-size: 14px;
}

.links-table th {
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

.links-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.links-table tr:last-child td {
    border-bottom: none;
}

.links-table tr:hover td {
    background: var(--surface-2);
}

.col-center {
    text-align: center;
}

.short-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 500;
    color: var(--red);
    text-decoration: none;
}

.short-link:hover {
    color: var(--red-dark);
}

.short-link__slash {
    color: var(--muted);
}

.short-link__ext {
    width: 12px;
    height: 12px;
    margin-left: 2px;
    opacity: 0.6;
}

.dest-cell {
    display: block;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--ink-soft);
    font-size: 13px;
}

.user-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    color: var(--ink);
    text-decoration: none;
    padding: 6px 10px;
    background: var(--surface-2);
    border-radius: var(--radius);
    transition: var(--transition);
}

.user-link:hover {
    background: var(--border);
    color: var(--red);
}

.user-link svg {
    width: 14px;
    height: 14px;
}

.user-link--guest {
    background: #f3f4f6;
    color: var(--muted);
    cursor: default;
}

.clicks-cell {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
}

.clicks-cell svg {
    width: 14px;
    height: 14px;
    color: var(--gold);
}

.link-status {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: var(--radius);
    white-space: nowrap;
}

.link-status--active {
    background: #f0fdf4;
    color: #16a34a;
}

.link-status--inactive {
    background: #fef2f2;
    color: var(--red);
}

.date-cell {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
}

.link-actions {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
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
    background: var(--red);
    color: var(--surface);
    border-color: var(--red);
}

.pagination__link--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: var(--surface-2);
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

    .dest-cell {
        max-width: 150px;
    }

    .link-actions {
        flex-wrap: wrap;
    }
}
</style>
