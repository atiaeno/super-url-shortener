<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head } from '@inertiajs/vue3';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const props = defineProps({
    message: String,
});
</script>

<template>
    <Head>
        <title>Maintenance Mode</title>
        <meta name="robots" content="noindex,nofollow" />
    </Head>

    <div class="maintenance-page">
        <Masthead />

        <main class="maintenance-container">
            <div class="maintenance-content">
                <div class="icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                
                <h1 class="maintenance-title">Under Maintenance</h1>
                
                <p class="maintenance-message">
                    {{ message }}
                </p>
                
                <div class="maintenance-info">
                    <p>We're working hard to improve our service. We'll be back shortly!</p>
                    <p class="retry-hint">You can try refreshing this page in a few minutes.</p>
                </div>
                
                <button @click="$inertia.reload()" class="refresh-btn">
                    Refresh Page
                </button>
            </div>
        </main>

        <EditorialFooter />
    </div>
</template>

<style scoped>
.maintenance-page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.maintenance-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.maintenance-content {
    text-align: center;
    max-width: 500px;
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-wrapper svg {
    width: 40px;
    height: 40px;
    color: #6b7280;
}

.maintenance-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

.maintenance-message {
    font-size: 1.125rem;
    color: #4b5563;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.maintenance-info {
    margin-bottom: 2rem;
}

.maintenance-info p {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.retry-hint {
    font-size: 0.875rem;
    font-style: italic;
}

.refresh-btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

.refresh-btn:hover {
    background: #2563eb;
}
</style>
