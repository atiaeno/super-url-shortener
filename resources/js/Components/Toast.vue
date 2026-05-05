<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    toasts: { type: Array, default: () => [] },
});

const localToasts = ref([...props.toasts]);

watch(() => props.toasts, (newToasts) => {
    localToasts.value = [...newToasts];
}, { deep: true });

const dismiss = (id) => {
    localToasts.value = localToasts.value.filter(t => t.id !== id);
};

const getIcon = (type) => {
    const icons = {
        success: 'check_circle',
        error: 'error',
        warning: 'warning',
        info: 'info',
    };
    return icons[type] || 'info';
};

const getIconColor = (type) => {
    const colors = {
        success: '#16a34a',
        error: '#dc2626',
        warning: '#f59e0b',
        info: '#3b82f6',
    };
    return colors[type] || '#3b82f6';
};
</script>

<template>
    <div class="toast-container">
        <TransitionGroup name="toast">
            <div v-for="toast in localToasts" :key="toast.id" class="toast" :class="`toast--${toast.type}`">
                <span class="toast-icon material-icons" :style="{ color: getIconColor(toast.type) }">
                    {{ getIcon(toast.type) }}
                </span>
                <span class="toast-message">{{ toast.message }}</span>
                <button class="toast-close" @click="dismiss(toast.id)">
                    <span class="material-icons">close</span>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 380px;
}

.toast {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ccc;
}

.toast--success {
    border-left-color: #16a34a;
}

.toast--error {
    border-left-color: #dc2626;
}

.toast--warning {
    border-left-color: #f59e0b;
}

.toast--info {
    border-left-color: #3b82f6;
}

.toast-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.toast-message {
    flex: 1;
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.toast-close {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    opacity: 0.5;
    transition: opacity 200ms;
}

.toast-close:hover {
    opacity: 1;
}

.toast-close .material-icons {
    font-size: 18px;
    color: #666;
}

/* Transitions */
.toast-enter-active {
    animation: slideIn 0.3s ease-out;
}

.toast-leave-active {
    animation: slideOut 0.3s ease-in;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }

    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>
