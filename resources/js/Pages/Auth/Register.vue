<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import CaptchaWrapper from '@/Components/CaptchaWrapper.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    recaptchaSiteKey: {
        type: String,
        default: ''
    },
    captchaProvider: {
        type: String,
        default: 'recaptcha'
    }
});

const recaptchaRef = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    recaptcha_token: '',
});

const submit = () => {
    form.post(route('register'), {
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

        <Head title="Apply for Membership" />

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">New Membership</h1>
            <p class="page-subtitle">Complete your registration credentials</p>
        </div>

        <form @submit.prevent="submit" class="auth-form">
            <!-- Name Field -->
            <div class="form-field">
                <InputLabel for="name" value="Full Designation" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">&#128100;</span>
                    <TextInput id="name" type="text" class="auth-input" v-model="form.name" required autofocus
                        autocomplete="name" placeholder="Enter your complete name" />
                </div>
                <InputError class="field-error" :message="form.errors.name" />
            </div>

            <!-- Email Field -->
            <div class="form-field">
                <InputLabel for="email" value="Electronic Address" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">@</span>
                    <TextInput id="email" type="email" class="auth-input" v-model="form.email" required
                        autocomplete="username" placeholder="correspondent@domain.com" />
                </div>
                <InputError class="field-error" :message="form.errors.email" />
            </div>

            <!-- Password Field -->
            <div class="form-field">
                <InputLabel for="password" value="Primary Cipher" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">&#128274;</span>
                    <PasswordInput id="password" class="auth-input" v-model="form.password" required
                        autocomplete="new-password" placeholder="Create a secure passphrase" />
                </div>
                <p class="field-hint">Minimum 8 characters, include letters and numbers</p>
                <InputError class="field-error" :message="form.errors.password" />
            </div>

            <!-- Confirm Password Field -->
            <div class="form-field">
                <InputLabel for="password_confirmation" value="Confirm Cipher" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">&#10003;</span>
                    <PasswordInput id="password_confirmation" class="auth-input" v-model="form.password_confirmation"
                        required autocomplete="new-password" placeholder="Re-enter your passphrase" />
                </div>
                <InputError class="field-error" :message="form.errors.password_confirmation" />
            </div>

            <!-- Terms Agreement -->
            <div class="terms-section">
                <p class="terms-text">
                    By proceeding, you agree to our
                    <Link href="/legal/terms" class="terms-link">Terms of Service</Link>
                    and
                    <Link href="/legal/privacy" class="terms-link">Privacy Protocol</Link>.
                </p>
            </div>

            <!-- CAPTCHA -->
            <div v-if="recaptchaSiteKey" class="recaptcha-field">
                <CaptchaWrapper ref="recaptchaRef" v-model="form.recaptcha_token" :site-key="recaptchaSiteKey"
                    :provider="captchaProvider" />
                <InputError class="field-error" :message="form.errors.recaptcha_token" />
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <Link :href="route('login')" class="secondary-link">
                    Return to Entry
                </Link>

                <PrimaryButton class="submit-btn" :class="{ 'processing': form.processing }"
                    :disabled="form.processing">
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Complete Registration</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Benefits Note -->
        <div class="benefits-note">
            <h3 class="benefits-title">Membership Privileges</h3>
            <ul class="benefits-list">
                <li>Permanent link archival</li>
                <li>Detailed analytics access</li>
                <li>Custom alias configuration</li>
                <li>Priority support correspondence</li>
            </ul>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Match Welcome.vue grayscale aesthetic */
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

/* Page Header */
.page-header {
    text-align: center;
    margin-bottom: 2.5rem;
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

/* Terms Section */
.terms-section {
    padding: 1rem;
    background: #fafafa;
    border-left: 2px solid #e5e5e5;
}

.terms-text {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    line-height: 1.6;
}

.terms-link {
    color: #1a1a1a;
    text-decoration: none;
    transition: color 200ms ease;
}

.terms-link:hover {
    color: #666;
    text-decoration: underline;
}

/* reCAPTCHA Field */
.recaptcha-field {
    margin-top: 0.5rem;
}

/* Form Actions */
.form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 0.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e5e5;
}

.secondary-link {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    text-decoration: none;
    transition: color 200ms ease;
}

.secondary-link:hover {
    color: #1a1a1a;
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

.submit-btn:hover:not(:disabled) {
    background: #333;
    transform: translateY(-1px);
}

.submit-btn.processing {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Benefits Note */
.benefits-note {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #fafafa;
    border: 1px solid #e5e5e5;
}

.benefits-title {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.benefits-list li {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    padding: 0.375rem 0;
    padding-left: 1.25rem;
    position: relative;
}

.benefits-list li::before {
    content: '—';
    position: absolute;
    left: 0;
    color: #1a1a1a;
}

/* Responsive */
@media (max-width: 480px) {
    .form-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .secondary-link {
        text-align: center;
    }

    .submit-btn {
        width: 100%;
    }
}
</style>
