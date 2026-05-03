<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tiers: { type: Array, default: () => [] },
});

const createForm = useForm({ name: '', visit_threshold: 0, commission_rate: 0 });
const editingId = ref(null);
const editForm = useForm({ name: '', visit_threshold: 0, commission_rate: 0, is_active: true });

const startEdit = (tier) => {
    editingId.value = tier.id;
    editForm.name = tier.name;
    editForm.visit_threshold = tier.visit_threshold;
    editForm.commission_rate = tier.commission_rate;
    editForm.is_active = tier.is_active;
};

const cancelEdit = () => { editingId.value = null; editForm.reset(); };

const submitEdit = (id) => {
    editForm.patch(route('admin.affiliate-tiers.update', id), {
        onSuccess: () => cancelEdit(),
    });
};

// Country rates modal
const ratesModal = ref(null);
const ratesForm = useForm({ rates: [] });

const openRatesModal = (tier) => {
    ratesModal.value = tier;
    ratesForm.rates = tier.country_rates?.map(r => ({ country_code: r.country_code, commission_rate: r.commission_rate })) ?? [];
};

const addRate = () => ratesForm.rates.push({ country_code: '', commission_rate: '' });
const removeRate = (i) => ratesForm.rates.splice(i, 1);

const submitRates = () => {
    ratesForm.post(route('admin.affiliate-tiers.country-rates', ratesModal.value.id), {
        onSuccess: () => { ratesModal.value = null; },
    });
};
</script>

