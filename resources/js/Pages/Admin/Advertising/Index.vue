<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CountrySelector from '@/Components/CountrySelector.vue';

const props = defineProps({
    ads: Array,
});

const showCreateModal = ref(false);
const editingAd = ref(null);
const selectedAds = ref([]);
const showBulkDeleteModal = ref(false);

const icons = {
    advertising: 'campaign',
    banner: 'image',
    interstitial: 'timer',
    edit: 'edit',
    delete: 'delete',
    plus: 'add',
};

const statCards = computed(() => [
    { label: 'Total Promotions', value: props.ads?.length || 0 },
    { label: 'Active', value: props.ads?.filter(ad => ad.is_active).length || 0 },
    { label: 'Inactive', value: props.ads?.filter(ad => !ad.is_active).length || 0 },
    { label: 'Banner Promotions', value: props.ads?.filter(ad => ad.format === 'banner').length || 0 },
]);

const createForm = useForm({
    name: '',
    format: 'banner',
    placement: 'redirect',
    content: '',
    target_url: '',
    target_countries: [],
    countdown_seconds: 5,
    image: null,
});

const editForm = useForm({
    name: '',
    content: '',
    target_url: '',
    target_countries: [],
    countdown_seconds: 5,
    is_active: true,
    placement: 'redirect',
});


const createAd = () => {
    createForm.post(route('admin.advertising.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const updateAd = () => {
    editForm.patch(route('admin.advertising.update', editingAd.value.id), {
        onSuccess: () => {
            editingAd.value = null;
            editForm.reset();
        }
    });
};

const deleteAd = (ad) => {
    if (confirm('Are you sure you want to delete this ad?')) {
        router.delete(route('admin.advertising.destroy', ad.id));
    }
};

const openEditModal = (ad) => {
    editingAd.value = ad;
    editForm.name = ad.name;
    editForm.content = ad.content;
    editForm.target_url = ad.target_url;
    editForm.target_countries = ad.target_countries || [];
    editForm.countdown_seconds = ad.countdown_seconds || 5;
    editForm.is_active = ad.is_active;
    editForm.placement = ad.placement || 'redirect';
};


const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        createForm.image = file;
    }
};

const getPlacementName = (placement) => {
    const placements = {
        redirect: 'Redirect Page',
        header: 'Header',
        footer: 'Footer',
        left_side: 'Left Side',
        right_side: 'Right Side',
        before_counter: 'Before Counter',
        under_counter: 'Under Counter',
        above_button: 'Above Button',
        under_button: 'Under Button',
        popup: 'Popup'
    };
    return placements[placement] || placement;
};
</script>

