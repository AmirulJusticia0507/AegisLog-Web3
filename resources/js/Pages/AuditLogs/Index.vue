<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useAccount, useSignMessage } from '@wagmi/vue';
import { useFileHash } from '../../composables/useFileHash';
import StatusBadge from '../../Components/StatusBadge.vue';

const props = defineProps({
    auditLogs: { type: Array, default: () => [] },
});

const { address, isConnected } = useAccount();
const { signMessage, isPending: signing } = useSignMessage();
const { hashing, computeHash } = useFileHash();

const file = ref(null);
const title = ref('');
const hash = ref('');
const fileSize = ref(null);
const errors = ref({});

const flash = computed(() => usePage().props.flash);

const formatSize = (bytes) => {
    if (bytes === null || bytes === undefined) return '-';

    if (bytes > 1024 * 1024) return `${(bytes / 1024 / 1024).toFixed(2)} MB`;

    return `${Math.max(1, Math.round(bytes / 1024))} KB`;
};

async function onFileChange(event) {
    const selected = event.target.files[0];

    if (!selected) return;

    file.value = selected;
    fileSize.value = selected.size;
    errors.value = {};
    hash.value = await computeHash(selected);
}

async function submit() {
    errors.value = {};

    if (!file.value) {
        errors.value.file = 'Pilih berkas terlebih dahulu.';
        return;
    }

    if (!title.value.trim()) {
        errors.value.title = 'Judul laporan wajib diisi.';
        return;
    }

    if (!isConnected.value) {
        errors.value.wallet = 'Hubungkan wallet terlebih dahulu untuk menandatangani.';
        return;
    }

    const message = `AegisLog-Web3 anchor ${hash.value}`;

    let signature;
    try {
        signature = await signMessage({ message });
    } catch {
        errors.value.signature = 'Penandatanganan dibatalkan atau gagal di wallet.';
        return;
    }

    const form = new FormData();
    form.append('title', title.value);
    form.append('file', file.value);
    form.append('client_hash', hash.value);
    form.append('signature', signature);
    form.append('address', address.value);

    router.post('/audit-logs', form, {
        preserveScroll: true,
        onError: (pageErrors) => {
            errors.value = pageErrors;
        },
        onSuccess: () => {
            file.value = null;
            title.value = '';
            hash.value = '';
            fileSize.value = null;
            document.getElementById('file-input').value = '';
        },
    });
}
</script>

<template>
    <Head title="Audit Logs" />

    <div v-if="flash?.message" class="mb-6 rounded-lg border border-emerald-800/60 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">
        {{ flash.message }}
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <section class="lg:col-span-2 p-6 rounded-xl border border-slate-800 bg-slate-900/80 self-start">
            <h1 class="text-xl font-bold text-slate-100">Anchor Audit Report</h1>
            <p class="mt-1 text-sm text-slate-400">
                Hash SHA-256 dihitung di browser, ditandatangani wallet, lalu diverifikasi ulang di backend.
            </p>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <div>
                    <label class="text-xs uppercase tracking-wider text-slate-500">Berkas (PDF/JSON/TXT/LOG)</label>
                    <input
                        id="file-input"
                        type="file"
                        accept=".pdf,.json,.txt,.log"
                        @change="onFileChange"
                        class="mt-2 w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500"
                    >
                    <p v-if="errors.file" class="mt-1 text-xs text-red-500">{{ errors.file }}</p>
                </div>

                <div>
                    <label class="text-xs uppercase tracking-wider text-slate-500">Judul Laporan</label>
                    <input
                        v-model="title"
                        type="text"
                        maxlength="255"
                        placeholder="cth: Pentest-Laporan-Aplikasi-Bank-2026"
                        class="mt-2 w-full rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 placeholder:text-slate-600 focus:border-indigo-500 focus:outline-none"
                    >
                    <p v-if="errors.title" class="mt-1 text-xs text-red-500">{{ errors.title }}</p>
                </div>

                <div v-if="hashing" class="text-sm text-slate-400">Menghitung hash SHA-256 di browser…</div>

                <div v-if="hash" class="rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span class="uppercase tracking-wider">Preview Berkas</span>
                        <span class="font-mono">{{ formatSize(fileSize) }}</span>
                    </div>
                    <div class="mt-2 flex items-center space-x-2 font-mono text-xs text-slate-300">
                        <span class="text-indigo-400 font-bold">SHA256:</span>
                        <span class="truncate">{{ hash }}</span>
                    </div>
                </div>

                <p v-if="errors.wallet || errors.signature" class="text-xs text-red-500">
                    {{ errors.wallet || errors.signature }}
                </p>

                <button
                    type="submit"
                    :disabled="hashing || signing || !hash"
                    class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-4 py-2.5 text-sm font-semibold text-white transition"
                >
                    {{ signing ? 'Menandatangani…' : 'Sign & Anchor' }}
                </button>
            </form>
        </section>

        <section class="lg:col-span-3 p-6 rounded-xl border border-slate-800 bg-slate-900/80">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-100">Riwayat Audit Logs</h2>
                <span class="font-mono text-xs text-slate-500">{{ auditLogs.length }} records</span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-slate-500 border-b border-slate-800">
                            <th class="py-2 pr-4">Judul</th>
                            <th class="py-2 pr-4">Hash SHA-256</th>
                            <th class="py-2 pr-4">Pengunggah</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in auditLogs" :key="log.id" class="border-b border-slate-800/60">
                            <td class="py-3 pr-4 text-slate-200 font-medium">{{ log.title }}</td>
                            <td class="py-3 pr-4">
                                <div class="flex items-center space-x-2 font-mono text-xs bg-slate-950/80 px-2.5 py-1.5 rounded border border-slate-800 text-slate-300">
                                    <span class="truncate w-40">{{ log.file_hash }}</span>
                                </div>
                            </td>
                            <td class="py-3 pr-4 font-mono text-xs text-slate-400">{{ log.user?.wallet_address?.slice(0, 10) }}…</td>
                            <td class="py-3"><StatusBadge :status="log.integrity_status" /></td>
                        </tr>
                        <tr v-if="auditLogs.length === 0">
                            <td colspan="4" class="py-8 text-center text-sm text-slate-500">
                                Belum ada log audit yang dijangkarkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
