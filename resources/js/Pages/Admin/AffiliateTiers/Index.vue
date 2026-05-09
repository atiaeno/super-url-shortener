<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tiers: { type: Array, default: () => [] },
});

const icons = {
    tiers: `<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>`,
    users: `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    edit: `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    globe: `<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>`,
    trash: `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>`,
    plus: `<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>`,
    x: `<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>`,
    check: `<polyline points="20 6 9 17 4 12"/>`,
};

const stats = computed(() => ({
    total: props.tiers.length,
    active: props.tiers.filter(t => t.is_active).length,
    affiliates: props.tiers.reduce((sum, t) => sum + (t.affiliates_count || 0), 0),
}));

const statItems = computed(() => [
    { id: 'total', label: 'Total Tiers', value: stats.value.total, roman: 'I.', icon: icons.tiers, color: 'red' },
    { id: 'active', label: 'Active', value: stats.value.active, roman: 'II.', icon: icons.tiers, color: 'green' },
    { id: 'affiliates', label: 'Total Affiliates', value: stats.value.affiliates, roman: 'III.', icon: icons.users, color: 'blue' },
]);

// Format helpers
const fRate = (v) => parseFloat(v) || 0;
const fMult = (v) => parseInt(v) || 1000;
const fmtMult = (v) => { const m = fMult(v); return m >= 1000 ? `${m / 1000}k` : m; };

// Create modal
const createModal = ref(false);
const createForm = useForm({
    name: '',
    visit_threshold: 0,
    view_rate: 3.00,
    view_multiplier: 10000,
    commission_rate: null,
});
const openCreateModal = () => { createModal.value = true; createForm.reset(); createForm.view_rate = 3.00; createForm.view_multiplier = 10000; };
const closeCreateModal = () => { createModal.value = false; createForm.reset(); };
const submitCreate = () => { createForm.post(route('admin.affiliate-tiers.store'), { onSuccess: () => closeCreateModal() }); };

// Edit modal
const editModal = ref(null);
const editForm = useForm({
    name: '',
    visit_threshold: 0,
    view_rate: 0,
    view_multiplier: 10000,
    commission_rate: null,
    is_active: true,
});
const openEditModal = (tier) => {
    editModal.value = tier;
    editForm.name = tier.name;
    editForm.visit_threshold = tier.visit_threshold;
    editForm.view_rate = fRate(tier.view_rate);
    editForm.view_multiplier = fMult(tier.view_multiplier);
    editForm.commission_rate = tier.commission_rate;
    editForm.is_active = tier.is_active;
};
const closeEditModal = () => { editModal.value = null; editForm.reset(); };
const submitEdit = () => { editForm.patch(route('admin.affiliate-tiers.update', editModal.value.id), { onSuccess: () => closeEditModal() }); };

// Country rates modal
const ratesModal = ref(null);
const ratesForm = useForm({ rates: [] });
const openRatesModal = (tier) => {
    ratesModal.value = tier;
    ratesForm.rates = tier.country_rates?.map(r => ({ country_code: r.country_code, commission_rate: parseFloat(r.commission_rate) || 0 })) ?? [];
};
const addRate = () => ratesForm.rates.push({ country_code: '', commission_rate: '' });
const removeRate = (i) => ratesForm.rates.splice(i, 1);
const submitRates = () => { ratesForm.post(route('admin.affiliate-tiers.country-rates', ratesModal.value.id), { onSuccess: () => { ratesModal.value = null; } }); };
</script>

