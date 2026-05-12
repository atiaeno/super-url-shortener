<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import CaptchaWrapper from '@/Components/CaptchaWrapper.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
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
    email: '',
    password: '',
    remember: false,
    recaptcha_token: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
            if (recaptchaRef.value) {
                recaptchaRef.value.reset();
            }
        },
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Sign In" />

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Member Access</h1>
            <p class="page-subtitle">Present your credentials to enter the archive</p>
        </div>

        <!-- Status Message -->
        <div v-if="status" class="status-message">
            <span class="status-icon">&#10003;</span>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="auth-form">
            <!-- Email Field -->
            <div class="form-field">
                <InputLabel for="email" value="Email Address" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">@</span>
                    <TextInput id="email" type="email" class="auth-input" v-model="form.email" required autofocus
                        autocomplete="username" placeholder="correspondent@domain.com" />
                </div>
                <InputError class="field-error" :message="form.errors.email" />
            </div>

            <!-- Password Field -->
            <div class="form-field">
                <InputLabel for="password" value="Password" class="field-label" />
                <div class="input-wrap">
                    <span class="input-icon">&#128274;</span>
                    <PasswordInput id="password" class="auth-input" v-model="form.password" required
                        autocomplete="current-password" placeholder="Enter your passphrase" />
                </div>
                <InputError class="field-error" :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="form-options">
                <label class="remember-label">
                    <Checkbox name="remember" v-model:checked="form.remember" class="auth-checkbox" />
                    <span class="remember-text">Maintain session continuity</span>
                </label>
            </div>

            <!-- CAPTCHA -->
            <div v-if="recaptchaSiteKey" class="recaptcha-field">
                <CaptchaWrapper ref="recaptchaRef" v-model="form.recaptcha_token" :site-key="recaptchaSiteKey"
                    :provider="captchaProvider" />
                <InputError class="field-error" :message="form.errors.recaptcha_token" />
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <Link v-if="canResetPassword" :href="route('password.request')" class="forgot-link">
                    Cipher recovery?
                </Link>

                <PrimaryButton class="submit-btn" :class="{ 'processing': form.processing }"
                    :disabled="form.processing">
                    <span v-if="form.processing">Authenticating...</span>
                    <span v-else>Enter Archive</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Divider -->
        <div class="section-divider">
            <span class="divider-line"></span>
            <span class="divider-text">Alternative Entry Methods</span>
            <span class="divider-line"></span>
        </div>

        <!-- OAuth Buttons -->
        <div class="oauth-grid">
            <a :href="route('social.redirect', 'google')" class="oauth-btn oauth-google">
                <svg class="oauth-icon" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4" />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853" />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                        fill="#FBBC05" />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335" />
                </svg>
                <span class="oauth-label">Google</span>
            </a>
            <a :href="route('social.redirect', 'github')" class="oauth-btn oauth-github">
                <svg class="oauth-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" />
                </svg>
                <span class="oauth-label">GitHub</span>
            </a>
            <a :href="route('social.redirect', 'facebook')" class="oauth-btn oauth-facebook">
                <svg class="oauth-icon" viewBox="0 0 24 24" fill="#1877F2">
                    <path
                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                </svg>
                <span class="oauth-label">Facebook</span>
            </a>
        </div>

        <!-- Registration CTA -->
        <div class="register-cta">
            <p>Not yet registered with the Bureau?</p>
            <Link :href="route('register')" class="register-link">
                Apply for Membership
                <span class="link-arrow">&#8594;</span>
            </Link>
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

/* Status Message */
.status-message {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1rem;
    margin-bottom: 1.5rem;
    background: #f5f5f5;
    border-left: 3px solid #1a1a1a;
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #1a1a1a;
}

.status-icon {
    font-size: 1rem;
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

.field-error {
    margin-top: 0.5rem;
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #c41e3a;
}

/* Form Options */
.form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.auth-checkbox {
    accent-color: #1a1a1a;
}

.remember-text {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
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

.forgot-link {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    text-decoration: none;
    transition: color 200ms ease;
}

.forgot-link:hover {
    color: #1a1a1a;
    text-decoration: underline;
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

/* Section Divider */
.section-divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 2.5rem 0 1.5rem;
}

.divider-line {
    flex: 1;
    height: 1px;
    background: #e5e5e5;
}

.divider-text {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #999;
    white-space: nowrap;
}

/* OAuth Grid */
.oauth-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.oauth-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 0.5rem;
    background: #fafafa;
    border: 1px solid #e5e5e5;
    text-decoration: none;
    transition: all 200ms ease;
}

.oauth-btn:hover {
    background: #fff;
    border-color: #1a1a1a;
    transform: translateY(-2px);
}

.oauth-icon {
    width: 20px;
    height: 20px;
}

.oauth-label {
    font-family: 'Oswald', sans-serif;
    font-size: 9px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #666;
}

/* Register CTA */
.register-cta {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e5e5;
}

.register-cta p {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    margin-bottom: 0.5rem;
}

.register-link {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #1a1a1a;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 200ms ease;
}

.register-link:hover {
    color: #666;
    gap: 0.75rem;
}

.link-arrow {
    transition: transform 200ms ease;
}

.register-link:hover .link-arrow {
    transform: translateX(4px);
}

/* Responsive */
@media (max-width: 480px) {
    .oauth-grid {
        grid-template-columns: 1fr;
    }

    .oauth-btn {
        flex-direction: row;
        justify-content: center;
        padding: 0.75rem;
    }

    .form-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .forgot-link {
        text-align: center;
    }

    .submit-btn {
        width: 100%;
    }
}
</style>
