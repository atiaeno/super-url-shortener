<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const props = defineProps({
    tokens: { type: Array, default: () => [] },
});

const showNewTokenModal = ref(false);
const newToken = ref('');
const newTokenName = ref('');

const tokenForm = useForm({
    name: '',
    expires_days: null,
});

const openNewTokenModal = () => {
    tokenForm.name = '';
    tokenForm.expires_days = null;
};

const createToken = () => {
    tokenForm.post(route('profile.api-tokens.store'), {
        onSuccess: (page) => {
            if (page.props.new_token) {
                newToken.value = page.props.new_token;
                newTokenName.value = page.props.new_token_name || 'API Token';
                showNewTokenModal.value = true;
            }
        },
    });
};

const closeTokenModal = () => {
    showNewTokenModal.value = false;
    newToken.value = '';
};

const copyToken = async () => {
    await navigator.clipboard.writeText(newToken.value);
};

const deleteForm = useForm({});

const deleteToken = (id) => {
    if (confirm('Are you sure you want to revoke this token? This action cannot be undone.')) {
        deleteForm.delete(route('profile.api-tokens.destroy', id));
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'Never';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const isExpired = (expiresAt) => {
    if (!expiresAt) return false;
    return new Date(expiresAt) < new Date();
};

const statusClass = (token) => {
    if (isExpired(token.expires_at)) return 'badge--red';
    if (token.last_used_at) return 'badge--green';
    return 'badge--gray';
};

const statusText = (token) => {
    if (isExpired(token.expires_at)) return 'Expired';
    if (token.last_used_at) return 'Active';
    return 'Never used';
};
</script>

<template>

    <Head title="API Tokens" />

    <AuthenticatedLayout>
        <template #header><span class="material-icons">vpn_key</span> API Tokens</template>

        <div class="page-content">
            <!-- Intro -->
            <div class="intro-card">
                <h2 class="intro-title">API Access</h2>
                <p class="intro-text">
                    Generate API tokens to access your links programmatically. 
                    Tokens can be used to create, read, update, and delete links via our REST API.
                </p>
                <div class="intro-link">
                    <a :href="route('api-docs')" target="_blank" class="link-btn">
                        <span class="material-icons">menu_book</span>
                        View API Documentation
                    </a>
                </div>
            </div>

            <!-- Create Token Form -->
            <div class="create-card">
                <h3 class="card-title">Create New Token</h3>
                <form @submit.prevent="createToken" class="create-form">
                    <div class="field-row">
                        <div class="field">
                            <label class="field__label">Token Name</label>
                            <input 
                                v-model="tokenForm.name" 
                                type="text" 
                                class="field__input"
                                placeholder="e.g., Production API"
                                maxlength="255"
                            />
                        </div>
                        <div class="field field--sm">
                            <label class="field__label">Expires (days)</label>
                            <input 
                                v-model="tokenForm.expires_days" 
                                type="number" 
                                class="field__input"
                                placeholder="Never"
                                min="1"
                                max="365"
                            />
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" :disabled="tokenForm.processing">
                        <span v-if="tokenForm.processing">Creating...</span>
                        <span v-else><span class="material-icons">add</span> Create Token</span>
                    </button>
                </form>
            </div>

            <!-- Tokens List -->
            <div class="tokens-card">
                <h3 class="card-title">Your Tokens</h3>
                
                <div v-if="tokens.length === 0" class="empty-state">
                    <span class="material-icons empty-icon">vpn_key_off</span>
                    <p>No API tokens yet</p>
                    <p class="empty-hint">Create your first token above to get started.</p>
                </div>

                <div v-else class="tokens-list">
                    <div v-for="token in tokens" :key="token.id" class="token-row">
                        <div class="token-info">
                            <span class="token-name">{{ token.name }}</span>
                            <span class="token-meta">
                                Created {{ formatDate(token.created_at) }}
                                <span v-if="token.expires_at"> · Expires {{ formatDate(token.expires_at) }}</span>
                            </span>
                        </div>
                        <div class="token-status">
                            <span class="status-badge" :class="statusClass(token)">
                                {{ statusText(token) }}
                            </span>
                            <span v-if="token.last_used_at" class="last-used">
                                Last used {{ formatDate(token.last_used_at) }}
                            </span>
                        </div>
                        <button @click="deleteToken(token.id)" class="btn-revoke" title="Revoke token">
                            <span class="material-icons">delete_outline</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Token Modal -->
        <div v-if="showNewTokenModal" class="modal-overlay" @click.self="closeTokenModal">
            <div class="modal">
                <div class="modal-header">
                    <span class="material-icons modal-icon">check_circle</span>
                    <h3>Token Created</h3>
                </div>
                <div class="modal-body">
                    <p class="modal-label">Token name:</p>
                    <p class="modal-value">{{ newTokenName }}</p>
                    
                    <p class="modal-label">Your new API token:</p>
                    <div class="token-display">
                        <code>{{ newToken }}</code>
                        <button @click="copyToken" class="btn-copy" title="Copy to clipboard">
                            <span class="material-icons">content_copy</span>
                        </button>
                    </div>
                    
                    <div class="warning-box">
                        <span class="material-icons">warning</span>
                        <p>Copy this token now. You won't be able to see it again!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="closeTokenModal" class="btn-primary">I've saved my token</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');

:root {
    --font-display: 'Oswald', sans-serif;
    --red: #e74c3c;
    --red-dark: #c0392b;
    --green: #27ae60;
    --ink: #1a1a1a;
    --muted: #666;
    --border: #e5e5e5;
    --surface: #fff;
    --radius: 4px;
}

.page-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 24px;
}

.material-icons {
    font-size: 20px;
    vertical-align: middle;
    margin-right: 4px;
}

/* Intro Card */
.intro-card {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-radius: var(--radius);
    padding: 28px;
    margin-bottom: 20px;
    color: #fff;
}

.intro-title {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 600;
    margin: 0 0 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.intro-text {
    font-size: 14px;
    color: #aaa;
    margin: 0 0 16px;
    line-height: 1.6;
}

.link-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #fff;
    text-decoration: none;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 16px;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: var(--radius);
    transition: all 0.2s;
}

.link-btn:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.5);
}

