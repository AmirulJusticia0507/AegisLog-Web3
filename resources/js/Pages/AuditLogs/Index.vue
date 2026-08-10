<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Audit Logs" />

    <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
        <h1 class="text-2xl font-bold text-slate-100">Audit Logs</h1>
        <p class="mt-2 text-sm text-slate-400">
            Daftar log audit akan muncul di sini. Halaman ini terhubung ke tabel <span class="font-mono text-indigo-400">audit_logs</span>
            (Fase 2: pipeline penjangkaran hash client-side).
        </p>
    </div>
</template>
