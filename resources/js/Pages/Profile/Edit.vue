<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
    connectedProviders: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// ── Profile Information Form ───────────────────────────────────────────────
const profileForm = useForm({
    name: user.value.name,
    email: user.value.email,
});

const submitProfile = () => {
    profileForm.patch(route('profile.update'));
};

// ── Password Form ──────────────────────────────────────────────────────────
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

// ── Avatar upload ───────────────────────────────────────────────────────────
const avatarForm = useForm({ avatar: null });
const avatarPreview = ref(null);
const fileInput = ref(null);
const avatarSuccess = computed(() => props.status === 'avatar-updated');

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const submitAvatar = () => {
    avatarForm.post(route('profile.avatar'), {
        forceFormData: true,
        onSuccess: () => { avatarPreview.value = null; },
    });
};

// ── Delete Account ───────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deleteForm = useForm({ password: '' });

const confirmDelete = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => { showDeleteModal.value = false; deleteForm.reset(); },
        onError: () => { deleteForm.reset('password'); },
    });
};

// ── OAuth connections ───────────────────────────────────────────────────────
const providers = [
    { key: 'google', label: 'Google', icon: 'G' },
    { key: 'github', label: 'GitHub', icon: 'GH' },
    { key: 'facebook', label: 'Facebook', icon: 'fb' },
];

const isConnected = (provider) => props.connectedProviders.includes(provider);

const disconnect = (provider) => {
    if (!confirm(`Disconnect ${provider}?`)) return;
    router.delete(route('social.disconnect', provider));
};

// ── Tabs ───────────────────────────────────────────────────────────────────────
const activeTab = ref('profile');

const tabs = [
    { id: 'profile', label: 'Profile', icon: 'badge' },
    { id: 'security', label: 'Security', icon: 'lock' },
    { id: 'connections', label: 'Connections', icon: 'link' },
    { id: 'api', label: 'API Tokens', icon: 'vpn_key' },
    { id: 'danger', label: 'Danger Zone', icon: 'warning' },
];
</script>

