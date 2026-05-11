<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const props = defineProps({
    links: {
        type: Object,
        required: true,
    },
});

const pageNumbers = computed(() => {
    const total = props.links.last_page;
    const current = props.links.current_page;
    const pages = [];
    const delta = 2;

    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }
    return pages;
});

const copiedId = ref(null);
const showDeleteModal = ref(false);
const linkToDelete = ref(null);
const activeFilter = ref(page.props.ziggy?.query?.status || 'all');
const searchQuery = ref(page.props.ziggy?.query?.search || '');

let searchTimeout = null;
watch([activeFilter, searchQuery], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('links.index'), {
            status: activeFilter.value === 'all' ? null : activeFilter.value,
            search: searchQuery.value || null,
        }, { preserveState: true, replace: true });
    }, 300);
});

const copyShortUrl = async (link) => {
    const domain = link.domain ? `https://${link.domain.domain}` : window.location.origin;
    const url = `${domain}/${link.short_code}`;
    await navigator.clipboard.writeText(url);
    copiedId.value = link.short_code;
    setTimeout(() => { copiedId.value = null; }, 2000);
};

const confirmDelete = (link) => {
    linkToDelete.value = link;
    showDeleteModal.value = true;
};

const deleteLink = () => {
    if (linkToDelete.value) {
        router.delete(route('links.destroy', linkToDelete.value.id));
        showDeleteModal.value = false;
        linkToDelete.value = null;
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const icons = {
    external: `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>`,
    chart: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    trash: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>`,
};
</script>

<template>

    <Head title="My Links" />

    <AuthenticatedLayout>
        <template #header><span class="material-icons">link</span> My Links</template>

        <!-- Dashboard Header -->
        <header class="page-header">
            <div class="page-header__left">
                <span class="page-header__marker">Links</span>
                <h1 class="page-header__title">
                    Welcome back<span>, {{ user?.name?.split(' ')[0] ?? 'User' }}</span>
                </h1>
                <p class="page-header__sub">Here's what's happening with your links today.</p>
            </div>
            <Link :href="route('links.create')" class="create-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                New Link
            </Link>
        </header>

        <!-- Filters -->
        <div class="filters">
            <div class="search-box">
                <span class="material-icons">search</span>
                <input v-model="searchQuery" type="text" placeholder="Search links..." class="search-input" />
            </div>
            <div class="filter-tabs">
                <button class="filter-tab" :class="{ active: activeFilter === 'all' }"
                    @click="activeFilter = 'all'">All</button>
                <button class="filter-tab" :class="{ active: activeFilter === 'active' }"
                    @click="activeFilter = 'active'">
                    <span class="material-icons">check_circle</span> Active
                </button>
                <button class="filter-tab" :class="{ active: activeFilter === 'inactive' }"
                    @click="activeFilter = 'inactive'">
                    <span class="material-icons">cancel</span> Inactive
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="links.data?.length === 0" class="empty">
            <span class="material-icons empty-icon">link_off</span>
            <h2 class="empty__title">No links yet</h2>
            <p class="empty__sub">Create your first short link to get started.</p>
            <Link :href="route('links.create')" class="btn-primary">Create Link</Link>
        </div>

        <!-- Links Grid -->
        <div v-else class="links-grid">
            <div v-for="link in links.data" :key="link.id" class="link-card">
                <div class="link-card__header">
                    <a :href="link.domain ? `https://${link.domain.domain}/${link.short_code}` : `/${link.short_code}`"
                        target="_blank" class="short-link">
                        <span v-if="link.domain">{{ link.domain.domain }}/</span>{{ link.short_code }}
                        <span class="material-icons">open_in_new</span>
                    </a>
                    <span class="status-badge" :class="link.is_active ? 'active' : 'inactive'">
                        <span class="material-icons">{{ link.is_active ? 'check_circle' : 'cancel' }}</span>
                        {{ link.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="link-card__dest" :title="link.destination_url">
                    {{ link.destination_url }}
                </div>

                <div class="link-card__footer">
                    <div class="link-card__stats">
                        <span class="stat">
                            <span class="material-icons">touch_app</span>
                            {{ (link.clicks_count ?? 0).toLocaleString() }} clicks
                        </span>
                        <span class="stat">
                            <span class="material-icons">calendar_today</span>
                            {{ formatDate(link.created_at) }}
                        </span>
                    </div>
                    <div class="link-card__actions">
                        <Link :href="route('links.show', link.id)" class="icon-btn icon-btn--chart" title="Analytics">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.chart" />
                        </Link>
                        <Link :href="route('links.edit', link.id)" class="icon-btn" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.edit" />
                        </Link>
                        <button @click="confirmDelete(link)" class="icon-btn icon-btn--danger" title="Delete">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.trash" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="links.last_page > 1" class="pagination">
            <Link v-if="links.prev_page_url" :href="links.prev_page_url" class="page-btn">← Prev</Link>
            <template v-for="page in pageNumbers" :key="page">
                <span v-if="page === '...'" class="page-ellipsis">...</span>
                <Link v-else :href="`?page=${page}`" class="page-btn"
                    :class="{ 'page-btn--active': page === links.current_page }">
                    {{ page }}
                </Link>
            </template>
            <Link v-if="links.next_page_url" :href="links.next_page_url" class="page-btn">Next →</Link>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
            <div class="modal">
                <div class="modal-icon">
                    <span class="material-icons">warning</span>
                </div>
                <h3 class="modal-title">Delete Link</h3>
                <p class="modal-desc">
                    Are you sure you want to delete <strong>/{{ linkToDelete?.short_code }}</strong>?<br>
                    This action cannot be undone.
                </p>
                <div class="modal-actions">
                    <button @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                    <button @click="deleteLink" class="btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600&display=swap');

/* ── Dashboard Header ────────────────────────────── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border);
}

.page-header__left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.page-header__marker {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--red);
}

.page-header__title {
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.page-header__title span {
    color: var(--red);
}

.page-header__sub {
    font-size: 14px;
    color: #666;
    margin: 0;
}

.create-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--red);
    color: #fff;
    border: none;
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: opacity 200ms;
}

.create-btn:hover {
    opacity: 0.9;
}

.create-btn svg {
    width: 16px;
    height: 16px;
}

/* ── Filters ────────────────────────────── */
.filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    flex: 1;
    max-width: 300px;
}

.search-box .material-icons {
    font-size: 18px;
    color: #999;
}

.search-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 14px;
    color: #333;
    outline: none;
    font-family: inherit;
}

.search-input::placeholder {
    color: #999;
}

.filter-tabs {
    display: flex;
    gap: 8px;
}

.filter-tab {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 200ms;
}

.filter-tab:hover {
    background: #f5f5f5;
    color: #333;
}

.filter-tab.active {
    background: var(--red);
    border-color: var(--red);
    color: #fff;
}

.filter-tab .material-icons {
    font-size: 16px;
}

.btn-primary {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    background: var(--red);
    color: #fff;
    border: none;
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-primary:hover {
    opacity: 0.9;
}

.btn-primary .material-icons {
    font-size: 18px;
}

/* ── Empty State ────────────────────────────── */
.empty {
    text-align: center;
    padding: 60px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
}

.empty-icon {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 16px;
}

.empty__title {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0 0 8px 0;
    text-transform: uppercase;
}

.empty__sub {
    font-size: 14px;
    color: #666;
    margin: 0 0 20px 0;
}

/* ── Links Grid ────────────────────────────── */
.links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}

.link-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 16px;
    transition: border-color 200ms;
}

.link-card:hover {
    border-color: #ccc;
}

.link-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.short-link {
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: var(--red);
    text-decoration: none;
}

.short-link:hover {
    text-decoration: underline;
}

.short-link .material-icons {
    font-size: 14px;
    color: #999;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-badge .material-icons {
    font-size: 12px;
}

.status-badge.active {
    background: #dcfce7;
    color: #16a34a;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #dc2626;
}

.link-card__dest {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.link-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.link-card__stats {
    display: flex;
    gap: 16px;
}

.stat {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #888;
}

.stat .material-icons {
    font-size: 14px;
}

.link-card__actions {
    display: flex;
    gap: 8px;
}

.icon-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 4px;
    color: #666;
    cursor: pointer;
    transition: all 200ms;
    text-decoration: none;
}

.icon-btn:hover {
    background: #f5f5f5;
    color: #333;
}

.icon-btn svg {
    width: 14px;
    height: 14px;
}

.icon-btn--danger:hover {
    background: #fee2e2;
    border-color: #dc2626;
    color: #dc2626;
}

.icon-btn--chart:hover {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #3b82f6;
}

/* ── Pagination ────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 32px;
}

.page-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    color: #666;
    text-decoration: none;
    padding: 8px 16px;
    border: 1px solid var(--border);
    border-radius: 4px;
    transition: all 200ms;
}

.page-btn:hover {
    background: #f5f5f5;
    color: #333;
}

.page-btn--active {
    background: var(--red);
    color: #fff;
    border-color: var(--red);
}

.page-ellipsis {
    color: #888;
    padding: 0 4px;
}

.page-info {
    font-size: 13px;
    color: #888;
}

/* ── Editorial Header ────────────────────────────── */
.editorial-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.issue-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.roman-num {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #e74c3c;
    letter-spacing: 2px;
}

.meta-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #888;
}

.page-sub {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    font-style: italic;
    color: #888;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 12px 28px;
    background: #e74c3c;
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 3px;
    text-transform: uppercase;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #c0392b;
}

/* ── Links Table (Dashboard Style) ───────────────── */
.links-table {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    overflow: hidden;
}

.links-table__head {
    display: grid;
    grid-template-columns: 130px 1fr 90px 80px 90px 72px;
    gap: 12px;
    padding: 14px 20px;
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
    background: #fafafa;
    border-bottom: 1px solid #e5e5e5;
}

.col-center {
    text-align: center;
    justify-content: center;
}

.link-row {
    display: grid;
    grid-template-columns: 130px 1fr 90px 80px 90px 72px;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
    transition: background 0.15s ease;
}

.link-row:last-child {
    border-bottom: none;
}

.link-row:hover {
    background: #fdf9f5;
}

.link-row>div {
    min-width: 0;
}

.link-row__short {
    display: flex;
    align-items: center;
}

.short-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #e74c3c;
}

