<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
});
</script>

<template>

    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="admin-page">
            <h1 class="admin-page__title">Dashboard</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats?.totalUsers ?? 0 }}</span>
                    <span class="stat-card__label">Total Users</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats?.totalLinks ?? 0 }}</span>
                    <span class="stat-card__label">Total Links</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats?.totalClicks ?? 0 }}</span>
                    <span class="stat-card__label">Total Clicks</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card__value">{{ stats?.pendingPayouts ?? 0 }}</span>
                    <span class="stat-card__label">Pending Payouts</span>
                </div>
            </div>

            <div class="admin-section">
                <h2 class="admin-section__title">Quick Actions</h2>
                <div class="quick-actions">
                    <a :href="route('admin.users.index')" class="quick-action">
                        <span class="quick-action__icon">👤</span>
                        Manage Users
                    </a>
                    <a :href="route('admin.payouts.index')" class="quick-action">
                        <span class="quick-action__icon">💰</span>
                        Review Payouts
                    </a>
                    <a :href="route('admin.moderation.index')" class="quick-action">
                        <span class="quick-action__icon">🛡️</span>
                        Moderation
                    </a>
                    <a :href="route('admin.settings.index')" class="quick-action">
                        <span class="quick-action__icon">⚙️</span>
                        Settings
                    </a>
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
    grid-template-columns: repeat(4, 1fr);
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
    padding: 20px;
}

.admin-section__title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--ink);
    margin-bottom: 16px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.quick-action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    text-decoration: none;
    font-family: var(--font-body);
    font-size: 13px;
    transition: all 0.2s ease;
}

.quick-action:hover {
    border-color: var(--red);
    background: var(--surface-2);
}

.quick-action__icon {
    font-size: 20px;
}

@media (max-width: 1024px) {

    .stats-grid,
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {

    .stats-grid,
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>