<template>

    <Head title="Profile Settings" />

    <AuthenticatedLayout>
        <template #header><span class="material-icons">person</span> Profile Settings</template>

        <div class="page-content">
            <!-- Tab Navigation -->
            <div class="tabs-nav">
                <button v-for="tab in tabs" :key="tab.id" :class="['tab-btn', { active: activeTab === tab.id }]"
                    @click="activeTab = tab.id">
                    <span class="material-icons">{{ tab.icon }}</span>
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Profile Tab -->
                <div v-if="activeTab === 'profile'" class="card">
                    <div class="card-header">
                        <span class="material-icons">badge</span>
                        <h3>Profile Information</h3>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitProfile" class="form-stack">
                            <div class="field">
                                <label class="field__label">Name</label>
                                <input v-model="profileForm.name" type="text" required autocomplete="name"
                                    class="field__input" placeholder="Your name" />
                                <span v-if="profileForm.errors.name" class="field__error">{{ profileForm.errors.name
                                    }}</span>
                            </div>
                            <div class="field">
                                <label class="field__label">Email Address</label>
                                <input v-model="profileForm.email" type="email" required autocomplete="username"
                                    class="field__input" placeholder="your@email.com" />
                                <span v-if="profileForm.errors.email" class="field__error">{{ profileForm.errors.email
                                    }}</span>
                            </div>
                            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="verify-notice">
                                <p>Your email address is unverified.</p>
                                <a :href="route('verification.send')" method="post" as="button">Resend verification
                                    email</a>
                                <span v-if="status === 'verification-link-sent'" class="success-msg">Verification link
                                    sent!</span>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary" :disabled="profileForm.processing">
                                    {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                                <span v-if="profileForm.recentlySuccessful" class="success-msg">Saved!</span>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Profile Photo (only in Profile tab) -->
                <div v-if="activeTab === 'profile'" class="card">
                    <div class="card-header">
                        <span class="material-icons">photo_camera</span>
                        <h3>Profile Photo</h3>
                    </div>
                    <div class="card-body">
                        <div class="photo-row">
                            <img :src="avatarPreview ?? user.avatar ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=128&background=1a1a1a&color=fff`"
                                alt="Avatar" class="avatar-lg" />
                            <div class="photo-actions">
                                <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/gif"
                                    class="hidden" @change="onFileChange" />
                                <button type="button" @click="fileInput.click()" class="btn-secondary">Choose
                                    Photo</button>
                                <button v-if="avatarForm.avatar" type="button" @click="submitAvatar"
                                    :disabled="avatarForm.processing" class="btn-primary">
                                    {{ avatarForm.processing ? 'Saving…' : 'Save Photo' }}
                                </button>
                            </div>
                        </div>
                        <p class="field__hint">JPG, PNG, or GIF. Max 2MB. Saved at 64, 128, and 256px.</p>
                        <p v-if="avatarForm.errors.avatar" class="field__error">{{ avatarForm.errors.avatar }}</p>
                        <p v-if="avatarSuccess" class="success-msg">Photo updated!</p>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div v-if="activeTab === 'security'" class="tab-panel">
                <div class="card">
                    <div class="card-header">
                        <span class="material-icons">lock</span>
                        <h3>Update Password</h3>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="updatePassword" class="form-stack">
                            <div class="field">
                                <label class="field__label">Current Password</label>
                                <input v-model="passwordForm.current_password" type="password"
                                    autocomplete="current-password" class="field__input" placeholder="••••••••" />
                                <span v-if="passwordForm.errors.current_password" class="field__error">{{
                                    passwordForm.errors.current_password }}</span>
                            </div>
                            <div class="field">
                                <label class="field__label">New Password</label>
                                <input v-model="passwordForm.password" type="password" autocomplete="new-password"
                                    class="field__input" placeholder="••••••••" />
                                <span v-if="passwordForm.errors.password" class="field__error">{{
                                    passwordForm.errors.password }}</span>
                            </div>
                            <div class="field">
                                <label class="field__label">Confirm Password</label>
                                <input v-model="passwordForm.password_confirmation" type="password"
                                    autocomplete="new-password" class="field__input" placeholder="••••••••" />
                                <span v-if="passwordForm.errors.password_confirmation" class="field__error">{{
                                    passwordForm.errors.password_confirmation }}</span>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary" :disabled="passwordForm.processing">
                                    {{ passwordForm.processing ? 'Saving...' : 'Update Password' }}
                                </button>
                                <span v-if="passwordForm.recentlySuccessful" class="success-msg">Password
                                    updated!</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Connections Tab -->
            <div v-if="activeTab === 'connections'" class="tab-panel">
                <div class="card">
                    <div class="card-header">
                        <span class="material-icons">link</span>
                        <h3>Connected Accounts</h3>
                    </div>
                    <div class="card-body">
                        <div class="providers-list">
                            <div v-for="p in providers" :key="p.key" class="provider-row">
                                <span class="provider-label">{{ p.label }}</span>
                                <div class="provider-actions">
                                    <span :class="['status-badge', isConnected(p.key) ? 'connected' : 'disconnected']">
                                        {{ isConnected(p.key) ? 'Connected' : 'Not connected' }}
                                    </span>
                                    <a v-if="!isConnected(p.key)" :href="route('social.redirect', p.key)"
                                        class="btn-secondary btn-sm">Connect</a>
                                    <button v-else @click="disconnect(p.key)"
                                        class="btn-ghost btn-sm">Disconnect</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Tab -->
            <div v-if="activeTab === 'api'" class="tab-panel">
                <div class="card">
                    <div class="card-header">
                        <span class="material-icons">vpn_key</span>
                        <h3>API Access</h3>
                    </div>
                    <div class="card-body">
                        <p class="api-desc">Generate API tokens to access your links programmatically via our REST API.
                        </p>
                        <Link :href="route('profile.api-tokens')" class="btn-secondary">
                            <span class="material-icons">settings</span>
                            Manage API Tokens
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Danger Zone Tab -->
            <div v-if="activeTab === 'danger'" class="tab-panel">
                <div class="card card-danger">
                    <div class="card-header">
                        <span class="material-icons">warning</span>
                        <h3>Delete Account</h3>
                    </div>
                    <div class="card-body">
                        <p class="danger-text">Once your account is deleted, all of its resources and data will be
                            permanently
                            deleted.</p>
                        <button @click="showDeleteModal = true" class="btn-danger">Delete Account</button>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
                <div class="modal">
                    <h3 class="modal-title">Delete Account</h3>
                    <p class="modal-desc">Enter your password to confirm. This action cannot be undone.</p>
                    <div class="field">
                        <label class="field__label">Password</label>
                        <input v-model="deleteForm.password" type="password" class="field__input"
                            placeholder="Enter password" @keyup.enter="confirmDelete" />
                        <span v-if="deleteForm.errors.password" class="field__error">{{ deleteForm.errors.password
                        }}</span>
                    </div>
                    <div class="modal-actions">
                        <button @click="showDeleteModal = false; deleteForm.reset();" class="btn-ghost">Cancel</button>
                        <button @click="confirmDelete" :disabled="deleteForm.processing" class="btn-danger">
                            {{ deleteForm.processing ? 'Deleting...' : 'Delete Account' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');

.page-content {
    max-width: 700px;
    margin: 0 auto;
    padding: 24px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Tabs Navigation */
.tabs-nav {
    display: flex;
    gap: 4px;
    background: #f5f5f5;
    padding: 4px;
    border-radius: 10px;
    overflow-x: auto;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: transparent;
    border: none;
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn .material-icons {
    font-size: 18px;
}

.tab-btn:hover {
    color: #333;
    background: rgba(0, 0, 0, 0.05);
}

.tab-btn.active {
    background: #fff;
    color: #1a1a1a;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tab-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.tab-panel {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    background: #fafafa;
}

.card-header .material-icons {
    font-size: 20px;
    color: #666;
}

.card-header h3 {
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0;
}

.card-body {
    padding: 20px;
}

.card-danger .card-header {
    background: #fef2f2;
}

.card-danger .card-header .material-icons {
    color: #dc2626;
}

.form-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field__label {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    color: #333;
}

.field__input {
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #1a1a1a;
    background: #fff;
    transition: border-color 200ms;
}

.field__input:focus {
    outline: none;
    border-color: var(--red);
}

.field__hint {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #888;
    margin-top: 8px;
}

.field__error {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #dc2626;
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
}

.btn-primary {
    padding: 10px 20px;
    background: var(--red);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    padding: 10px 20px;
    background: transparent;
    color: #333;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    cursor: pointer;
    transition: all 200ms;
}

.btn-secondary:hover {
    background: #f5f5f5;
}

.api-desc {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #666;
    margin: 0 0 16px;
    line-height: 1.5;
}

.btn-ghost {
    padding: 10px 20px;
    background: transparent;
    color: #666;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    cursor: pointer;
}

.btn-ghost:hover {
    background: #f5f5f5;
    color: #333;
}

.btn-danger {
    padding: 10px 20px;
    background: #dc2626;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    cursor: pointer;
}

.btn-sm {
    padding: 6px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
}

.success-msg {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #16a34a;
}

.verify-notice {
    padding: 12px;
    background: #fef3c7;
    border-radius: 8px;
    font-size: 12px;
    letter-spacing: 0;
}

.verify-notice p {
    margin: 0 0 8px 0;
    color: #92400e;
    font-size: 12px;
    letter-spacing: 0;
}

.verify-notice a {
    color: #b45309;
    font-weight: 500;
}

.photo-row {
    display: flex;
    align-items: center;
    gap: 20px;
}

.avatar-lg {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.photo-actions {
    display: flex;
    gap: 10px;
}

.providers-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.provider-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}

.provider-row:last-child {
    border-bottom: none;
}

.provider-label {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    color: #333;
}

.provider-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
}

.status-badge.connected {
    background: #dcfce7;
    color: #16a34a;
}

.status-badge.disconnected {
    background: #f3f4f6;
    color: #6b7280;
}

.danger-text {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #666;
    margin: 0 0 16px 0;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 8px 0;
}

.modal-desc {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #666;
    margin: 0 0 20px 0;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.hidden {
    display: none;
}

/* Material Icons */
.material-icons {
    font-size: 20px;
    vertical-align: middle;
    margin-right: 4px;
}
</style>