.short-link:hover {
    color: #c0392b;
}

.short-link__slash {
    color: #888;
    margin-right: 2px;
}

.short-link__ext {
    width: 12px;
    height: 12px;
    margin-left: 6px;
    opacity: 0;
    transition: opacity 0.2s;
}

.short-link:hover .short-link__ext {
    opacity: 1;
}

.link-row__dest {
    min-width: 0;
}

.link-row__dest span {
    font-family: 'Crimson Pro', serif;
    font-size: 13px;
    color: #666;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.link-row__clicks {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #1a1a1a;
    text-align: center;
}

.cell-status {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1px;
    padding: 4px 10px;
    border-radius: 4px;
    text-transform: uppercase;
}

.status--active {
    background: #dcfce7;
    color: #16a34a;
}

.status--inactive {
    background: #fee2e2;
    color: #dc2626;
}

.link-row__date {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    color: #888;
    text-align: center;
}

.link-row__actions {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

.icon-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 4px;
    background: #f5f5f5;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.icon-btn svg {
    width: 14px;
    height: 14px;
}

.icon-btn:hover {
    background: #eee;
    color: #1a1a1a;
}

.icon-btn--chart {
    background: #eff6ff;
    color: #3b82f6;
}

.icon-btn--chart:hover {
    background: #dbeafe;
    color: #1d4ed8;
}

