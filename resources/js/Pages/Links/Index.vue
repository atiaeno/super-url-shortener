<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    links: {
        type: Object,
        required: true,
    },
});

const copiedId = ref(null);

const copyShortUrl = async (shortCode) => {
    const url = `${window.location.origin}/${shortCode}`;
    await navigator.clipboard.writeText(url);
    copiedId.value = shortCode;
    setTimeout(() => { copiedId.value = null; }, 2000);
};

const deleteLink = (id) => {
    if (!confirm('Delete this link? This cannot be undone.')) return;
    router.delete(route('links.destroy', id));
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

    <Head title="My Links — Editorial" />

    <AuthenticatedLayout>
        <template #header>My Links</template>

        <!-- Editorial Header -->
        <div class="editorial-header">
            <div class="issue-meta">
                <span class="roman-num">I.</span>
                <span class="meta-label">Archive</span>
            </div>
            <p class="page-sub">{{ links.total ?? 0 }} entries catalogued</p>
            <Link :href="route('links.create')" class="btn-primary">
                New Entry
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="links.data?.length === 0" class="empty">
            <div class="empty__roman">I.</div>
            <h2 class="empty__title">No entries yet</h2>
            <p class="empty__sub">Begin your collection by creating the first short link.</p>
            <Link :href="route('links.create')" class="btn-primary">Create Entry</Link>
        </div>

        <!-- Links Table -->
        <div v-else class="links-table">
            <div class="links-table__head">
                <span>Short URL</span>
                <span>Destination</span>
                <span class="col-center">Clicks</span>
                <span class="col-center">Status</span>
                <span class="col-center">Created</span>
                <span></span>
            </div>

            <div v-for="link in links.data" :key="link.id" class="link-row">
                <!-- Short URL -->
                <div class="link-row__short">
                    <a :href="`/${link.short_code}`" target="_blank" class="short-link">
                        <span class="short-link__slash">/</span>{{ link.short_code }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            class="short-link__ext" v-html="icons.external" />
                    </a>
                </div>

                <!-- Destination -->
                <div class="link-row__dest">
                    <span :title="link.destination_url">{{ link.destination_url }}</span>
                </div>

                <!-- Clicks -->
                <div class="link-row__clicks col-center">
                    {{ (link.clicks_count ?? 0).toLocaleString() }}
                </div>

                <!-- Status -->
                <span class="col-center cell-status" :class="link.is_active ? 'status--active' : 'status--inactive'">
                    {{ link.is_active ? 'Active' : 'Inactive' }}
                </span>

                <!-- Date -->
                <span class="link-row__date col-center">{{ formatDate(link.created_at) }}</span>

                <!-- Actions -->
                <div class="link-row__actions">
                    <Link :href="route('links.show', link.id)" class="icon-btn icon-btn--chart" title="Analytics">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            v-html="icons.chart" />
                    </Link>
                    <Link :href="route('links.edit', link.id)" class="icon-btn" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            v-html="icons.edit" />
                    </Link>
                    <button @click="deleteLink(link.id)" class="icon-btn icon-btn--danger" title="Delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            v-html="icons.trash" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Editorial Pagination -->
        <div v-if="links.last_page > 1" class="pagination">
            <Link v-if="links.prev_page_url" :href="links.prev_page_url" class="page-btn">← Previous</Link>
            <span class="page-info">Page {{ links.current_page }} of {{ links.last_page }}</span>
            <Link v-if="links.next_page_url" :href="links.next_page_url" class="page-btn">Next →</Link>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

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
</style>
