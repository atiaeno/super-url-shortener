<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    ads: Array,
});

const showCreateModal = ref(false);
const editingAd = ref(null);
const countrySearch = ref('');

const createForm = useForm({
    name: '',
    format: 'banner',
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
});

const countries = [
    { code: 'US', name: 'United States' },
    { code: 'GB', name: 'United Kingdom' },
    { code: 'CA', name: 'Canada' },
    { code: 'AU', name: 'Australia' },
    { code: 'DE', name: 'Germany' },
    { code: 'FR', name: 'France' },
    { code: 'IT', name: 'Italy' },
    { code: 'ES', name: 'Spain' },
    { code: 'BR', name: 'Brazil' },
    { code: 'MX', name: 'Mexico' },
    { code: 'IN', name: 'India' },
    { code: 'JP', name: 'Japan' },
    { code: 'KR', name: 'South Korea' },
    { code: 'CN', name: 'China' },
    { code: 'RU', name: 'Russia' },
    { code: 'ZA', name: 'South Africa' },
];

const filteredCountries = computed(() => {
    return countries.filter(country => 
        country.name.toLowerCase().includes(countrySearch.value.toLowerCase()) ||
        country.code.toLowerCase().includes(countrySearch.value.toLowerCase())
    );
});

const createAd = () => {
    createForm.post(route('admin.ads.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const updateAd = () => {
    editForm.put(route('admin.ads.update', editingAd.value.id), {
        onSuccess: () => {
            editingAd.value = null;
            editForm.reset();
        }
    });
};

const deleteAd = (ad) => {
    if (confirm('Are you sure you want to delete this ad?')) {
        router.delete(route('admin.ads.destroy', ad.id));
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
};

const toggleCountry = (country) => {
    const form = editingAd.value ? editForm : createForm;
    const index = form.target_countries.indexOf(country.code);
    
    if (index > -1) {
        form.target_countries.splice(index, 1);
    } else {
        form.target_countries.push(country.code);
    }
};

const isCountrySelected = (country) => {
    const form = editingAd.value ? editForm : createForm;
    return form.target_countries.includes(country.code);
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        createForm.image = file;
    }
};
</script>

<template>
    <Head title="Advertising Management" />
    <AdminLayout>
        <div class="admin-page">
            <h1 class="admin-page__title">Advertising Management</h1>

            <div class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">Active Campaigns</h2>
                    <button @click="showCreateModal = true" class="btn-primary">
                        + Create Ad
                    </button>
                </div>

                <div v-if="!ads?.length" class="empty-state">
                    <p>No ads created yet.</p>
                </div>

                <div v-else class="ads-grid">
                    <div v-for="ad in ads" :key="ad.id" class="ad-card">
                        <div class="ad-card__header">
                            <h3 class="ad-card__title">{{ ad.name }}</h3>
                            <div class="ad-card__status">
                                <span class="status-badge" :class="{ 'status-active': ad.is_active, 'status-inactive': !ad.is_active }">
                                    {{ ad.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="ad-card__content">
                            <p class="ad-card__text">{{ ad.content }}</p>
                            <div class="ad-card__meta">
                                <span class="ad-card__format">{{ ad.format }}</span>
                                <span class="ad-card__countries">{{ ad.target_countries?.length || 0 }} countries</span>
                            </div>
                        </div>

                        <div class="ad-card__actions">
                            <button @click="openEditModal(ad)" class="btn-ghost-sm">Edit</button>
                            <button @click="deleteAd(ad)" class="btn-danger-sm">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Ad Modal -->
            <div v-if="showCreateModal" class="modal-backdrop">
                <div class="modal modal--large">
                    <div class="modal__header">
                        <h3 class="modal__title">Create Advertisement</h3>
                        <button @click="showCreateModal = false" class="modal__close">×</button>
                    </div>
                    <form @submit.prevent="createAd">
                        <div class="modal__body">
                            <div class="form-grid">
                                <div class="field">
                                    <label class="field__label">Ad Name</label>
                                    <input v-model="createForm.name" type="text" class="field__input" required />
                                </div>

                                <div class="field">
                                    <label class="field__label">Format</label>
                                    <select v-model="createForm.format" class="field__input" required>
                                        <option value="banner">Banner</option>
                                        <option value="popup">Popup</option>
                                        <option value="interstitial">Interstitial</option>
                                    </select>
                                </div>

                                <div class="field field--full">
                                    <label class="field__label">Content</label>
                                    <textarea v-model="createForm.content" class="field__input" rows="3" required></textarea>
                                </div>

                                <div class="field field--full">
                                    <label class="field__label">Target URL</label>
                                    <input v-model="createForm.target_url" type="url" class="field__input" required />
                                </div>

                                <div class="field field--full">
                                    <label class="field__label">Target Countries</label>
                                    <input v-model="countrySearch" type="text" class="field__input" placeholder="Search countries..." />
                                    <div class="countries-grid">
                                        <div v-for="country in filteredCountries" :key="country.code" 
                                            @click="toggleCountry(country)"
                                            class="country-chip"
                                            :class="{ 'country-chip--selected': isCountrySelected(country) }">
                                            {{ country.name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="field__label">Countdown (seconds)</label>
                                    <input v-model.number="createForm.countdown_seconds" type="number" min="1" max="30" class="field__input" />
                                </div>

                                <div class="field">
                                    <label class="field__label">Image (optional)</label>
                                    <input @change="handleImageUpload" type="file" accept="image/*" class="field__input" />
                                </div>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" @click="showCreateModal = false" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-primary" :disabled="createForm.processing">
                                Create Ad
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Ad Modal -->
            <div v-if="editingAd" class="modal-backdrop">
                <div class="modal modal--large">
                    <div class="modal__header">
                        <h3 class="modal__title">Edit Advertisement</h3>
                        <button @click="editingAd = null" class="modal__close">×</button>
                    </div>
                    <form @submit.prevent="updateAd">
                        <div class="modal__body">
                            <div class="form-grid">
                                <div class="field">
                                    <label class="field__label">Ad Name</label>
                                    <input v-model="editForm.name" type="text" class="field__input" required />
                                </div>

                                <div class="field">
                                    <label class="field__label">Status</label>
                                    <select v-model="editForm.is_active" class="field__input">
                                        <option :value="true">Active</option>
                                        <option :value="false">Inactive</option>
                                    </select>
                                </div>

                                <div class="field field--full">
                                    <label class="field__label">Content</label>
                                    <textarea v-model="editForm.content" class="field__input" rows="3" required></textarea>
                                </div>

                                <div class="field field--full">
                                    <label class="field__label">Target URL</label>
                                    <input v-model="editForm.target_url" type="url" class="field__input" required />
                                </div>

                                <div class="field field--full">
                                    <label class="field__label">Target Countries</label>
                                    <input v-model="countrySearch" type="text" class="field__input" placeholder="Search countries..." />
                                    <div class="countries-grid">
                                        <div v-for="country in filteredCountries" :key="country.code" 
                                            @click="toggleCountry(country)"
                                            class="country-chip"
                                            :class="{ 'country-chip--selected': isCountrySelected(country) }">
                                            {{ country.name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="field__label">Countdown (seconds)</label>
                                    <input v-model.number="editForm.countdown_seconds" type="number" min="1" max="30" class="field__input" />
                                </div>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" @click="editingAd = null" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-primary" :disabled="editForm.processing">
                                Update Ad
                            </button>
                        </div>
                    </form>
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

.admin-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.section-title {
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--muted);
}

.btn-primary {
    padding: 8px 16px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-primary:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
    font-style: italic;
}

.ads-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.ad-card {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    transition: var(--transition);
}

.ad-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.ad-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.ad-card__title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.status-badge {
    font-size: 10px;
    padding: 4px 8px;
    border-radius: var(--radius);
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.ad-card__content {
    margin-bottom: 16px;
}

.ad-card__text {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink-soft);
    margin: 0 0 8px 0;
    line-height: 1.5;
}

.ad-card__meta {
    display: flex;
    gap: 12px;
    font-size: 11px;
    color: var(--muted);
}

.ad-card__format {
    text-transform: capitalize;
}

.ad-card__actions {
    display: flex;
    gap: 8px;
}

.btn-ghost-sm {
    padding: 4px 8px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink-soft);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost-sm:hover {
    background: var(--surface);
    color: var(--ink);
}

.btn-danger-sm {
    padding: 4px 8px;
    background: var(--red);
    border: 1px solid var(--red);
    border-radius: var(--radius);
    color: var(--surface);
    font-size: 11px;
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-danger-sm:hover {
    background: var(--red-dark);
    border-color: var(--red-dark);
}

/* Modal Styles */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 26, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.modal--large {
    max-width: 800px;
}

.modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
}

.modal__title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
}

.modal__close {
    width: 32px;
    height: 32px;
    background: transparent;
    border: none;
    color: var(--muted);
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius);
    transition: var(--transition);
}

.modal__close:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.modal__body {
    padding: 24px;
}

.modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid var(--border);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field--full {
    grid-column: 1 / -1;
}

.field__label {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
}

.field__input {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--ink);
    font-family: var(--font-body);
    font-size: 14px;
    transition: var(--transition);
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.countries-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
    max-height: 200px;
    overflow-y: auto;
    padding: 8px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.country-chip {
    padding: 4px 8px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 11px;
    cursor: pointer;
    transition: var(--transition);
}

.country-chip:hover {
    background: var(--surface-2);
}

.country-chip--selected {
    background: var(--red);
    color: var(--surface);
    border-color: var(--red);
}

.btn-ghost {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--ink);
    font-family: var(--font-display);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
}

.btn-ghost:hover {
    background: var(--surface-2);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .ads-grid {
        grid-template-columns: 1fr;
    }
}
</style>
