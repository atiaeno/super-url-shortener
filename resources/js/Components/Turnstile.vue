<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: String,
    siteKey: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'verified', 'expired', 'error']);
const turnstileContainer = ref(null);
const widgetId = ref(null);

onMounted(() => {
    // Load Turnstile script if not already loaded
    if (!window.turnstile) {
        const script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad&render=explicit';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);

        window.onTurnstileLoad = () => {
            renderTurnstile();
        };
    } else {
        renderTurnstile();
    }
});

const renderTurnstile = () => {
    if (window.turnstile && turnstileContainer.value) {
        widgetId.value = window.turnstile.render(turnstileContainer.value, {
            sitekey: props.siteKey,
            callback: onVerify,
            'expired-callback': onExpired,
            'error-callback': onError,
            theme: 'light',
            size: 'normal',
            action: 'login',
            cData: 'test-mode'
        });
    }
};

const onVerify = (token) => {
    emit('update:modelValue', token);
    emit('verified', token);
};

const onExpired = () => {
    emit('update:modelValue', '');
    emit('expired');
};

const onError = (error) => {
    console.error('Turnstile error:', error);
    emit('update:modelValue', '');
    emit('error', error);
};

const reset = () => {
    if (window.turnstile && widgetId.value !== null) {
        window.turnstile.reset(widgetId.value);
    }
};

defineExpose({ reset });
</script>

<template>
    <div class="turnstile-container">
        <div ref="turnstileContainer" class="cf-turnstile"></div>
        <input type="hidden" :value="modelValue" required />
    </div>
</template>

<style scoped>
.turnstile-container {
    display: flex;
    justify-content: center;
    margin: 1rem 0;
}

.cf-turnstile {
    transform-origin: center;
}

@media (max-width: 480px) {
    .cf-turnstile {
        transform: scale(0.9);
    }
}
</style>
