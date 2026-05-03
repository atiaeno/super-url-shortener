<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

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
    { label: 'Analytics', icon: 'analytics', route: 'dashboard' },
    { label: 'Affiliate', icon: 'affiliate', route: 'affiliate.index' },
];

const supportNavItems = [
    { label: 'Help Center', icon: 'help', route: 'help.center' },
    { label: 'API Docs', icon: 'api', route: 'api.docs' },
    { label: 'Settings', icon: 'settings', route: 'profile.edit' },
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
};
</script>

<template>
    <div class="app-shell">

        <!-- Mobile backdrop -->
        <Transition name="fade">
            <div
                v-if="mobileSidebarOpen"
                class="mobile-backdrop"
                @click="mobileSidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="sidebar"
            :class="{ 'sidebar--collapsed': sidebarCollapsed, 'sidebar--open': mobileSidebarOpen }"
        >
            <!-- Ambient gradient -->
            <div class="sidebar__ambient"></div>

            <!-- Logo -->
            <div class="sidebar__brand">
                <Link :href="route('dashboard')" class="sidebar__logo">
                    <div class="sidebar__logo-icon">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2"/></svg>
                    </div>
                    <Transition name="fade">
                        <span v-if="!sidebarCollapsed" class="sidebar__logo-text">ShortLink</span>
                    </Transition>
                </Link>
                <button class="sidebar__toggle" @click="toggleSidebar" :aria-label="sidebarCollapsed ? 'Expand' : 'Collapse'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toggle-icon" :class="{ 'toggle-icon--flipped': sidebarCollapsed }" v-html="icons['chevron-left']" />
                </button>
            </div>

            <!-- Navigation -->
            <div class="sidebar__content">
                <nav class="nav-section">
                    <Transition name="fade">
                        <span v-if="!sidebarCollapsed" class="nav-section__label">Main</span>
                    </Transition>
                    <div class="nav-section__items">
                        <Link
                            v-for="item in mainNavItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="nav-item"
                            :class="{ 'nav-item--active': route().current(item.route) || (item.route === 'links.index' && route().current('links.*') && !route().current('links.create') && !route().current('links.bulk')) }"
                        >
                            <span class="nav-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="icons[item.icon]" />
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
                        <Link
                            v-for="item in toolsNavItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="nav-item"
                            :class="{ 'nav-item--active': route().current(item.route) }"
                        >
                            <span class="nav-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="icons[item.icon]" />
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
                        <Link
                            v-for="item in supportNavItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="nav-item"
                            :class="{ 'nav-item--active': route().current(item.route) }"
                        >
                            <span class="nav-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="icons[item.icon]" />
                            </span>
                            <Transition name="fade">
                                <span v-if="!sidebarCollapsed" class="nav-item__label">{{ item.label }}</span>
                            </Transition>
                        </Link>
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
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="logout-btn"
                    :class="{ 'logout-btn--collapsed': sidebarCollapsed }"
                >
                    <span class="logout-btn__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" v-html="icons.logout" />
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon" v-html="icons['menu']" />
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
</template>

<style>
/* ── Sophisticated Swiss Design System ──────────── */
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Newsreader:ital,wght@0,400;0,500;1,400&display=swap');

:root {
    /* Dimensions */
    --sidebar-width: 280px;
    --sidebar-collapsed: 76px;
    
    /* Colors - Sophisticated Navy & Coral */
    --navy-900: #0a1628;
    --navy-800: #111d32;
    --navy-700: #1a2942;
    --coral-500: #e85d4e;
    --coral-400: #f07062;
    --coral-300: #f5958a;
    --cream-50: #faf9f7;
    --cream-100: #f5f3f0;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-900: #111827;
    
    /* Typography */
    --font-display: 'Sora', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-body: 'Newsreader', Georgia, serif;
    
    /* Effects */
    --shadow-soft: 0 4px 20px rgba(10, 22, 40, 0.08);
    --shadow-medium: 0 8px 30px rgba(10, 22, 40, 0.12);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<style scoped>
/* ── App Shell ──────────────────────────────────── */
.app-shell {
    display: flex;
    min-height: 100vh;
    background: var(--cream-50);
    font-family: var(--font-display);
    color: var(--gray-900);
}

/* ── Sidebar ────────────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--sidebar-width);
    background: var(--navy-900);
    display: flex;
    flex-direction: column;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 100;
    overflow: hidden;
}

.sidebar--collapsed {
    width: var(--sidebar-collapsed);
}

.sidebar--open {
    transform: translateX(0);
}

/* Ambient gradient glow */
.sidebar__ambient {
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(ellipse at top right, rgba(232, 93, 78, 0.15) 0%, transparent 60%);
    pointer-events: none;
}

/* ── Brand Section ──────────────────────────────── */
.sidebar__brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 24px 24px;
    position: relative;
    z-index: 1;
}

