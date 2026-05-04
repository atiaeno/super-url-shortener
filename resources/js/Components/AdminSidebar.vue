<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle']);

const adminNavItems = [
    { label: 'Dashboard', icon: 'dashboard', route: 'admin.dashboard' },
    { label: 'Users', icon: 'users', route: 'admin.users.index' },
    { label: 'Links', icon: 'links', route: 'admin.links.index' },
    { label: 'Payouts', icon: 'payouts', route: 'admin.payouts.index' },
    { label: 'Affiliate Tiers', icon: 'tiers', route: 'admin.affiliate-tiers.index' },
    { label: 'Ads', icon: 'ads', route: 'admin.ads.index' },
    { label: 'Moderation', icon: 'moderation', route: 'admin.moderation.index' },
    { label: 'Settings', icon: 'settings', route: 'admin.settings.index' },
];

const toggleSidebar = () => emit('toggle');

const icons = {
    dashboard: `<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>`,
    users: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    links: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    payouts: `<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    tiers: `<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>`,
    ads: `<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>`,
    moderation: `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>`,
    settings: `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09A1.65 1.65 0 0 0 15.32 4.68l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15z"/>`,
    logout: `<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>`,
    'chevron-left': `<polyline points="15 18 9 12 15 6"/>`,
};
</script>

<template>
    <aside class="sidebar" :class="{ 'sidebar--collapsed': collapsed }">
        <div class="sidebar__ambient"></div>

        <div class="sidebar__brand">
            <Link :href="route('admin.dashboard')" class="sidebar__logo">
                <div class="sidebar__logo-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                        <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" />
                    </svg>
                </div>
                <Transition name="fade">
                    <span v-if="!collapsed" class="sidebar__logo-text">Admin</span>
                </Transition>
            </Link>
            <button class="sidebar__toggle" @click="toggleSidebar" :aria-label="collapsed ? 'Expand' : 'Collapse'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toggle-icon"
                    :class="{ 'toggle-icon--flipped': collapsed }" v-html="icons['chevron-left']" />
            </button>
        </div>

        <div class="sidebar__content">
            <nav class="nav-section">
                <Transition name="fade">
                    <span v-if="!collapsed" class="nav-section__label">Admin</span>
                </Transition>
                <div class="nav-section__items">
                    <Link v-for="item in adminNavItems" :key="item.route" :href="route(item.route)" class="nav-item"
                        :class="{ 'nav-item--active': route().current(item.route) }">
                        <span class="nav-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons[item.icon]" />
                        </span>
                        <Transition name="fade">
                            <span v-if="!collapsed" class="nav-item__label">{{ item.label }}</span>
                        </Transition>
                    </Link>
                </div>
            </nav>
        </div>

        <div class="sidebar__user">
            <div class="user-card" :class="{ 'user-card--collapsed': collapsed }">
                <div class="user-card__avatar">
                    {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                </div>
                <Transition name="fade">
                    <div v-if="!collapsed" class="user-card__info">
                        <span class="user-card__name">{{ user?.name }}</span>
                        <span class="user-card__email">{{ user?.email }}</span>
                    </div>
                </Transition>
            </div>
            <Link :href="route('logout')" method="post" as="button" class="logout-btn"
                :class="{ 'logout-btn--collapsed': collapsed }">
                <span class="logout-btn__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        v-html="icons.logout" />
                </span>
                <Transition name="fade">
                    <span v-if="!collapsed" class="logout-btn__text">Sign Out</span>
                </Transition>
            </Link>
        </div>
    </aside>
</template>

<style scoped>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--sidebar-width);
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    transition: width 0.3s ease;
    z-index: 100;
    overflow: hidden;
}

.sidebar--collapsed {
    width: var(--sidebar-collapsed);
}

.sidebar__ambient {
    display: none;
}

.sidebar__brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 16px 16px;
    border-bottom: 1px solid var(--border);
    position: relative;
    z-index: 1;
}

.sidebar__logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--ink);
}

.sidebar__logo-icon {
    width: 28px;
    height: 28px;
    background: var(--red);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar__logo-icon svg {
    width: 20px;
    height: 20px;
    color: #fff;
}

.sidebar__logo-text {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink);
    white-space: nowrap;
}

.sidebar__toggle {
    width: 28px;
    height: 28px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.sidebar__toggle:hover {
    background: var(--surface-2);
    color: var(--ink);
    border-color: #ccc;
}

.toggle-icon {
    width: 16px;
    height: 16px;
    transition: transform 0.3s ease;
}

.toggle-icon--flipped {
    transform: rotate(180deg);
}

.sidebar__content {
    flex: 1;
    padding: 12px 10px 16px;
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    gap: 20px;
    position: relative;
    z-index: 1;
}

.sidebar__content::-webkit-scrollbar {
    width: 4px;
}

.sidebar__content::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar__content::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 2px;
}

.nav-section {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-section__label {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--muted);
    padding: 0 8px;
    margin-bottom: 2px;
    white-space: nowrap;
}

.nav-section__items {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--ink-soft);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: var(--transition);
    position: relative;
}

.nav-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 20%;
    bottom: 20%;
    width: 2px;
    background: var(--red);
    border-radius: 0 2px 2px 0;
    transform: scaleY(0);
    transition: transform 0.2s ease;
}

.nav-item:hover {
    color: var(--ink);
    background: var(--surface-2);
}

.nav-item:hover::before {
    transform: scaleY(0.6);
}

.nav-item--active {
    color: var(--ink);
    background: #fef2f2;
}

.nav-item--active::before {
    transform: scaleY(1);
}

.nav-item__icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.nav-item__icon svg {
    width: 100%;
    height: 100%;
}

.nav-item--active .nav-item__icon {
    color: var(--red);
}

.nav-item__label {
    white-space: nowrap;
}

.sidebar__user {
    padding: 12px 10px 16px;
    border-top: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
    z-index: 1;
}

.user-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: var(--transition);
}

.user-card:hover {
    background: #eeeae4;
}

.user-card--collapsed {
    justify-content: center;
    padding: 12px;
}

.user-card__avatar {
    width: 30px;
    height: 30px;
    background: var(--red);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    flex-shrink: 0;
}

.user-card__info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
}

.user-card__name {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-card__email {
    font-size: 11px;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 12px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--muted);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
}

.logout-btn:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: var(--red);
}

.logout-btn__icon {
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logout-btn__icon svg {
    width: 100%;
    height: 100%;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}
</style>
