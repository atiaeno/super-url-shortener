<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const mainNavItems = [
    { label: 'Dashboard', icon: 'dashboard', route: 'dashboard' },
    { label: 'My Links', icon: 'links', route: 'links.index' },
    { label: 'Create Link', icon: 'create', route: 'links.create' },
    { label: 'Bulk Create', icon: 'bulk', route: 'links.bulk' },
];

const toolsNavItems = [
    { label: 'Analytics', icon: 'analytics', route: 'analytics' },
    { label: 'Affiliate', icon: 'affiliate', route: 'affiliate.index' },
];

const supportNavItems = [
    { label: 'Help Center', icon: 'help', route: 'help.center' },
    { label: 'API Docs', icon: 'api', route: 'api-docs' },
    { label: 'Settings', icon: 'settings', route: 'profile.edit' },
    { label: 'Visit Site', icon: 'external', route: null, href: '/', external: true },
];


const toggleSidebar = () => { sidebarCollapsed.value = !sidebarCollapsed.value; };
const toggleMobileSidebar = () => { mobileSidebarOpen.value = !mobileSidebarOpen.value; };

const icons = {
    dashboard: `<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>`,
    links: `<path d="M9.5 14.5a4.5 4.5 0 0 1 6.36-6.36l3.5 3.5a4.5 4.5 0 0 1-6.36 6.36l-1.5-1.5"/><path d="M14.5 9.5a4.5 4.5 0 0 0-6.36 6.36l3.5 3.5a4.5 4.5 0 0 0 6.36-6.36l-1.5-1.5"/>`,
    create: `<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>`,
    bulk: `<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>`,
    analytics: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    affiliate: `<path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    help: `<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><circle cx="12" cy="17" r=".5"/>`,
    api: `<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>`,
    settings: `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09A1.65 1.65 0 0 0 15.32 4.68l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15z"/>`,
    logout: `<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>`,
    menu: `<line x1="4" y1="8" x2="20" y2="8"/><line x1="4" y1="16" x2="20" y2="16"/>`,
    'chevron-left': `<polyline points="15 18 9 12 15 6"/>`,
    external: `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>`,
};
</script>

<template>
    <div class="app-shell">

        <!-- Mobile backdrop -->
        <Transition name="fade">
            <div v-if="mobileSidebarOpen" class="mobile-backdrop" @click="mobileSidebarOpen = false" />
        </Transition>

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'sidebar--collapsed': sidebarCollapsed, 'sidebar--open': mobileSidebarOpen }">
            <!-- Ambient gradient -->
            <div class="sidebar__ambient"></div>

            <!-- Logo -->
            <div class="sidebar__brand">
                <Link :href="route('dashboard')" class="sidebar__logo">
                    <div class="sidebar__logo-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                            <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </div>
                    <Transition name="fade">
                        <span v-if="!sidebarCollapsed" class="sidebar__logo-text">ShortLink</span>
                    </Transition>
                </Link>
                <button class="sidebar__toggle" @click="toggleSidebar"
                    :aria-label="sidebarCollapsed ? 'Expand' : 'Collapse'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toggle-icon"
                        :class="{ 'toggle-icon--flipped': sidebarCollapsed }" v-html="icons['chevron-left']" />
                </button>
            </div>

            <!-- Navigation -->
            <div class="sidebar__content">
                <nav class="nav-section">
                    <Transition name="fade">
                        <span v-if="!sidebarCollapsed" class="nav-section__label">Main</span>
                    </Transition>
                    <div class="nav-section__items">
                        <Link v-for="item in mainNavItems" :key="item.route" :href="route(item.route)" class="nav-item"
                            :class="{ 'nav-item--active': route().current(item.route) || (item.route === 'links.index' && route().current('links.*') && !route().current('links.create') && !route().current('links.bulk')) }">
                            <span class="nav-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="icons[item.icon]" />
                            </span>
                            <Transition name="fade">
                                <span v-if="!sidebarCollapsed" class="nav-item__label">{{ item.label }}</span>
                            </Transition>
                        </Link>
                    </div>
                </nav>

                <nav class="nav-section">
                    <Transition name="fade">
                        <span v-if="!sidebarCollapsed" class="nav-section__label">Tools</span>
                    </Transition>
                    <div class="nav-section__items">
                        <Link v-for="item in toolsNavItems" :key="item.route" :href="route(item.route)" class="nav-item"
                            :class="{ 'nav-item--active': route().current(item.route) }">
                            <span class="nav-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="icons[item.icon]" />
                            </span>
                            <Transition name="fade">
                                <span v-if="!sidebarCollapsed" class="nav-item__label">{{ item.label }}</span>
                            </Transition>
                        </Link>
                    </div>
                </nav>

                <nav class="nav-section">
                    <Transition name="fade">
                        <span v-if="!sidebarCollapsed" class="nav-section__label">Support</span>
                    </Transition>
                    <div class="nav-section__items">
                        <a v-for="item in supportNavItems" :key="item.label"
                            :href="item.external ? item.href : route(item.route)"
                            :target="item.external ? '_blank' : '_self'" class="nav-item"
                            :class="{ 'nav-item--active': !item.external && route().current(item.route) }">
                            <span class="nav-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="icons[item.icon]" />
                            </span>
                            <Transition name="fade">
                                <span v-if="!sidebarCollapsed" class="nav-item__label">{{ item.label }}</span>
                            </Transition>
                        </a>
                    </div>
                </nav>

            </div>

            <!-- User -->
            <div class="sidebar__user">
                <div class="user-card" :class="{ 'user-card--collapsed': sidebarCollapsed }">
                    <div class="user-card__avatar">
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                    </div>
                    <Transition name="fade">
                        <div v-if="!sidebarCollapsed" class="user-card__info">
                            <span class="user-card__name">{{ user?.name }}</span>
                            <span class="user-card__email">{{ user?.email }}</span>
                        </div>
                    </Transition>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="logout-btn"
                    :class="{ 'logout-btn--collapsed': sidebarCollapsed }">
                    <span class="logout-btn__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            v-html="icons.logout" />
                    </span>
                    <Transition name="fade">
                        <span v-if="!sidebarCollapsed" class="logout-btn__text">Sign Out</span>
                    </Transition>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content" :class="{ 'main-content--expanded': sidebarCollapsed }">

            <!-- Mobile Top Bar -->
            <header class="topbar">
                <button class="topbar__menu-btn" @click="toggleMobileSidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"
                        v-html="icons['menu']" />
                </button>
                <div class="topbar__title">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <slot />
            </main>
        </div>
    </div>

    <!-- Toast Notifications -->
    <Toast :toasts="$page.props.flash?.toast || []" @dismiss="(id) => { }" />
