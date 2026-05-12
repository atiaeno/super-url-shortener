<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import CaptchaWrapper from '@/Components/CaptchaWrapper.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
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
    recaptcha_token: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onFinish: () => {
            if (recaptchaRef.value) {
                recaptchaRef.value.reset();
            }
        },
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Cipher Recovery" />

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Cipher Recovery</h1>
            <p class="page-subtitle">Request a new access credential</p>
        </div>

        <!-- Instructions -->
        <div class="info-panel">
            <div class="info-icon">&#128276;</div>
            <p class="info-text">
                Forgot your secret cipher? No cause for alarm. Provide your electronic
                address below, and we shall dispatch a recovery directive to your
                registered correspondence channel.
            </p>
        </div>

        <!-- Success Message -->
        <div v-if="status" class="success-panel">
            <span class="success-icon">&#10003;</span>
            <p class="success-text">{{ status }}</p>
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

            <!-- CAPTCHA -->
            <div v-if="recaptchaSiteKey" class="recaptcha-field">
                <CaptchaWrapper ref="recaptchaRef" v-model="form.recaptcha_token" :site-key="recaptchaSiteKey"
                    :provider="captchaProvider" />
                <InputError class="field-error" :message="form.errors.recaptcha_token" />
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <Link :href="route('login')" class="secondary-link">
                    &#8592; Return to Entry
                </Link>

                <PrimaryButton class="submit-btn" :class="{ 'processing': form.processing }"
                    :disabled="form.processing">
                    <span v-if="form.processing">Sending...</span>
                    <span v-else>Dispatch Recovery</span>
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

/* Success Panel */
.success-panel {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    background: #f5f5f5;
    border-left: 3px solid #1a1a1a;
}

.success-icon {
    font-size: 1.25rem;
    color: #1a1a1a;
}

.success-text {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #1a1a1a;
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
    display: flex;
    align-items: center;
    gap: 0.375rem;
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

/* Responsive */
@media (max-width: 480px) {
    .form-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .secondary-link {
        justify-content: center;
    }

    .submit-btn {
        width: 100%;
    }

    .info-panel {
        flex-direction: column;
        text-align: center;
    }
}
</style>
