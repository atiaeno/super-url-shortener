<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { computed, defineAsyncComponent, ref } from 'vue';

const props = defineProps({
    modelValue: String,
    siteKey: {
        type: String,
        required: true
    },
    provider: {
        type: String,
        required: true,
        validator: (value) => ['recaptcha', 'turnstile'].includes(value)
    }
});

const emit = defineEmits(['update:modelValue', 'verified', 'expired', 'error']);

// Async components for lazy loading
const Recaptcha = defineAsyncComponent(() => import('./Recaptcha.vue'));
const Turnstile = defineAsyncComponent(() => import('./Turnstile.vue'));

const captchaRef = ref(null);

// Computed property to determine which component to render
const currentComponent = computed(() => {
    switch (props.provider) {
        case 'turnstile':
            return Turnstile;
        case 'recaptcha':
        default:
            return Recaptcha;
    }
});

// Handle events from child components
const handleVerified = (token) => {
    emit('update:modelValue', token);
    emit('verified', token);
};

const handleExpired = () => {
    emit('update:modelValue', '');
    emit('expired');
};

const handleError = () => {
    emit('update:modelValue', '');
    emit('error');
};

// Expose reset method to parent
const reset = () => {
    if (captchaRef.value) {
        captchaRef.value.reset();
    }
};

defineExpose({ reset });
</script>

<template>
    <div class="captcha-wrapper">
        <component
            :is="currentComponent"
            ref="captchaRef"
            :model-value="modelValue"
            :site-key="siteKey"
            @update:modelValue="$emit('update:modelValue', $event)"
            @verified="handleVerified"
            @expired="handleExpired"
            @error="handleError"
        />
    </div>
</template>

<style scoped>
.captcha-wrapper {
    display: flex;
    justify-content: center;
    margin: 1rem 0;
}
</style>
