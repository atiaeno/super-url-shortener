<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Recaptcha from '@/Components/Recaptcha.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    status: {
        type: String,
    },
    recaptchaSiteKey: {
        type: String,
        default: ''
    }
});

const recaptchaRef = ref(null);

const form = useForm({
    recaptcha_token: '',
});

const submit = () => {
    form.post(route('verification.send'), {
        onFinish: () => {
            if (recaptchaRef.value) {
                recaptchaRef.value.reset();
            }
        },
    });
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>

        <Head title="Correspondence Verification" />

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Verify Your Identity</h1>
            <p class="page-subtitle">Confirm your email address</p>
        </div>

        <!-- Instructions -->
        <div class="info-panel">
            <div class="info-icon">&#9993;</div>
            <p class="info-text">
                Welcome to the Bureau! Before accessing the archives, we require
                verification of your email address. Please examine your correspondence
                for our authentication directive and follow the enclosed instructions.
            </p>
        </div>

        <!-- Success Message -->
        <div v-if="verificationLinkSent" class="success-panel">
            <span class="success-icon">&#10003;</span>
            <p class="success-text">
                A fresh authentication directive has been dispatched to your
                registered email address. Please allow several minutes
                for delivery.
            </p>
        </div>

        <!-- Actions Form -->
        <form @submit.prevent="submit" class="auth-form">
            <!-- reCAPTCHA -->
            <div v-if="recaptchaSiteKey" class="recaptcha-field">
                <Recaptcha ref="recaptchaRef" v-model="form.recaptcha_token" :site-key="recaptchaSiteKey" />
                <InputError class="field-error" :message="form.errors.recaptcha_token" />
            </div>

            <div class="form-actions">
                <Link :href="route('logout')" method="post" as="button" class="secondary-link">
                    Exit Session
                </Link>

                <PrimaryButton class="submit-btn" :class="{ 'processing': form.processing }"
                    :disabled="form.processing">
                    <span v-if="form.processing">Dispatching...</span>
                    <span v-else>Resend Directive</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Help Note -->
        <div class="help-note">
            <h3 class="help-title">Correspondence Not Received?</h3>
            <ul class="help-list">
                <li>Inspect your correspondence filtering apparatus (spam folder)</li>
                <li>Confirm your email address was entered accurately</li>
                <li>Allow up to five minutes for delivery</li>
                <li>Request another directive using the button above</li>
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

/* Success Panel */
.success-panel {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    background: #f5f5f5;
    border-left: 3px solid #1a1a1a;
}

.success-icon {
    font-size: 1.25rem;
    color: #1a1a1a;
    flex-shrink: 0;
}

.success-text {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #1a1a1a;
    line-height: 1.5;
}

/* Form */
.auth-form {
    margin-bottom: 1.5rem;
}

/* reCAPTCHA Field */
.recaptcha-field {
    margin: 1rem 0;
}

/* Form Actions */
.form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e5e5;
}

.secondary-link {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    background: none;
    border: none;
    cursor: pointer;
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

/* Help Note */
.help-note {
    padding: 1.5rem;
    background: #fafafa;
    border: 1px solid #e5e5e5;
}

.help-title {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.help-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.help-list li {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    padding: 0.375rem 0;
    padding-left: 1.25rem;
    position: relative;
}

.help-list li::before {
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
        justify-content: center;
    }

    .info-panel,
    .success-panel {
        flex-direction: column;
        text-align: center;
    }
}
</style>