.sidebar__logo {
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    color: #fff;
}

.sidebar__logo-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, var(--coral-500) 0%, var(--coral-400) 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(232, 93, 78, 0.3);
}

.sidebar__logo-icon svg {
    width: 20px;
    height: 20px;
    color: #fff;
}

.sidebar__logo-text {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 18px;
    color: #fff;
    white-space: nowrap;
}

.sidebar__toggle {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: var(--gray-400);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-smooth);
}

.sidebar__toggle:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.2);
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
    padding: 16px 16px 24px;
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    gap: 28px;
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
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
}

/* ── Nav Section ───────────────────────────────── */
.nav-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.nav-section__label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--gray-500);
    padding: 0 12px;
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
    gap: 14px;
    padding: 12px 14px;
    border-radius: var(--radius-sm);
    text-decoration: none;
    color: var(--gray-400);
    font-size: 14px;
    font-weight: 500;
    transition: var(--transition-smooth);
    position: relative;
    overflow: hidden;
}

.nav-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--coral-500);
    border-radius: 0 2px 2px 0;
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.nav-item:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.04);
}

.nav-item:hover::before {
    transform: scaleY(0.5);
}

.nav-item--active {
    color: #fff;
    background: rgba(232, 93, 78, 0.12);
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
    color: var(--coral-400);
}

.nav-item__label {
    white-space: nowrap;
    font-weight: 500;
}

/* ── User Section ───────────────────────────────── */
.sidebar__user {
    padding: 20px 16px 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    z-index: 1;
}

.user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-md);
    transition: var(--transition-smooth);
}

.user-card:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.12);
}

.user-card--collapsed {
    justify-content: center;
    padding: 12px;
}

.user-card__avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, var(--coral-500) 0%, var(--coral-400) 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
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
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-card__email {
    font-size: 11px;
    color: var(--gray-500);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Logout Button ──────────────────────────────── */
.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 10px 14px;
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-sm);
    color: var(--gray-400);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition-smooth);
    width: 100%;
}

.logout-btn:hover {
    background: rgba(232, 93, 78, 0.1);
    border-color: rgba(232, 93, 78, 0.3);
    color: var(--coral-400);
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
    transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.main-content--expanded {
    margin-left: var(--sidebar-collapsed);
}

/* ── Topbar ────────────────────────────────────── */
.topbar {
    height: 72px;
    background: rgba(250, 249, 247, 0.8);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(10, 22, 40, 0.06);
    display: flex;
    align-items: center;
    padding: 0 40px;
    gap: 16px;
    position: sticky;
    top: 0;
    z-index: 50;
}

.topbar__menu-btn {
    display: none;
    width: 40px;
    height: 40px;
    background: transparent;
    border: none;
    border-radius: 10px;
    color: var(--gray-500);
    cursor: pointer;
    align-items: center;
    justify-content: center;
    transition: var(--transition-smooth);
}

.topbar__menu-btn:hover {
    background: rgba(10, 22, 40, 0.04);
    color: var(--gray-900);
}

.topbar__title {
    font-family: var(--font-body);
    font-size: 22px;
    font-weight: 500;
    font-style: italic;
    color: var(--gray-900);
}

/* ── Page Content ───────────────────────────────── */
.page-content {
    flex: 1;
    padding: 32px 40px 48px;
    background: var(--cream-50);
    min-height: calc(100vh - 72px);
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
    background: rgba(10, 22, 40, 0.6);
    backdrop-filter: blur(4px);
    z-index: 90;
}

/* ── Responsive ────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: var(--sidebar-width) !important;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar--open {
        transform: translateX(0);
    }

    .main-content,
    .main-content--expanded {
        margin-left: 0;
    }

    .topbar {
        padding: 0 20px;
    }

    .topbar__menu-btn {
        display: flex;
    }

    .page-content {
        padding: 24px 20px 32px;
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