<template>

    <Head title="Advertising Management" />
    <AdminLayout>
        <template #header><span class="material-icons">{{ icons.advertising }}</span> Advertising Management</template>

        <div class="page-content">

            <!-- Stats Row -->
            <div class="stats-row">
                <div v-for="stat in statCards" :key="stat.label" class="stat-box">
                    <span class="stat-value">{{ stat.value.toLocaleString() }}</span>
                    <span class="stat-label">{{ stat.label }}</span>
                </div>
            </div>

            <!-- Ads Table -->
            <div class="table-section">
                <div class="section-header">
                    <span class="header-icon"><span class="material-icons">{{ icons.advertising }}</span></span>
                    <h3 class="section-title">All Promotions</h3>
                    <button @click="showCreateModal = true" class="btn-create">
                        <span class="material-icons btn-icon">{{ icons.plus }}</span>Create Promotion
                    </button>
                </div>
                <div class="table-card">
                    <div v-if="!ads?.length" class="no-data">No promotions created yet</div>
                    <table v-else class="promotions-table">
                        <thead>
                            <tr>
                                <th class="table-header">Promotion Name</th>
                                <th class="table-header">Format</th>
                                <th class="table-header">Placement</th>
                                <th class="table-header">Status</th>
                                <th class="table-header">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ad in ads" :key="ad.id" class="table-row">
                                <td class="table-cell">
                                    <div class="ad-info">
                                        <div class="ad-name">{{ ad.name }}</div>
                                        <div class="ad-url" v-if="ad.target_url">{{ ad.target_url }}</div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <div class="format-badge">
                                        <span class="material-icons format-icon">{{ icons[ad.format] }}</span>
                                        {{ ad.format }}
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <div class="placement-info">
                                        <div class="placement-type">{{ getPlacementName(ad.placement) }}</div>
                                        <div class="placement-targeting" v-if="ad.target_countries?.length">
                                            {{ ad.target_countries.length }} countries
                                        </div>
                                        <div class="placement-targeting" v-else>
                                            Global
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <span class="status-badge"
                                        :class="{ 'status-active': ad.is_active, 'status-inactive': !ad.is_active }">
                                        {{ ad.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <div class="actions-cell">
                                        <button @click="openEditModal(ad)" class="btn-action" title="Edit">
                                            <span class="material-icons">{{ icons.edit }}</span>
                                        </button>
                                        <button @click="deleteAd(ad)" class="btn-action btn-danger" title="Delete">
                                            <span class="material-icons">{{ icons.delete }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Ad Modal -->
        <div v-if="showCreateModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Create Promotion</h3>
                    <button @click="showCreateModal = false" class="modal__close">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <form @submit.prevent="createAd">
                    <div class="modal__body">
                        <div class="form-grid">
                            <div class="field">
                                <label class="field__label">Promotion Name</label>
                                <input v-model="createForm.name" type="text" class="field__input" required />
                            </div>

                            <div class="field">
                                <label class="field__label">Format</label>
                                <select v-model="createForm.format" class="field__input" required>
                                    <option value="banner">Banner</option>
                                    <option value="interstitial">Interstitial</option>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Placement</label>
                                <select v-model="createForm.placement" class="field__input" required>
                                    <option value="redirect">Redirect Page</option>
                                    <option value="header">Header</option>
                                    <option value="footer">Footer</option>
                                    <option value="left_side">Left Side</option>
                                    <option value="right_side">Right Side</option>
                                    <option value="before_counter">Before Counter</option>
                                    <option value="under_counter">Under Counter</option>
                                    <option value="above_button">Above Button</option>
                                    <option value="under_button">Under Button</option>
                                    <option value="popup">Popup</option>
                                </select>
                            </div>

                            <div class="field field--full">
                                <label class="field__label">Content</label>
                                <textarea v-model="createForm.content" class="field__input" rows="3"
                                    placeholder="HTML content or image URL"></textarea>
                            </div>

                            <div class="field field--full">
                                <label class="field__label">Target URL</label>
                                <input v-model="createForm.target_url" type="url" class="field__input"
                                    placeholder="https://example.com" />
                            </div>

                            <div class="field" v-if="createForm.format === 'interstitial'">
                                <label class="field__label">Countdown (seconds)</label>
                                <input v-model.number="createForm.countdown_seconds" type="number" class="field__input"
                                    min="1" max="60" />
                            </div>

                            <div class="field field--full">
                                <label class="field__label">Target Countries (Optional)</label>
                                <CountrySelector v-model="createForm.target_countries" />
                            </div>
                        </div>
                    </div>
                    <div class="modal__footer">
                        <button type="button" @click="showCreateModal = false" class="btn-ghost">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="createForm.processing">
                            Create Promotion
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Ad Modal -->
        <div v-if="editingAd" class="modal-backdrop">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Edit Promotion</h3>
                    <button @click="editingAd = null" class="modal__close">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <form @submit.prevent="updateAd">
                    <div class="modal__body">
                        <div class="form-grid">
                            <div class="field">
                                <label class="field__label">Promotion Name</label>
                                <input v-model="editForm.name" type="text" class="field__input" required />
                            </div>

                            <div class="field">
                                <label class="field__label">Content</label>
                                <textarea v-model="editForm.content" class="field__input" rows="3"
                                    placeholder="HTML content or image URL"></textarea>
                            </div>

                            <div class="field">
                                <label class="field__label">Placement</label>
                                <select v-model="editForm.placement" class="field__input" required>
                                    <option value="redirect">Redirect Page</option>
                                    <option value="header">Header</option>
                                    <option value="footer">Footer</option>
                                    <option value="left_side">Left Side</option>
                                    <option value="right_side">Right Side</option>
                                    <option value="before_counter">Before Counter</option>
                                    <option value="under_counter">Under Counter</option>
                                    <option value="above_button">Above Button</option>
                                    <option value="under_button">Under Button</option>
                                    <option value="popup">Popup</option>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Target URL</label>
                                <input v-model="editForm.target_url" type="url" class="field__input"
                                    placeholder="https://example.com" />
                            </div>

                            <div class="field">
                                <label class="field__label">Status</label>
                                <label class="checkbox-label">
                                    <input v-model="editForm.is_active" type="checkbox" />
                                    <span>Active</span>
                                </label>
                            </div>

                            <div class="field field--full">
                                <label class="field__label">Target Countries (Optional)</label>
                                <CountrySelector v-model="editForm.target_countries" />
                            </div>
                        </div>
                    </div>
                    <div class="modal__footer">
                        <button type="button" @click="editingAd = null" class="btn-ghost">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="editForm.processing">
                            Update Promotion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

:root {
    --font-display: 'Oswald', sans-serif;
    --red: #e74c3c;
    --ink: #000000;
    --muted: #333333;
    --border: #e5e5e5;
    --surface: #fff;
    --radius: 4px;
}

.page-content {
    max-width: 1000px;
    margin: 0 auto;
    padding: 24px;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    text-align: center;
}

.stat-value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 600;
    color: var(--ink);
    display: block;
    margin-bottom: 4px;
}

.stat-label {
    font-family: var(--font-display);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
}

/* Table Section */
.table-section {
    margin-bottom: 32px;
}

.section-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--surface);
}