.link-btn .material-icons {
    font-size: 16px;
}

/* Create Card */
.create-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 20px;
}

.card-title {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--ink);
    margin: 0 0 16px;
}

.create-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.field-row {
    display: flex;
    gap: 16px;
}

.field {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field--sm {
    flex: 0 0 140px;
}

.field__label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ink);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.field__input {
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.field__input:focus {
    border-color: var(--red);
}

/* Buttons */
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 20px;
    background: var(--red);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: opacity 0.2s;
    align-self: flex-start;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary:hover:not(:disabled) {
    background: var(--red-dark);
}

/* Tokens Card */
.tokens-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
}

.tokens-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.token-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #fafafa;
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.token-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.token-name {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.token-meta {
    font-size: 12px;
    color: var(--muted);
}

.token-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.status-badge {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 10px;
    border-radius: 2px;
}

.badge--green {
    background: #dcfce7;
    color: #16a34a;
}

.badge--gray {
    background: #f3f4f6;
    color: #6b7280;
}

.badge--red {
    background: #fef2f2;
    color: #dc2626;
}

.last-used {
    font-size: 11px;
    color: var(--muted);
}

.btn-revoke {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
}

.btn-revoke:hover {
    border-color: var(--red);
    color: var(--red);
    background: #fef2f2;
}

.btn-revoke .material-icons {
    font-size: 18px;
    margin: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-icon {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 12px;
}

.empty-state p {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
}

.empty-hint {
    margin-top: 4px !important;
    font-size: 12px !important;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--surface);
    border-radius: 8px;
    width: 90%;
    max-width: 480px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
}

.modal-icon {
    font-size: 28px;
    color: var(--green);
}

.modal-header h3 {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.modal-body {
    padding: 24px;
}

.modal-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    margin: 0 0 4px;
}

.modal-value {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 16px;
}

.token-display {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #1a1a1a;
    border-radius: var(--radius);
    padding: 12px;
    margin-bottom: 16px;
}

.token-display code {
    flex: 1;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: #22c55e;
    word-break: break-all;
}

.btn-copy {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.1);
    border: none;
    border-radius: 4px;
    color: #fff;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-copy:hover {
    background: rgba(255,255,255,0.2);
}

.btn-copy .material-icons {
    font-size: 16px;
    margin: 0;
}

.warning-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: var(--radius);
    padding: 12px;
}

.warning-box .material-icons {
    font-size: 20px;
    color: #d97706;
    margin: 0;
}

.warning-box p {
    font-size: 13px;
    color: #92400e;
    margin: 0;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 640px) {
    .page-content {
        padding: 16px;
    }

    .field-row {
        flex-direction: column;
    }

    .field--sm {
        flex: 1;
    }

    .token-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .token-status {
        align-items: flex-start;
    }
}
</style>