.icon-btn--danger:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* ── Empty State ──────────────────────────────────── */
.empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100px 20px;
    text-align: center;
    gap: 16px;
    border: 1px solid #ddd;
    background: #fff;
}

.empty__roman {
    font-family: 'Oswald', sans-serif;
    font-size: 48px;
    font-weight: 700;
    color: #e74c3c;
    letter-spacing: 4px;
}

.empty__title {
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 500;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #1a1a1a;
}

.empty__sub {
    font-family: 'Crimson Pro', serif;
    font-size: 16px;
    font-style: italic;
    color: #888;
    margin-bottom: 8px;
}

/* ── Pagination ──────────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e5e5;
}

.page-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #1a1a1a;
    text-decoration: none;
    padding: 10px 20px;
    border: 1px solid #1a1a1a;
    background: #fff;
    transition: all 0.2s;
    cursor: pointer;
}

.page-btn:hover {
    background: #1a1a1a;
    color: #fff;
}

.page-info {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    color: #1a1a1a;
    letter-spacing: 1px;
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 900px) {
    .editorial-header {
        flex-wrap: wrap;
        gap: 16px;
    }

    .links-table__head,
    .link-row {
        grid-template-columns: 120px 1fr 80px 80px;
    }

    .link-row__date,
    .links-table__head span:nth-child(5) {
        display: none;
    }
}

@media (max-width: 640px) {
    .links-table__head {
        display: none;
    }

    .link-row {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 16px;
    }

    .link-row__clicks,
    .link-row__date,
    .links-table__head span:nth-child(3),
    .links-table__head span:nth-child(4) {
        display: none;
    }

    .link-row__short {
        grid-column: 1 / -1;
    }

    .link-row__dest {
        grid-column: 1 / -1;
    }

    .link-row__actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
        padding-top: 8px;
        border-top: 1px solid #eee;
    }
}

/* Delete Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: #fff;
    border-radius: 4px;
    padding: 32px;
    width: 100%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-icon {
    width: 56px;
    height: 56px;
    margin: 0 auto 16px;
    background: #fee2e2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-icon .material-icons {
    font-size: 28px;
    color: #dc2626;
}

.modal-title {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modal-desc {
    font-size: 14px;
    color: #666;
    margin: 0 0 24px 0;
    line-height: 1.6;
}

.modal-desc strong {
    color: #1a1a1a;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn-secondary {
    padding: 10px 24px;
    background: #fff;
    color: #666;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 200ms;
}

.btn-secondary:hover {
    background: #f5f5f5;
    color: #333;
}

.btn-danger {
    padding: 10px 24px;
    background: #dc2626;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-danger:hover {
    opacity: 0.9;
}
</style>
