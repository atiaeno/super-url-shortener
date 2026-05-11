<!--// Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import '../../css/admin_sidebar.css';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Get route function from ziggy - use global route
const route = (name, params = {}) => {
    // When called without arguments, return an object with current method
    if (name === undefined) {
        return {
            current: (routeName) => {
                const currentRoute = window.route().current();
                return currentRoute === routeName || currentRoute.startsWith(routeName + '.');
            }
        };
    }

    return window.route(name, params);
};

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle']);

const navGroups = [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard', icon: 'dashboard', route: 'admin.dashboard' },
            { label: 'Analytics', icon: 'analytics', route: 'admin.analytics' },
        ],
    },
    {
        label: 'Content',
        items: [
            { label: 'Users', icon: 'users', route: 'admin.users.index' },
            { label: 'Links', icon: 'links', route: 'admin.links.index' },
            { label: 'Alias Domains', icon: 'domains', route: 'admin.alias-domains.index' },
        ],
    },
    {
        label: 'Monetization',
        items: [
            { label: 'Payouts', icon: 'payouts', route: 'admin.payouts.index' },
            { label: 'Affiliate Tiers', icon: 'tiers', route: 'admin.affiliate-tiers.index' },
            { label: 'Advertising', icon: 'ads', route: 'admin.advertising.index' },
        ],
    },
    {
        label: 'System',
        items: [
            { label: 'SEO Indexer', icon: 'indexer', route: 'admin.settings.indexer.index' },
            { label: 'Settings', icon: 'settings', route: 'admin.settings.index' },
        ],
    },
];

const isModerationActive = computed(() => {
    const currentRoute = window.route().current();
    return currentRoute.startsWith('admin.moderation');
});

// Keep dropdown open if a submenu is selected or if manually toggled
const isModerationDropdownOpen = computed(() => {
    return isModerationActive.value || moderationDropdownOpen.value;
});

const moderationDropdownOpen = ref(false);

const toggleModerationDropdown = () => {
    moderationDropdownOpen.value = !moderationDropdownOpen.value;
    // Auto-close dropdown when sidebar is collapsed and dropdown opens
    if (props.collapsed && moderationDropdownOpen.value) {
        setTimeout(() => {
            document.addEventListener('click', closeDropdownOnClickOutside);
        }, 100);
    } else {
        document.removeEventListener('click', closeDropdownOnClickOutside);
    }
};

const closeDropdownOnClickOutside = (event) => {
    const dropdown = document.querySelector('.nav-dropdown');
    if (dropdown && !dropdown.contains(event.target) && !isModerationActive.value) {
        moderationDropdownOpen.value = false;
        document.removeEventListener('click', closeDropdownOnClickOutside);
    }
};

const toggleSidebar = () => emit('toggle');

const icons = {
    dashboard: `<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>`,
    users: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    links: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    domains: `<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>`,
    payouts: `<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    tiers: `<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>`,
    ads: `<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>`,
    moderation: `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>`,
    settings: `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09A1.65 1.65 0 0 0 15.32 4.68l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15z"/>`,
    logout: `<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>`,
    'chevron-left': `<polyline points="15 18 9 12 15 6"/>`,
    'chevron-down': `<polyline points="6 9 12 15 18 9"/>`,
    external: `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>`,
    'report-queue': `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>`,
    'flagged-links': `<path d="M3 21v-4m0 0V5a2 2 0 0 1 2-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 0 0-2 2zm9-13.5V9"/>`,
    indexer: `<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>`,
    analytics: `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
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
            <!-- Dynamic Nav Groups -->
            <nav v-for="group in navGroups" :key="group.label" class="nav-section">
                <Transition name="fade">
                    <span v-if="!collapsed" class="nav-section__label">{{ group.label }}</span>
                </Transition>
                <div class="nav-section__items">
                    <Link v-for="item in group.items" :key="item.route" :href="route(item.route)" class="nav-item"
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

            <!-- Moderation Group -->
            <nav class="nav-section">
                <Transition name="fade">
                    <span v-if="!collapsed" class="nav-section__label">Moderation</span>
                </Transition>
                <div class="nav-section__items">
                    <!-- Moderation Dropdown -->
                    <div class="nav-dropdown" :class="{ 'nav-dropdown--open': isModerationDropdownOpen }">
                        <button @click="toggleModerationDropdown" class="nav-item nav-item--dropdown"
                            :class="{ 'nav-item--active': isModerationActive }">
                            <span class="nav-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    v-html="icons.moderation" />
                            </span>
                            <Transition name="fade">
                                <span v-if="!collapsed" class="nav-item__label">Moderation</span>
                            </Transition>
                            <Transition name="fade">
                                <span v-if="!collapsed" class="nav-item__chevron"
                                    :class="{ 'nav-item__chevron--open': isModerationDropdownOpen }">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        v-html="icons['chevron-down']" />
                                </span>
                            </Transition>
                        </button>

                        <Transition name="dropdown">
                            <div v-show="isModerationDropdownOpen" class="nav-dropdown__items">
                                <Link :href="route('admin.moderation.reports')" class="nav-item nav-item--sub"
                                    :class="{ 'nav-item--active': route().current('admin.moderation.reports') }">
                                    <span class="nav-item__icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                            v-html="icons['report-queue']" />
                                    </span>
                                    <Transition name="fade">
                                        <span v-if="!collapsed" class="nav-item__label">Report Queue</span>
                                    </Transition>
                                </Link>
                                <Link :href="route('admin.moderation.flagged')" class="nav-item nav-item--sub"
                                    :class="{ 'nav-item--active': route().current('admin.moderation.flagged') }">
                                    <span class="nav-item__icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                            v-html="icons['flagged-links']" />
                                    </span>
                                    <Transition name="fade">
                                        <span v-if="!collapsed" class="nav-item__label">Flagged Links</span>
                                    </Transition>
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </nav>

            <!-- External -->
            <nav class="nav-section nav-section--external">
                <Transition name="fade">
                    <span v-if="!collapsed" class="nav-section__label">External</span>
                </Transition>
                <div class="nav-section__items">
                    <a href="/" target="_blank" rel="noopener noreferrer" class="nav-item">
                        <span class="nav-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons['external']" />
                        </span>
                        <Transition name="fade">
                            <span v-if="!collapsed" class="nav-item__label">Visit Website</span>
                        </Transition>
                    </a>
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
