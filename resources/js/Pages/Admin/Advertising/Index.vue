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

// Toast state
const toast = ref({
    show: false,
    type: 'success',
    message: ''
});

const showToast = (type, message) => {
    toast.value = { type, message, show: true };
    setTimeout(() => {
        toast.value.show = false;
    }, 4000);
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

// Bulk selection functions
const toggleAdSelection = (adId) => {
    const index = selectedAds.value.indexOf(adId);
    if (index > -1) {
        selectedAds.value.splice(index, 1);
    } else {
        selectedAds.value.push(adId);
    }
};

const toggleSelectAll = () => {
    if (selectedAds.value.length === props.ads.length) {
        selectedAds.value = [];
    } else {
        selectedAds.value = props.ads.map(ad => ad.id);
    }
};

const isAdSelected = (adId) => {
    return selectedAds.value.includes(adId);
};

const isAllSelected = () => {
    return props.ads.length > 0 && selectedAds.value.length === props.ads.length;
};

const bulkDeleteAds = () => {
    if (selectedAds.value.length === 0) return;
    showBulkDeleteModal.value = true;
};

const confirmBulkDelete = () => {
    router.delete(route('admin.advertising.bulkDelete'), {
        data: { ids: selectedAds.value },
        onSuccess: () => {
            selectedAds.value = [];
            showBulkDeleteModal.value = false;
        }
    });
};

const cancelBulkDelete = () => {
    showBulkDeleteModal.value = false;
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

    <!-- Toast Notification -->
    <Teleport to="body">
        <div v-if="toast.show" class="toast" :class="`toast--${toast.type}`">
            <span v-if="toast.type === 'success'" class="toast__icon">✓</span>
            <span v-else class="toast__icon">✕</span>
            <span class="toast__message">{{ toast.message }}</span>
            <button @click="toast.show = false" class="toast__close">×</button>
        </div>
    </Teleport>

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
                    <div class="header-actions">
                        <button v-if="selectedAds.length > 0" @click="bulkDeleteAds" class="btn-bulk-delete">
                            <span class="material-icons btn-icon">{{ icons.delete }}</span>Delete Selected ({{
                                selectedAds.length }})
                        </button>
                        <button @click="showCreateModal = true" class="btn-create">
                            <span class="material-icons btn-icon">{{ icons.plus }}</span>Create Promotion
                        </button>
                    </div>
                </div>
                <div class="table-card">
                    <div v-if="!ads?.length" class="empty-state">
                        <div class="empty-state__icon">
                            <span class="material-icons">{{ icons.advertising }}</span>
                        </div>
                        <p class="empty-state__title">No promotions created yet</p>
                        <p class="empty-state__text">Start by creating your first advertising promotion.</p>
                    </div>

                    <div v-else class="table-wrapper">
                        <table class="promotions-table">
                            <thead>
                                <tr>
                                    <th class="table-header checkbox-column">
                                        <input type="checkbox" :checked="isAllSelected()" @change="toggleSelectAll"
                                            class="select-all-checkbox">
                                    </th>
                                    <th class="table-header promotion-name-column">Promotion Name</th>
                                    <th class="table-header">Format</th>
                                    <th class="table-header">Placement</th>
                                    <th class="table-header">Status</th>
                                    <th class="table-header">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ad in ads" :key="ad.id" class="table-row"
                                    :class="{ 'row-selected': isAdSelected(ad.id) }">
                                    <td class="table-cell checkbox-column">
                                        <input type="checkbox" :checked="isAdSelected(ad.id)"
                                            @change="toggleAdSelection(ad.id)" class="ad-checkbox">
                                    </td>
                                    <td class="table-cell">
                                        <div class="promotion-cell">
                                            <div class="promotion-icon"
                                                :class="ad.is_active ? 'promotion-icon--active' : 'promotion-icon--inactive'">
                                                <span class="material-icons">{{ icons[ad.format] }}</span>
                                            </div>
                                            <div class="promotion-details">
                                                <div class="promotion-name">{{ ad.name }}</div>
                                                <div class="promotion-meta" v-if="ad.target_url">{{ ad.target_url }}
                                                </div>
                                            </div>
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
                                        <span class="promotion-status"
                                            :class="ad.is_active ? 'promotion-status--active' : 'promotion-status--inactive'">
                                            {{ ad.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="table-cell">
                                        <div class="promotion-actions">
                                            <button @click="openEditModal(ad)" class="btn-icon btn-icon--edit"
                                                title="Edit Promotion">
                                                <span class="material-icons">{{ icons.edit }}</span>
                                            </button>
                                            <button @click="deleteAd(ad)" class="btn-icon btn-icon--delete"
                                                title="Delete Promotion">
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
                                    <input v-model.number="createForm.countdown_seconds" type="number"
                                        class="field__input" min="1" max="60" />
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

            <!-- Bulk Delete Modal -->
            <div v-if="showBulkDeleteModal" class="modal-overlay" @click="cancelBulkDelete">
                <div class="modal-content" @click.stop>
                    <div class="modal-header">
                        <h3 class="modal-title">Confirm Bulk Delete</h3>
                        <button @click="cancelBulkDelete" class="modal-close">
                            <span class="material-icons">close</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="modal-message">
                            Are you sure you want to delete <strong>{{ selectedAds.length }}</strong> promotion(s)?
                        </p>
                        <p class="modal-warning">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button @click="cancelBulkDelete" class="btn btn-secondary">Cancel</button>
                        <button @click="confirmBulkDelete" class="btn btn-danger">
                            <span class="material-icons">{{ icons.delete }}</span>
                            Delete {{ selectedAds.length }} Promotion(s)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
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
    padding: 2px 13px;
    border-radius: 4px;
    font-family: 'Oswald';
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.no-data {
    text-align: center;
    color: var(--muted);
    font-size: 14px;
    padding: 40px 0;
}

.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-wrapper table {
    min-width: 800px;
}

@media (max-width: 768px) {
    .table-wrapper {
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
    }

    .table-wrapper table {
        min-width: 100%;
    }

    .promotions-table th,
    .promotions-table td {
        padding: 12px 10px;
        font-size: 13px;
    }

    .promotion-name-column {
        width: 200px;
        min-width: 200px;
    }
}

.promotions-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
}

.promotions-table th {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    text-align: left;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface-2);
    white-space: nowrap;
}

.promotion-name-column {
    width: 35%;
    min-width: 250px;
}

.promotions-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.promotions-table tr:last-child td {
    border-bottom: none;
}

.promotions-table tr:hover td {
    background: var(--surface-2);
}

/* Promotion Cell Styles */
.promotion-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.promotion-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.promotion-icon--active {
    background: #dcfce7;
    color: #16a34a;
}

.promotion-icon--inactive {
    background: #f3f4f6;
    color: var(--muted);
}

.promotion-icon .material-icons {
    font-size: 16px;
}

.promotion-details {
    display: flex;
    flex-direction: column;
}

.promotion-name {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 2px;
}

.promotion-meta {
    font-family: var(--font-body);
    font-size: 10px;
    font-style: italic;
    color: var(--muted);
}

/* Promotion Status */
.promotion-status {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: var(--radius);
    display: inline-block;
}

.promotion-status--active {
    background: #dcfce7;
    color: #16a34a;
}

.promotion-status--inactive {
    background: #f3f4f6;
    color: var(--muted);
}

/* Promotion Actions - Using same as tier-actions */
.promotion-actions {
    display: flex;
    gap: 6px;
}

.btn-icon {

    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;

    color: #fff;
    cursor: pointer;
    transition: var(--transition);
}

.btn-icon:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.btn-icon .material-icons {
    font-size: 14px;
}

.btn-icon--edit {
    background: #fef9f0;
    color: var(--gold);
    border-color: #fde68a;
}

.btn-icon--edit:hover {
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
    color: var(--red-dark);
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}

.empty-state__icon {
    width: 48px;
    height: 48px;
    background: var(--surface-2);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    color: var(--muted);
}

.empty-state__title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 8px 0;
}

.empty-state__text {
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

/* Ads Table */
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

.table-header:nth-child(2) {
    width: 35%;
}

.table-header:nth-child(3) {
    width: 15%;
    min-width: 100px;
}

.table-header:nth-child(4) {
    width: 20%;
    min-width: 120px;
}

.table-header:nth-child(5) {
    width: 10%;
    min-width: 80px;
}

.table-header:nth-child(6) {
    width: 10%;
    min-width: 80px;
    text-align: center;
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

/* Bulk Selection Styles */
.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn-bulk-delete[data-v-ca81ac89] {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 2px 13px;
    background: var(--red);
    color: white;
    border: none;
    border-radius: 4px;
    font-family: 'Oswald';
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.btn-bulk-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.checkbox-column {
    width: 30px !important;
    text-align: center !important;
    padding: 8px 4px !important;
}

.table-header.checkbox-column {
    width: 30px !important;
    padding: 8px 4px !important;
}

.table-cell.checkbox-column {
    width: 30px !important;
    padding: 8px 4px !important;
}

.select-all-checkbox,
.ad-checkbox {
    width: 14px !important;
    height: 14px !important;
    accent-color: var(--primary);
    cursor: pointer;
}

.checkbox-column input[type="checkbox"] {
    width: 14px !important;
    height: 14px !important;
}

.table-header input[type="checkbox"] {
    width: 14px !important;
    height: 14px !important;
}

.row-selected {
    background-color: rgba(59, 130, 246, 0.1) !important;
}

.row-selected:hover {
    background-color: rgba(59, 130, 246, 0.15) !important;
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

    .header-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .checkbox-column {
        width: 30px;
    }
}

/* Bulk Delete Modal Styles */
.modal-overlay {
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

.modal-content {
    background: white;
    border-radius: var(--radius);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
}

.modal-title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: var(--muted);
    border-radius: 4px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: var(--border);
    color: var(--ink);
}

.modal-body {
    padding: 24px;
}

.modal-message {
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--ink);
    margin: 0 0 12px 0;
    line-height: 1.5;
}

.modal-warning {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    color: #e74c3c;
    margin: 0;
    font-weight: 500;
}

.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 20px 24px;
    border-top: 1px solid var(--border);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-danger:hover {
    background: #c82333;
}

/* Toast Notifications */
.toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-radius: 8px;
    font-family: var(--font-body);
    font-size: 14px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast--success {
    background: #10b981;
    color: white;
}

.toast--error {
    background: #ef4444;
    color: white;
}

.toast__icon {
    font-size: 16px;
    font-weight: bold;
}

.toast__message {
    flex: 1;
}

.toast__close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    opacity: 0.8;
    padding: 0;
    line-height: 1;
}

.toast__close:hover {
    opacity: 1;
}
</style>