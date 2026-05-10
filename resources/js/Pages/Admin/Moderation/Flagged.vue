// © Atia Hegazy — atiaeno.com
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    flaggedLinks: Object,
    stats: Object,
});
</script>

<template>
    <AdminLayout title="Flagged Links - Admin">
        <div class="page-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">{{ stats.flagged }}</div>
                    <div class="stat-label">Flagged Links</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ stats.auto_suspended }}</div>
                    <div class="stat-label">Auto Suspended</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ stats.high_priority }}</div>
                    <div class="stat-label">High Priority</div>
                </div>
            </div>

            <div class="table-section">
                <div class="table-card">
                    <div class="section-header">
                        <div class="section-title">
                            <span class="material-icons">flag</span>
                            Flagged Links
                        </div>
                    </div>

                    <div v-if="!flaggedLinks.data?.length" class="empty-state">
                        <div class="empty-state__icon">
                            <span class="material-icons">check_circle</span>
                        </div>
                        <p class="empty-state__title">No flagged links found</p>
                        <p class="empty-state__text">All links are clean.</p>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="flagged-table">
                            <thead>
                                <tr>
                                    <th>Link</th>
                                    <th>Reports</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="link in flaggedLinks.data" :key="link.id" class="table-row">
                                    <td>
                                        <div class="link-cell">
                                            <a :href="link.url" target="_blank" class="link-url">
                                                {{ link.short_code }}
                                            </a>
                                            <div class="link-target">{{ link.url }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="report-count">{{ link.reports_count }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge" :class="`status-badge--${link.is_active ? 'active' : 'inactive'}`">
                                            {{ link.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span v-if="link.auto_suspended_at" class="auto-suspended-badge">
                                            Auto Suspended
                                        </span>
                                    </td>
                                    <td>{{ new Date(link.created_at).toLocaleDateString() }}</td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-action btn-action--view" title="View Reports">
                                                <span class="material-icons">visibility</span>
                                            </button>
                                            <button class="btn-action btn-action--edit" title="Edit Link">
                                                <span class="material-icons">edit</span>
                                            </button>
                                            <button class="btn-action btn-action--delete" title="Delete Link">
                                                <span class="material-icons">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.page-content {
    padding: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 14px;
    color: #71717a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-section {
    margin-top: 24px;
}

.table-card {
    background: white;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    overflow: hidden;
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e4e4e7;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
}

.section-title .material-icons {
    color: #ef4444;
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
}

.empty-state__icon {
    margin-bottom: 16px;
    color: #22c55e;
}

.empty-state__icon .material-icons {
    font-size: 48px;
}

.empty-state__title {
    font-family: 'Oswald', sans-serif;
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.empty-state__text {
    color: #71717a;
}

.table-wrapper {
    overflow-x: auto;
}

.flagged-table {
    width: 100%;
    border-collapse: collapse;
}

.flagged-table th {
    background: #f8fafc;
    padding: 12px 16px;
    text-align: left;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #71717a;
    border-bottom: 1px solid #e4e4e7;
}

.flagged-table td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.flagged-table tr:hover td {
    background: #f8fafc;
}

.link-cell .link-url {
    display: block;
    color: #3b82f6;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    margin-bottom: 4px;
}

.link-cell .link-url:hover {
    text-decoration: underline;
}

.link-target {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    color: #71717a;
    word-break: break-all;
}

.report-count {
    display: inline-block;
    padding: 4px 8px;
    background: #fef2f2;
    color: #dc2626;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    margin-right: 4px;
}

.status-badge--active {
    background: #dcfce7;
    color: #166534;
}

.status-badge--inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.auto-suspended-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #fef3c7;
    color: #92400e;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid #e4e4e7;
    border-radius: 6px;
    color: #71717a;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-action:hover {
    background: #f8fafc;
    color: #1a1a1a;
}

.btn-action .material-icons {
    font-size: 16px;
}

.btn-action--view:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #3b82f6;
}

.btn-action--edit:hover {
    background: #f0fdf4;
    border-color: #22c55e;
    color: #22c55e;
}

.btn-action--delete:hover {
    background: #fef2f2;
    border-color: #ef4444;
    color: #ef4444;
}
</style>
