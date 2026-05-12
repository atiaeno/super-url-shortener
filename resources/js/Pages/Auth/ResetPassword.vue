<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Recaptcha from '@/Components/Recaptcha.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
    recaptchaSiteKey: {
        type: String,
        default: ''
    }
});

const recaptchaRef = ref(null);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
    recaptcha_token: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
            if (recaptchaRef.value) {
                recaptchaRef.value.reset();
            }
        },
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Establish New Cipher" />

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">New Cipher Establishment</h1>
            <p class="page-subtitle">Create a fresh secure credential</p>
        </div>

        <!-- Instructions -->
        <div class="info-panel">
            <div class="info-icon">&#128272;</div>
            <p class="info-text">
                Your identity has been verified. Please establish a new secret cipher below.
                Ensure it differs from your previous credential for enhanced security.
            </p>
        </div>

        <form @submit.prevent="submit" class="auth-form">
            <!-- Email Field (Read-only) -->
            <div class="form-field">
                <InputLabel for="email" value="Verified Email" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">@</span>
                    <TextInput id="email" type="email" class="auth-input auth-input--readonly" v-model="form.email"
                        readonly autocomplete="username" />
                </div>
                <InputError class="field-error" :message="form.errors.email" />
            </div>

            <!-- New Password Field -->
            <div class="form-field">
                <InputLabel for="password" value="New Password" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">&#128274;</span>
                    <PasswordInput id="password" class="auth-input" v-model="form.password" required
                        autocomplete="new-password" placeholder="Create your new passphrase" />
                </div>
                <p class="field-hint">Minimum 8 characters, include letters and numbers</p>
                <InputError class="field-error" :message="form.errors.password" />
            </div>

            <!-- Confirm Password Field -->
            <div class="form-field">
                <InputLabel for="password_confirmation" value="Confirm New Password" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">&#10003;</span>
                    <PasswordInput id="password_confirmation" class="auth-input" v-model="form.password_confirmation"
                        required autocomplete="new-password" placeholder="Re-enter your new passphrase" />
                </div>
                <InputError class="field-error" :message="form.errors.password_confirmation" />
            </div>

            <!-- reCAPTCHA -->
            <div v-if="recaptchaSiteKey" class="recaptcha-field">
                <Recaptcha ref="recaptchaRef" v-model="form.recaptcha_token" :site-key="recaptchaSiteKey" />
                <InputError class="field-error" :message="form.errors.recaptcha_token" />
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <PrimaryButton class="submit-btn submit-btn--full" :class="{ 'processing': form.processing }"
                    :disabled="form.processing">
                    <span v-if="form.processing">Establishing...</span>
                    <span v-else>Establish New Cipher</span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
/* Match Welcome.vue grayscale aesthetic */
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

/* Page Header */
.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-title {
    font-family: 'Oswald', sans-serif;
    font-size: 32px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    letter-spacing: -1px;
}

.page-subtitle {
    font-family: 'Crimson Pro', serif;
    font-size: 16px;
    color: #666;
    font-style: italic;
}

/* Info Panel */
.info-panel {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    background: #fafafa;
    border-left: 3px solid #1a1a1a;
}

.info-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.info-text {
    font-family: 'Crimson Pro', serif;
    font-size: 15px;
    color: #666;
    line-height: 1.6;
}

/* Form Fields */
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-field {
    position: relative;
}

.field-label {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
    margin-bottom: 0.5rem;
    display: block;
}

.input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 1rem;
    font-size: 0.875rem;
    color: #999;
    z-index: 1;
}

.auth-input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 2.5rem;
    font-family: 'Crimson Pro', serif;
    font-size: 16px;
    color: #1a1a1a;
    background: #fafafa;
    border: 1px solid #e5e5e5;
    border-radius: 0;
    transition: all 200ms ease;
}

.auth-input:focus {
    outline: none;
    border-color: #1a1a1a;
    background: #fff;
}

.auth-input::placeholder {
    color: #999;
}

.auth-input--readonly {
    background: #f0f0f0;
    cursor: not-allowed;
}

.field-hint {
    margin-top: 0.375rem;
    font-family: 'Crimson Pro', serif;
    font-size: 13px;
    color: #999;
    font-style: italic;
}

.field-error {
    margin-top: 0.5rem;
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #c41e3a;
}

/* reCAPTCHA Field */
.recaptcha-field {
    margin-top: 0.5rem;
}

/* Form Actions */
.form-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 0.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e5e5;
}

.submit-btn {
    padding: 0.875rem 2rem;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #fff;
    background: #1a1a1a;
    border: none;
    border-radius: 0;
    cursor: pointer;
    transition: all 200ms ease;
}

.submit-btn--full {
    width: 100%;
    justify-content: center;
}

.submit-btn:hover:not(:disabled) {
    background: #333;
    transform: translateY(-1px);
}

.submit-btn.processing {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 480px) {
    .info-panel {
        flex-direction: column;
        text-align: center;
    }
}
</style>
