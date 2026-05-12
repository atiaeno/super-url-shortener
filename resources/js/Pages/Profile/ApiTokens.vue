<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const props = defineProps({
    tokens: { type: Array, default: () => [] },
    newToken: { type: String, default: '' },
    newTokenName: { type: String, default: '' },
});

const showToken = ref(props.newToken || '');
const showTokenName = ref(props.newTokenName || '');
const copiedId = ref(null);

const tokenForm = useForm({
    name: '',
    expires_days: '', // Empty = never expires
});

const createToken = () => {
    tokenForm.post(route('profile.api-tokens.store'));
};

const copyToken = async () => {
    await navigator.clipboard.writeText(showToken.value);
};

const copyToClipboard = async (text) => {
    await navigator.clipboard.writeText(text);
    // Show temporary success state
    copiedId.value = text;
    setTimeout(() => { copiedId.value = null; }, 2000);
};

const closeToken = () => {
    showToken.value = '';
    showTokenName.value = '';
};

const deleteForm = useForm({});

const deleteToken = (id) => {
    if (confirm('Are you sure you want to revoke this token? This action cannot be undone.')) {
        console.log('Deleting token:', id);
        deleteForm.delete(route('profile.api-tokens.destroy', { id }), {
            onSuccess: () => console.log('Token deleted'),
            onError: (e) => console.error('Delete error:', e),
        });
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
                            <input v-model="tokenForm.name" type="text" class="field__input"
                                placeholder="e.g., Production API" maxlength="255" required />
                        </div>
                        <div class="field field--sm">
                            <label class="field__label">Expires (days)</label>
                            <input v-model="tokenForm.expires_days" type="number" class="field__input"
                                placeholder="Never" min="1" max="365" />
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
                            <div class="token-value">
                                <code>{{ token.token }}</code>
                                <button @click="copyToClipboard(token.token)" class="btn-copy-small">
                                    {{ copiedId === token.token ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
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

        <!-- New Token Banner -->
        <div v-if="showToken" class="token-banner">
            <div class="token-banner-header">
                <span class="material-icons">check_circle</span>
                <h3>Token Created!</h3>
                <button @click="closeToken" class="btn-close">&times;</button>
            </div>
            <p class="token-banner-name">{{ showTokenName }}</p>
            <div class="token-banner-value">
                <code>{{ showToken }}</code>
                <button @click="copyToken" class="btn-copy">Copy</button>
            </div>
            <p class="token-banner-warning">⚠️ Copy this now - you won't see it again!</p>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');

:root {
    --font-display: 'Oswald', sans-serif;
    --font-body: 'DM Sans', sans-serif;
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
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
    letter-spacing: 0;
}

.intro-text {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
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
    letter-spacing: 0;
    border-radius: var(--radius);
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 8px 16px;
    transition: all 200ms;
}

.link-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
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
    letter-spacing: 0;
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    color: var(--ink);
    text-transform: uppercase;
    letter-spacing: 0;
}

.field__input {
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
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
    letter-spacing: 0;
    cursor: pointer;
    transition: opacity 0.2s;
    align-self: flex-start;
}

.btn-create {
    padding: 12px 24px;
    background: var(--green);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0;
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
    letter-spacing: 0;
}

.token-meta {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: var(--muted);
}

.token-value {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    background: #f5f5f5;
    padding: 8px;
    border-radius: 4px;
}

.token-value code {
    flex: 1;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #333;
    word-break: break-all;
}

.btn-copy-small {
    padding: 4px 8px;
    background: #333;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
}

.btn-copy-small:hover {
    background: #555;
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
    letter-spacing: 0;
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
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
    font-family: 'DM Sans', sans-serif;
    margin: 0;
    color: var(--muted);
    font-size: 12px;
    letter-spacing: 0;
}

.empty-hint {
    font-family: 'DM Sans', sans-serif;
    margin-top: 4px !important;
    font-size: 12px !important;
    letter-spacing: 0 !important;
}

/* Modal */
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

.modal {
    background: var(--surface);
    border-radius: 8px;
    width: 90%;
    max-width: 480px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
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
    letter-spacing: 0;
}

.modal-body {
    padding: 24px;
}

.modal-label {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0;
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
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 4px;
    color: #fff;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-copy:hover {
    background: rgba(255, 255, 255, 0.2);
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
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #92400e;
    margin: 0;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
}

/* Token Banner */
.token-banner {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 20px;
    color: #fff;
}

.token-banner-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.token-banner-header .material-icons {
    color: var(--green);
    font-size: 24px;
}

.token-banner-header h3 {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0;
    margin: 0;
    flex: 1;
}

.btn-close {
    background: none;
    border: none;
    color: #666;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.btn-close:hover {
    color: #fff;
}

.token-banner-name {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #aaa;
    margin: 0 0 8px;
}

.token-banner-value {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #000;
    border-radius: 4px;
    padding: 12px;
    margin-bottom: 12px;
}

.token-banner-value code {
    flex: 1;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: #22c55e;
    word-break: break-all;
}

.btn-copy {
    padding: 6px 12px;
    background: #333;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.btn-copy:hover {
    background: #444;
}

.token-banner-warning {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    color: #f59e0b;
    margin: 0;
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