<template>

    <Head title="Affiliate Tiers" />

    <AdminLayout>
        <template #header>Affiliate Tier Management</template>

        <!-- Create new tier -->
        <div class="card mb">
            <h3 class="card-title">Create Tier</h3>
            <form @submit.prevent="createForm.post(route('admin.affiliate-tiers.store'))" class="inline-form">
                <div class="field">
                    <label class="field__label">Name</label>
                    <input v-model="createForm.name" type="text" placeholder="Bronze" class="field__input" required />
                </div>
                <div class="field">
                    <label class="field__label">Visit Threshold</label>
                    <input v-model="createForm.visit_threshold" type="number" min="0" class="field__input" required />
                </div>
                <div class="field">
                    <label class="field__label">Commission %</label>
                    <input v-model="createForm.commission_rate" type="number" step="0.01" min="0" max="100"
                        class="field__input" required />
                </div>
                <button type="submit" class="btn-primary" :disabled="createForm.processing">Add Tier</button>
            </form>
        </div>

        <!-- Tiers table -->
        <div class="table-card">
            <div class="table-head">
                <span>Name</span>
                <span>Visit Threshold</span>
                <span>Commission %</span>
                <span>Affiliates</span>
                <span>Status</span>
                <span>Actions</span>
            </div>

            <div v-for="tier in tiers" :key="tier.id">
                <!-- View row -->
                <div v-if="editingId !== tier.id" class="table-row">
                    <span class="cell-name">{{ tier.name }}</span>
                    <span class="cell-num">{{ tier.visit_threshold.toLocaleString() }}</span>
                    <span class="cell-rate">{{ tier.commission_rate }}%</span>
                    <span class="cell-num">{{ tier.affiliates_count ?? 0 }}</span>
                    <span class="badge" :class="tier.is_active ? 'badge--green' : 'badge--red'">
                        {{ tier.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <div class="cell-actions">
                        <button @click="startEdit(tier)" class="action-btn">Edit</button>
                        <button @click="openRatesModal(tier)" class="action-btn">Country Rates</button>
                    </div>
                </div>

                <!-- Edit row -->
                <div v-else class="edit-row">
                    <input v-model="editForm.name" class="field__input field__input--sm" placeholder="Name" />
                    <input v-model="editForm.visit_threshold" type="number" class="field__input field__input--sm" />
                    <input v-model="editForm.commission_rate" type="number" step="0.01"
                        class="field__input field__input--sm" />
                    <span></span>
                    <label class="toggle">
                        <input type="checkbox" v-model="editForm.is_active" class="toggle__input" />
                        <span class="toggle__track"><span class="toggle__thumb" /></span>
                    </label>
                    <div class="cell-actions">
                        <button @click="submitEdit(tier.id)" class="action-btn action-btn--primary"
                            :disabled="editForm.processing">Save</button>
                        <button @click="cancelEdit" class="action-btn">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Country Rates Modal -->
        <div v-if="ratesModal" class="modal-overlay" @click.self="ratesModal = null">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Country Rates — {{ ratesModal.name }}</h3>
                    <button @click="ratesModal = null" class="modal__close">✕</button>
                </div>

                <form @submit.prevent="submitRates" class="modal__body">
                    <div v-for="(rate, i) in ratesForm.rates" :key="i" class="rate-row">
                        <input v-model="rate.country_code" type="text" placeholder="US" maxlength="2"
                            class="field__input field__input--sm" style="width:70px;text-transform:uppercase" />
                        <input v-model="rate.commission_rate" type="number" step="0.01" min="0" max="100"
                            placeholder="%" class="field__input field__input--sm" style="width:90px" />
                        <button type="button" @click="removeRate(i)" class="action-btn action-btn--danger">✕</button>
                    </div>

                    <button type="button" @click="addRate" class="btn-ghost-sm">+ Add Country</button>

                    <div class="modal__footer">
                        <button type="button" @click="ratesModal = null" class="btn-ghost">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="ratesForm.processing">Save Rates</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.mb {
    margin-bottom: 20px;
}

.card {
    background: #141414;
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    padding: 24px;
}

.card-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #A1A1AA;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 16px;
}

.inline-form {
    display: flex;
    align-items: flex-end;
    gap: 14px;
    flex-wrap: wrap;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.field__label {
    font-size: 12px;
    color: #71717A;
}

.field__input {
    background: #0A0A0A;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 13px;
    color: #FAFAFA;
    outline: none;
    transition: border-color 200ms;
}

.field__input:focus {
    border-color: #22D3EE;
}

.field__input--sm {
    padding: 7px 10px;
    font-size: 13px;
}

/* ── Table ─────────────────────────────────── */
.table-card {
    background: #141414;
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    overflow: hidden;
}

.table-head {
    display: grid;
    grid-template-columns: 1fr 140px 120px 100px 90px 200px;
    padding: 10px 20px;
    font-size: 11px;
    font-weight: 600;
    color: #52525B;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.table-row,
.edit-row {
    display: grid;
    grid-template-columns: 1fr 140px 120px 100px 90px 200px;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    transition: background 200ms;
}

.table-row:hover {
    background: rgba(255, 255, 255, 0.02);
}

.table-row:last-child,
.edit-row:last-child {
    border-bottom: none;
}

.cell-name {
    font-size: 14px;
    font-weight: 600;
    color: #FAFAFA;
}

.cell-num {
    font-size: 13px;
    color: #A1A1AA;
}

.cell-rate {
    font-size: 14px;
    font-weight: 600;
    color: #22D3EE;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    width: fit-content;
}

.badge--green {
    color: #22C55E;
    background: rgba(34, 197, 94, 0.1);
}

.badge--red {
    color: #EF4444;
    background: rgba(239, 68, 68, 0.1);
}

.cell-actions {
    display: flex;
    gap: 6px;
}

.action-btn {
    font-size: 11px;
    color: #71717A;
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: none;
    cursor: pointer;
    transition: all 200ms;
    white-space: nowrap;
}

.action-btn:hover {
    color: #FAFAFA;
    border-color: rgba(255, 255, 255, 0.2);
}

.action-btn--primary {
    color: #22D3EE;
    border-color: rgba(34, 211, 238, 0.2);
}

.action-btn--primary:hover {
    background: rgba(34, 211, 238, 0.1);
}

.action-btn--danger:hover {
    color: #EF4444;
    border-color: rgba(239, 68, 68, 0.3);
}

/* ── Toggle ──────────────────────────────────── */
.toggle {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.toggle__input {
    display: none;
}

.toggle__track {
    width: 36px;
    height: 20px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    padding: 2px;
    transition: background 200ms;
}

.toggle__input:checked+.toggle__track {
    background: #22D3EE;
}

.toggle__thumb {
    width: 16px;
    height: 16px;
    background: #fff;
    border-radius: 50%;
    transition: transform 200ms;
}

.toggle__input:checked+.toggle__track .toggle__thumb {
    transform: translateX(16px);
}

/* ── Modal ──────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal {
    background: #1A1A1A;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    width: 480px;
    max-width: 95vw;
}

.modal__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.modal__title {
    font-size: 15px;
    font-weight: 600;
    color: #FAFAFA;
}

.modal__close {
    background: none;
    border: none;
    color: #52525B;
    font-size: 16px;
    cursor: pointer;
}

.modal__body {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    margin-top: 8px;
}

.rate-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 9px 18px;
    background: #22D3EE;
    color: #0A0A0A;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-primary:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.btn-primary:not(:disabled):hover {
    opacity: 0.85;
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: 9px 16px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    color: #71717A;
    font-size: 13px;
    text-decoration: none;
    background: none;
    cursor: pointer;
    transition: all 200ms;
}

.btn-ghost:hover {
    color: #FAFAFA;
    border-color: rgba(255, 255, 255, 0.2);
}

.btn-ghost-sm {
    font-size: 12px;
    color: #52525B;
    background: none;
    border: 1px dashed rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 5px 12px;
    cursor: pointer;
    transition: all 200ms;
}

.btn-ghost-sm:hover {
    color: #FAFAFA;
    border-color: rgba(255, 255, 255, 0.2);
}
</style>
