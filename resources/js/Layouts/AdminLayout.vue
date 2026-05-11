<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const toggleSidebar = () => { sidebarCollapsed.value = !sidebarCollapsed.value; };
const toggleMobileSidebar = () => { mobileSidebarOpen.value = !mobileSidebarOpen.value; };
</script>

<template>
    <div class="app-shell">
        <Transition name="fade">
            <div v-if="mobileSidebarOpen" class="mobile-backdrop" @click="mobileSidebarOpen = false" />
        </Transition>

        <AdminSidebar :collapsed="sidebarCollapsed" @toggle="toggleSidebar"
            :class="{ 'sidebar--open': mobileSidebarOpen }" />

        <div class="main-content" :class="{ 'main-content--expanded': sidebarCollapsed }">
            <header class="topbar">
                <button class="topbar__menu-btn" @click="toggleMobileSidebar">
                    <svg v-if="!mobileSidebarOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="icon">
                        <line x1="4" y1="8" x2="20" y2="8" />
                        <line x1="4" y1="16" x2="20" y2="16" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <div class="topbar__title">
                    <span class="topbar__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <slot name="header-icon">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="16" />
                                <line x1="8" y1="12" x2="16" y2="12" />
                            </slot>
                        </svg>
                    </span>
                    <slot name="header" />
                </div>
            </header>

            <main class="page-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,500;0,600;1,400&family=Oswald:wght@400;500;600;700&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');
@import url('../../../public/css/admin.css');

:root {
    --sidebar-width: 240px;
    --sidebar-collapsed: 60px;
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
    --font-display: 'Oswald', sans-serif;
    --font-body: 'Crimson Pro', serif;
    --font-admin: 'DM Sans', sans-serif;
    --font-sidebar: 'DM Sans', sans-serif;
    --shadow: 0 2px 12px rgba(26, 26, 26, 0.07);
    --radius: 4px;
    --transition: all 0.2s ease;
}
</style>

<style scoped>
.app-shell {
    display: flex;
    min-height: 100vh;
    background: var(--bg);
    font-family: var(--font-body);
    color: var(--ink);
}

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
    display: flex;
    align-items: center;
    gap: 10px;
}

.topbar__icon {
    width: 20px;
    height: 20px;
    color: var(--red);
    display: flex;
    align-items: center;
    justify-content: center;
}

.page-content {
    flex: 1;
    padding: 28px 32px 48px;
    background: var(--bg);
    min-height: calc(100vh - 56px);
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

.mobile-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 26, 0.4);
    backdrop-filter: blur(2px);
    z-index: 90;
}

@media (max-width: 768px) {

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
    .main-content {
        margin-left: var(--sidebar-collapsed);
    }
}
</style>
