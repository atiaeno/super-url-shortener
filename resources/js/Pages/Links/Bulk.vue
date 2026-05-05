<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const raw = ref('');
const results = ref([]);
const loading = ref(false);
const error = ref(null);

const urls = computed(() =>
    raw.value.split('\n').map(u => u.trim()).filter(Boolean)
);

const successCount = computed(() => results.value.filter(r => r.status === 'success').length);
const errorCount = computed(() => results.value.filter(r => r.status === 'error').length);

const submit = async () => {
    if (!urls.value.length) return;
    error.value = null;
    results.value = [];
    loading.value = true;

    try {
        const res = await axios.post(route('links.bulk.store'), { urls: urls.value });
        results.value = res.data.results;
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Something went wrong.';
    } finally {
        loading.value = false;
    }
};

const exportCsv = async () => {
    const res = await axios.post(route('links.bulk.export'), { results: results.value }, {
        responseType: 'blob',
    });
    const url = URL.createObjectURL(res.data);
    const a = document.createElement('a');
    a.href = url;
    a.download = `bulk-links-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
};

const handleFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => { raw.value = ev.target.result; };
    reader.readAsText(file);
};

const copyUrl = async (url) => {
    await navigator.clipboard.writeText(url);
};
</script>

<template>

    <Head title="Bulk URL Shortener" />

    <AuthenticatedLayout>
        <template #header>Bulk URL Shortener</template>

        <div class="bulk-layout">
            <div class="bulk-main">
                <!-- Input Panel -->
                <div class="panel">
                    <div class="panel__header">
                        <h3 class="panel__title">Paste URLs</h3>
                        <label class="upload-btn">
                            Upload CSV
                            <input type="file" accept=".csv,.txt" class="sr-only" @change="handleFile" />
                        </label>
                    </div>

                    <textarea v-model="raw" class="url-textarea"
                        placeholder="Paste one URL per line (max 500)&#10;https://example.com/very-long-url-1&#10;https://example.com/very-long-url-2"
                        rows="16" />

                    <div class="panel__footer">
                        <span class="count-badge">{{ urls.length }} / 500 URLs</span>
                        <button @click="submit" class="btn-primary"
                            :disabled="loading || !urls.length || urls.length > 500">
                            <span v-if="loading" class="spinner" />
                            <span v-else>Shorten All →</span>
                        </button>
                    </div>

                    <p v-if="error" class="field-error">{{ error }}</p>
                </div>

                <!-- Results Panel -->
                <div v-if="results.length" class="panel">
                    <div class="panel__header">
                        <h3 class="panel__title">
                            Results
                            <span class="badge badge--green">{{ successCount }} ok</span>
                            <span v-if="errorCount" class="badge badge--red">{{ errorCount }} failed</span>
                        </h3>
                        <button @click="exportCsv" class="btn-ghost">Export CSV</button>
                    </div>

                    <div class="results-table">
                        <div class="results-head">
                            <span>Original URL</span>
                            <span>Short URL</span>
                            <span></span>
                            <span>Status</span>
                        </div>
                        <div v-for="(row, i) in results" :key="i" class="results-row"
                            :class="row.status === 'error' ? 'results-row--error' : ''">
                            <span class="cell-url cell-url--original" :title="row.original_url">{{ row.original_url
                            }}</span>
                            <a v-if="row.short_url" :href="row.short_url" target="_blank"
                                class="cell-url cell-url--short">{{
                                    row.short_url }}</a>
                            <span v-else class="cell-url cell-url--err">{{ row.error }}</span>
                            <button v-if="row.short_url" @click="copyUrl(row.short_url)" class="copy-btn"
                                title="Copy">Copy</button>
                            <span v-else></span>
                            <span :class="row.status === 'success' ? 'status-ok' : 'status-err'">
                                {{ row.status === 'success' ? '✓' : '✗' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="bulk-sidebar">
                <h3 class="sidebar-title">Tips</h3>
                <ul class="sidebar-list">
                    <li>Paste one URL per line</li>
                    <li>Maximum 500 URLs at once</li>
                    <li>Upload CSV or TXT file</li>
                    <li>Export results to CSV</li>
                </ul>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');

.bulk-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 32px;
    max-width: 1200px;
}

.bulk-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.panel {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    overflow: hidden;
}

.panel__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e5e5;
    background: #fafafa;
}

.panel__title {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.panel__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-top: 1px solid #e5e5e5;
    background: #fafafa;
}

.url-textarea {
    width: 100%;
    background: #fff;
    border: none;
    outline: none;
    padding: 16px 20px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: #1a1a1a;
    line-height: 1.6;
    resize: vertical;
    min-height: 240px;
}

.url-textarea::placeholder {
    color: #888;
}

.count-badge {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #666;
}

.upload-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #1a1a1a;
    border: 1px solid #1a1a1a;
    border-radius: 4px;
    padding: 6px 14px;
    cursor: pointer;
    transition: all 200ms;
    background: #fff;
}

.upload-btn:hover {
    background: #1a1a1a;
    color: #fff;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: #e74c3c;
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: background 200ms;
}

.btn-primary:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.btn-primary:not(:disabled):hover {
    background: #c0392b;
}

.btn-ghost {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #666;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    padding: 6px 14px;
    background: #fff;
    cursor: pointer;
    transition: all 200ms;
}

.btn-ghost:hover {
    border-color: #1a1a1a;
    color: #1a1a1a;
}

.badge {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}

.badge--green {
    background: #dcfce7;
    color: #16a34a;
}

.badge--red {
    background: #fee2e2;
    color: #dc2626;
}

.field-error {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #dc2626;
    padding: 8px 20px;
}

/* Results table */
.results-table {
    max-height: 400px;
    overflow-y: auto;
}

.results-head {
    display: grid;
    grid-template-columns: 1fr 180px 60px 60px;
    padding: 10px 20px;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: #fafafa;
    border-bottom: 1px solid #e5e5e5;
    position: sticky;
    top: 0;
}

.results-row {
    display: grid;
    grid-template-columns: 1fr 180px 60px 60px;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 150ms;
}

.copy-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    padding: 4px 10px;
    background: #f5f5f5;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    cursor: pointer;
    color: #666;
}

.copy-btn:hover {
    background: #1a1a1a;
    color: #fff;
    border-color: #1a1a1a;
}

/* Sidebar */
.bulk-sidebar {
    padding: 20px;
    background: #fafafa;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    height: fit-content;
}

.sidebar-title {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #1a1a1a;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #1a1a1a;
}

.sidebar-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.sidebar-list li {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    color: #444;
    line-height: 1.5;
    padding-left: 16px;
    position: relative;
}

.sidebar-list li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 6px;
    width: 6px;
    height: 6px;
    background: #e74c3c;
    border-radius: 50%;
}

@media (max-width: 900px) {
    .bulk-layout {
        grid-template-columns: 1fr;
    }

    .bulk-sidebar {
        display: none;
    }
}

.results-row:hover {
    background: #fdf9f5;
}

.results-row--error {
    background: #fef2f2;
}

.cell-url {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cell-url--original {
    color: #666;
}

.cell-url--short {
    color: #e74c3c;
    text-decoration: none;
}

.cell-url--short:hover {
    text-decoration: underline;
}

.cell-url--err {
    color: #dc2626;
    font-size: 11px;
}

.status-ok {
    color: #16a34a;
    font-weight: 700;
    font-size: 14px;
    text-align: center;
}

.status-err {
    color: #dc2626;
    font-weight: 700;
    font-size: 14px;
    text-align: center;
}

.spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    display: inline-block;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>