<template>

    <Head title="Affiliate Tiers" />

    <AdminLayout>
        <template #header-icon>
            <polygon points="12 2 2 7 12 12 22 7 12 2" />
            <polyline points="2 17 12 22 22 17" />
            <polyline points="2 12 12 17 22 12" />
        </template>
        <template #header>Affiliate Tiers</template>

        <div class="tiers-page">

            <!-- Page Header -->
            <header class="page-header">
                <div class="page-header__left">
                    <span class="page-header__marker">Affiliate Program</span>
                    <h1 class="page-header__title">Tier Management</h1>
                    <p class="page-header__sub">Configure view-based earnings per unique views</p>
                </div>
                <button @click="openCreateModal" class="btn-create">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="icons.plus" />
                    Create Tier
                </button>
            </header>

            <div class="section-rule"></div>

            <!-- Stats -->
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

            <!-- Tiers Table -->
            <section class="table-section">
                <div class="section-header">
                    <h2 class="section-header__title">All Tiers</h2>
                </div>
                <div class="table-card">
                    <div v-if="!tiers.length" class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                v-html="icons.tiers" />
                        </div>
                        <p class="empty-state__title">No tiers created</p>
                        <p class="empty-state__text">Create your first affiliate tier to get started.</p>
                    </div>

                    <table v-else class="tiers-table">
                        <thead>
                            <tr>
                                <th class="table-header">Tier</th>
                                <th class="table-header">Rate</th>
                                <th class="table-header">Countries</th>
                                <th class="table-header">Affiliates</th>
                                <th class="table-header">Status</th>
                                <th class="table-header">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tier in tiers" :key="tier.id" class="table-row">
                                <td class="table-cell">
                                    <div class="tier-cell">
                                        <div class="tier-icon"
                                            :class="tier.is_active ? 'tier-icon--active' : 'tier-icon--inactive'">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.tiers" />
                                        </div>
                                        <div class="tier-details">
                                            <div class="tier-name">{{ tier.name }}</div>
                                            <div class="tier-meta">{{ tier.visit_threshold.toLocaleString() }}+ visits
                                                required</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <div class="rate-display">
                                        <span class="rate-amount">${{ fRate(tier.view_rate).toFixed(2) }}</span>
                                        <span class="rate-slash">/</span>
                                        <span class="rate-views">{{ fmtMult(tier.view_multiplier) }} unique
                                            views</span>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <div class="tier-countries" v-if="tier.country_rates?.length">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                            v-html="icons.globe" />
                                        {{ tier.country_rates.length }}
                                    </div>
                                    <span v-else class="text-muted">Global</span>
                                </td>
                                <td class="table-cell">
                                    <div class="tier-affiliates">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                            v-html="icons.users" />
                                        {{ tier.affiliates_count ?? 0 }}
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <span class="tier-status"
                                        :class="tier.is_active ? 'tier-status--active' : 'tier-status--inactive'">
                                        {{ tier.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <div class="tier-actions">
                                        <button @click="openEditModal(tier)" class="btn-icon btn-icon--edit"
                                            title="Edit Tier">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.edit" />
                                        </button>
                                        <button @click="openRatesModal(tier)" class="btn-icon btn-icon--globe"
                                            title="Country Rates">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                v-html="icons.globe" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Country Rates Modal -->
        <Teleport to="body">
            <div v-if="ratesModal" class="modal-overlay" @click="ratesModal = null">
                <div class="modal modal--wide" @click.stop>
                    <div class="modal__header">
                        <div class="modal__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.globe" />
                        </div>
                        <div>
                            <h3 class="modal__title">Country Rates — {{ ratesModal.name }}</h3>
                            <p class="modal__sub">Base: ${{ fRate(ratesModal.view_rate).toFixed(2) }} / {{
                                fmtMult(ratesModal.view_multiplier) }} views · Override rates for specific countries</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitRates" class="modal__body">
                        <div v-if="ratesForm.rates.length" class="rates-table">
                            <div class="rates-table__head">
                                <span class="rates-table__th rates-table__th--code">Country</span>
                                <span class="rates-table__th rates-table__th--rate">$ / {{
                                    fmtMult(ratesModal.view_multiplier)
                                    }} views</span>
                                <span class="rates-table__th rates-table__th--action"></span>
                            </div>
                            <div class="rates-table__body">
                                <div v-for="(rate, i) in ratesForm.rates" :key="i" class="rates-table__row">
                                    <input v-model="rate.country_code" type="text" placeholder="US" maxlength="2"
                                        class="rates-table__input rates-table__input--code" />
                                    <div class="rates-table__rate-cell">
                                        <span class="rates-table__dollar">$</span>
                                        <input v-model="rate.commission_rate" type="number" step="0.01" min="0"
                                            placeholder="3.00" class="rates-table__input rates-table__input--rate" />
                                    </div>
                                    <button type="button" @click="removeRate(i)" class="btn-icon btn-icon--delete"
                                        title="Remove">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            v-html="icons.trash" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="rates-empty">
                            <p>No country overrides — all countries use the base rate.</p>
                        </div>

                        <button type="button" @click="addRate" class="btn-add">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.plus" />
                            Add Country
                        </button>

                        <div class="modal__actions">
                            <button type="button" @click="ratesModal = null"
                                class="modal__btn modal__btn--ghost">Cancel</button>
                            <button type="submit" class="modal__btn modal__btn--primary"
                                :disabled="ratesForm.processing">
                                {{ ratesForm.processing ? 'Saving...' : 'Save Rates' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Create Tier Modal -->
        <Teleport to="body">
            <div v-if="createModal" class="modal-overlay" @click="closeCreateModal">
                <div class="modal" @click.stop>
                    <div class="modal__header">
                        <div class="modal__icon modal__icon--create">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.tiers" />
                        </div>
                        <h3 class="modal__title">Create New Tier</h3>
                    </div>
                    <form @submit.prevent="submitCreate" class="modal__body">
                        <div class="modal-fields">
                            <div class="field">
                                <label class="field__label">Tier Name <span class="required">*</span></label>
                                <input v-model="createForm.name" type="text" placeholder="e.g., Elite"
                                    class="field__input" required />
                                <span v-if="createForm.errors.name" class="field__error">{{ createForm.errors.name
                                    }}</span>
                            </div>
                            <div class="field">
                                <label class="field__label">Visit Threshold</label>
                                <input v-model="createForm.visit_threshold" type="number" min="0" placeholder="0"
                                    class="field__input" />
                                <span class="field__hint">Minimum visits to qualify</span>
                            </div>
                            <div class="field">
                                <label class="field__label">Rate <span class="required">*</span></label>
                                <div class="rate-inputs">
                                    <span class="currency">$</span>
                                    <input v-model="createForm.view_rate" type="number" step="0.01" min="0"
                                        placeholder="3.00" class="field__input field__input--rate" required />
                                    <span class="per">per</span>
                                    <select v-model="createForm.view_multiplier"
                                        class="field__input field__input--multiplier">
                                        <option :value="1000">1k views</option>
                                        <option :value="10000">10k views</option>
                                    </select>
                                </div>
                                <span v-if="createForm.errors.view_rate" class="field__error">{{
                                    createForm.errors.view_rate
                                    }}</span>
                            </div>
                        </div>
                        <div class="modal__actions">
                            <button type="button" @click="closeCreateModal"
                                class="modal__btn modal__btn--ghost">Cancel</button>
                            <button type="submit" class="modal__btn modal__btn--primary"
                                :disabled="createForm.processing">
                                {{ createForm.processing ? 'Creating...' : 'Create Tier' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Edit Tier Modal -->
        <Teleport to="body">
            <div v-if="editModal" class="modal-overlay" @click="closeEditModal">
                <div class="modal" @click.stop>
                    <div class="modal__header">
                        <div class="modal__icon modal__icon--edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                v-html="icons.edit" />
                        </div>
                        <h3 class="modal__title">Edit — {{ editModal.name }}</h3>
                    </div>
                    <form @submit.prevent="submitEdit" class="modal__body">
                        <div class="modal-fields">
                            <div class="field">
                                <label class="field__label">Tier Name <span class="required">*</span></label>
                                <input v-model="editForm.name" type="text" class="field__input" required />
                                <span v-if="editForm.errors.name" class="field__error">{{ editForm.errors.name }}</span>
                            </div>
                            <div class="field">
                                <label class="field__label">Visit Threshold</label>
                                <input v-model="editForm.visit_threshold" type="number" min="0" class="field__input" />
                            </div>
                            <div class="field">
                                <label class="field__label">Rate <span class="required">*</span></label>
                                <div class="rate-inputs">
                                    <span class="currency">$</span>
                                    <input v-model="editForm.view_rate" type="number" step="0.01" min="0"
                                        class="field__input field__input--rate" required />
                                    <span class="per">per</span>
                                    <select v-model="editForm.view_multiplier"
                                        class="field__input field__input--multiplier">
                                        <option :value="1000">1k views</option>
                                        <option :value="10000">10k views</option>
                                    </select>
                                </div>
                                <span v-if="editForm.errors.view_rate" class="field__error">{{ editForm.errors.view_rate
                                    }}</span>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" v-model="editForm.is_active" class="toggle__input" />
                                <span class="toggle__track"><span class="toggle__thumb" /></span>
                                <span class="toggle__label">{{ editForm.is_active ? 'Active' : 'Inactive' }}</span>
                            </label>
                        </div>
                        <div class="modal__actions">
                            <button type="button" @click="closeEditModal"
                                class="modal__btn modal__btn--ghost">Cancel</button>
                            <button type="submit" class="modal__btn modal__btn--primary"
                                :disabled="editForm.processing">
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
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

.btn-create {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-create:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.btn-create svg {
    width: 16px;
    height: 16px;
}

/* ── Section Rule ─────────────────────────── */
.section-rule {
    height: 1px;
    background: linear-gradient(90deg, var(--red) 60px, var(--border) 60px);
    margin-bottom: 28px;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.section-header__title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
}

/* ── Stats Grid ───────────────────────────── */
.stats-section {
    margin-bottom: 32px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.stat-card--red {
    background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
}

.stat-card--green {
    background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);
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

/* ── Table Styles ─────────────────────────── */
.tiers-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}

.table-header {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
    text-align: left;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface-2);
}

.table-row {
    border-bottom: 1px solid var(--border);
    transition: background 200ms;
}

.table-row:hover {
    background: var(--surface-2);
}

.table-cell {
    padding: 12px 16px;
    vertical-align: middle;
}

.tier-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.tier-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tier-icon--active {
    background: #dcfce7;
    color: #16a34a;
}

.tier-icon--inactive {
    background: #f3f4f6;
    color: var(--muted);
}

.tier-icon svg {
    width: 16px;
    height: 16px;
}

.tier-details {
    display: flex;
    flex-direction: column;
}

.tier-name {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 2px;
}

.tier-meta {
    font-family: var(--font-body);
    font-size: 10px;
    font-style: italic;
    color: var(--muted);
}

.rate-display {
    display: flex;
    align-items: baseline;
    gap: 4px;
    font-family: var(--font-display);
}

.rate-amount {
    font-size: 14px;
    font-weight: 600;
    color: var(--red);
}

.rate-slash {
    font-size: 11px;
    color: var(--muted);
    margin: 0 2px;
}

.rate-views {
    font-size: 10px;
    color: var(--ink-soft);
    font-weight: 500;
}

.tier-countries,
.tier-affiliates {
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    color: var(--ink-soft);
}

.tier-countries svg,
.tier-affiliates svg {
    width: 12px;
    height: 12px;
}

.text-muted {
    font-family: var(--font-body);
    font-size: 10px;
    font-style: italic;
    color: var(--muted);
}

.tier-status {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: var(--radius);
    display: inline-block;
}

.tier-status--active {
    background: #dcfce7;
    color: #16a34a;
}

.tier-status--inactive {
    background: #f3f4f6;
    color: var(--muted);
}

.tier-actions {
    display: flex;
    gap: 6px;
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
}

.btn-icon:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.btn-icon svg {
    width: 14px;
    height: 14px;
}

.btn-icon--edit {
    background: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
}

.btn-icon--edit:hover {
    background: #dbeafe;
    color: #2563eb;
}

.btn-icon--globe {
    background: #f5f3ef;
    color: var(--gold);
    border-color: #fde68a;
}

.btn-icon--globe:hover {
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
}

/* ── Toggle ──────────────────────────────── */
.toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.toggle__input {
    display: none;
}

.toggle__track {
    width: 36px;
    height: 20px;
    background: var(--border);
    border-radius: 10px;
    display: flex;
    align-items: center;
    padding: 2px;
    transition: background 200ms;
}

.toggle__input:checked+.toggle__track {
    background: var(--red);
}

.toggle__thumb {
    width: 16px;
    height: 16px;
    background: var(--surface);
    border-radius: 50%;
    transition: transform 200ms;
}

.toggle__input:checked+.toggle__track .toggle__thumb {
    transform: translateX(16px);
}

.toggle__label {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 500;
    color: var(--ink-soft);
}

/* ── Modal ───────────────────────────────── */
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
    max-width: 480px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    animation: modalEnter 0.2s ease;
}

.modal--wide {
    max-width: 560px;
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
    flex-shrink: 0;
}

.modal__icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    border-radius: var(--radius);
    color: #3b82f6;
    flex-shrink: 0;
}

.modal__icon--create {
    background: #fef2f2;
    color: var(--red);
}

.modal__icon--edit {
    background: #eff6ff;
    color: #3b82f6;
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

.modal__sub {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
    margin: 4px 0 0;
}

.modal__body {
    padding: 16px 24px 24px;
    overflow-y: auto;
    flex: 1;
}

.modal-fields {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* ── Form Fields ──────────────────────────── */
.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field__label {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    color: var(--ink);
    text-transform: uppercase;
}

.required {
    color: var(--red);
}

.highlight {
    color: var(--red);
    font-weight: 600;
}

.field__input {
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: var(--transition);
    outline: none;
}

.field__input:focus {
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.field__hint {
    font-family: var(--font-body);
    font-size: 12px;
    font-style: italic;
    color: var(--muted);
}

.field__error {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--red);
    font-style: italic;
}

.rate-inputs {
    display: flex;
    align-items: center;
    gap: 8px;
}

.currency,
.per {
    font-family: var(--font-display);
    font-size: 16px;
    color: var(--muted);
}

.field__input--rate {
    width: 100px;
    text-align: center;
}

.field__input--multiplier {
    width: auto;
    min-width: 110px;
}

/* ── Rates Table (Country Rates Modal) ────── */
.rates-table {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 12px;
}

.rates-table__head {
    display: grid;
    grid-template-columns: 80px 1fr 36px;
    gap: 8px;
    padding: 8px 12px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
}

.rates-table__th {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--muted);
}

.rates-table__body {
    max-height: 340px;
    overflow-y: auto;
}

.rates-table__row {
    display: grid;
    grid-template-columns: 80px 1fr 36px;
    gap: 8px;
    padding: 6px 12px;
    align-items: center;
    border-bottom: 1px solid var(--border);
}

.rates-table__row:last-child {
    border-bottom: none;
}

.rates-table__input {
    padding: 6px 8px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 13px;
    outline: none;
    transition: var(--transition);
}

.rates-table__input:focus {
    border-color: var(--red);
    box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.1);
}

.rates-table__input--code {
    text-transform: uppercase;
    text-align: center;
    font-family: var(--font-display);
    font-weight: 600;
    letter-spacing: 1px;
}

.rates-table__input--rate {
    text-align: right;
    width: 100%;
}

.rates-table__rate-cell {
    display: flex;
    align-items: center;
    gap: 4px;
}

.rates-table__dollar {
    font-family: var(--font-display);
    font-size: 13px;
    color: var(--muted);
    flex-shrink: 0;
}

.rates-empty {
    text-align: center;
    padding: 24px 16px;
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--muted);
    margin-bottom: 12px;
}

.rates-empty p {
    margin: 0;
}

.btn-add {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px;
    background: var(--surface);
    border: 1px dashed var(--border);
    border-radius: var(--radius);
    color: var(--muted);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-add:hover {
    color: var(--red);
    border-color: var(--red);
    background: #fef2f2;
}

.btn-add svg {
    width: 16px;
    height: 16px;
}

.modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background: var(--surface-2);
    border-top: 1px solid var(--border);
    margin: 24px -24px -24px;
    border-radius: 0 0 var(--radius) var(--radius);
    flex-shrink: 0;
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

.modal__btn--primary {
    background: var(--red);
    border-color: var(--red);
    color: var(--surface);
}

.modal__btn--primary:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.modal__btn--primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ── Responsive ────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .tier-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .tier-side {
        width: 100%;
        justify-content: flex-start;
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

    .rate-inputs {
        flex-wrap: wrap;
    }
}
</style>