.header-icon {
    display: flex;
    align-items: center;
    color: var(--red);
}

.section-title {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--ink);
    margin: 0;
    flex: 1;
}

.material-icons {
    font-family: 'Material Icons';
    font-weight: normal;
    font-style: normal;
    font-size: 18px;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    -webkit-font-feature-settings: 'liga';
    font-feature-settings: 'liga';
    -webkit-font-smoothing: antialiased;
}

.btn-icon {
    font-size: 16px;
    margin-right: 6px;
    vertical-align: middle;
}

.btn-create {
    background: var(--red);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-create:hover {
    background: #c0392b;
}

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    width: 100%;
}

.no-data {
    text-align: center;
    color: var(--muted);
    font-size: 14px;
    padding: 40px 0;
}

/* Table Styles */
.ads-table {
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
    background: #f8f9fa;
}

.table-header:nth-child(1) {
    width: 30%;
}

.table-header:nth-child(2) {
    width: 15%;
}

.table-header:nth-child(3) {
    width: 25%;
}

.table-header:nth-child(4) {
    width: 15%;
}

.table-header:nth-child(5) {
    width: 15%;
}

.table-row {
    border-bottom: 1px solid var(--border);
    transition: background 200ms;
}

.table-row:hover {
    background: #f8f9fa;
}

.table-cell {
    padding: 12px 16px;
    vertical-align: middle;
}

.ad-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ad-name {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
}

.ad-url {
    font-family: 'DM Sans', sans-serif;
    font-size: 10px;
    color: var(--muted);
    word-break: break-all;
}

.format-badge {
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: var(--font-display);
    font-size: 10px;
    text-transform: uppercase;
    color: var(--ink-soft);
}

.format-icon {
    font-size: 14px;
    margin-right: 4px;
    vertical-align: middle;
}

.placement-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.placement-type {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    color: var(--ink);
    text-transform: uppercase;
}

.placement-targeting {
    font-family: 'DM Sans', sans-serif;
    font-size: 9px;
    color: var(--muted);
}

.status-badge {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: var(--radius);
    letter-spacing: 0.5px;
    display: inline-block;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.actions-cell {
    display: flex;
    gap: 6px;
}

.btn-action {
    background: transparent;
    border: 1px solid var(--border);
    padding: 4px 8px;
    border-radius: var(--radius);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-action:hover {
    background: #f8f9fa;
}

.btn-danger {
    color: var(--red);
    border-color: var(--red);
}

.btn-danger:hover {
    background: var(--red);
    color: white;
}


/* Modal Styles */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal__header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal__title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.modal__close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--muted);
    padding: 4px;
    border-radius: var(--radius);
    transition: all 0.2s;
}

.modal__close:hover {
    color: var(--ink);
    background: var(--surface-2);
}

.modal__body {
    padding: 20px;
}

.modal__footer {
    padding: 20px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
}

.field--full {
    grid-column: 1 / -1;
}

.field__label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink);
    margin-bottom: 6px;
}

.field__input {
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    transition: all 0.2s;
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.promotions-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    background: var(--surface);
    table-layout: fixed;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    color: var(--ink);
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
}

.btn-ghost {
    background: transparent;
    border: 1px solid var(--border);
    padding: 6px 12px;
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-ghost:hover {
    background: var(--surface-2);
}

.btn-primary {
    background: var(--red);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #c0392b;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }

    .page-content {
        padding: 16px;
    }
}
</style>