</template>

<style>
/* ── Editorial Design System — Light ──────────── */
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,500;0,600;1,400&family=Oswald:wght@400;500;600;700&display=swap');

:root {
    /* Dimensions */
    --sidebar-width: 240px;
    --sidebar-collapsed: 60px;

    /* Editorial Palette */
    --red: #e74c3c;
    --red-dark: #c0392b;
    --gold: #d4af37;
    --ink: #1a1a1a;
    --ink-soft: #444;
    --muted: #888;
    --border: #e8e5e0;
    --bg: #fafafa;
    --surface: #fff;
    --surface-2: #f5f3ef;

    /* Typography */
    --font-display: 'Oswald', sans-serif;
    --font-body: 'Crimson Pro', serif;

    /* Effects */
    --shadow: 0 2px 12px rgba(26, 26, 26, 0.07);
    --radius: 4px;
    --transition: all 0.2s ease;
}
</style>

<style scoped>
/* ── App Shell ──────────────────────────────────── */
.app-shell {
    display: flex;
    min-height: 100vh;
    background: var(--bg);
    font-family: var(--font-body);
    color: var(--ink);
}

/* ── Sidebar ────────────────────────────────────── */
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

.sidebar--open {
    transform: translateX(0);
}

.sidebar__ambient {
    display: none;
}

/* ── Brand Section ──────────────────────────────── */
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

/* ── Navigation Content ─────────────────────────── */
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

/* Scrollbar styling */
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

/* ── Nav Section ───────────────────────────────── */
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

/* ── Nav Item ───────────────────────────────────── */
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

/* ── User Section ───────────────────────────────── */
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

/* ── Logout Button ──────────────────────────────── */
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

/* ── Main Content ───────────────────────────────── */
.main-content {
    margin-left: var(--sidebar-width);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: margin-left 0.3s ease;
}

.main-content--expanded {
    margin-left: var(--sidebar-collapsed);
}

/* ── Topbar ────────────────────────────────────── */
.topbar {
    height: 56px;
    background: rgba(250, 250, 250, 0.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 32px;
    gap: 14px;
    position: sticky;
    top: 0;
    z-index: 50;
}

.topbar__menu-btn {
    display: none;
    width: 32px;
    height: 32px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--muted);
    cursor: pointer;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.topbar__menu-btn:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.topbar__title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--muted);
}

/* ── Page Content ───────────────────────────────── */
.page-content {
    flex: 1;
    padding: 28px 32px 48px;
    background: var(--bg);
    min-height: calc(100vh - 56px);
}

/* ── Transitions ───────────────────────────────── */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

/* ── Mobile Backdrop ───────────────────────────── */
.mobile-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 26, 0.4);
    backdrop-filter: blur(2px);
    z-index: 90;
}

/* ── Responsive ────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: var(--sidebar-width) !important;
        transition: transform 0.3s ease;
    }

    .sidebar--open {
        transform: translateX(0);
    }

    .main-content,
    .main-content--expanded {
        margin-left: 0;
    }

    .topbar {
        padding: 0 16px;
    }

    .topbar__menu-btn {
        display: flex;
    }

    .page-content {
        padding: 20px 16px 32px;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .sidebar {
        width: var(--sidebar-collapsed);
    }

    .main-content {
        margin-left: var(--sidebar-collapsed);
    }
}
</style>
