<script setup>
import { Link } from '@inertiajs/vue3';
import { useTamperAlerts } from './composables/useTamperAlerts';

const { alerts } = useTamperAlerts();
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-200">
        <div v-if="alerts.length" class="fixed top-4 right-4 z-50 w-80 space-y-2">
            <div
                v-for="alert in alerts"
                :key="alert.id"
                class="rounded-lg border border-red-800/70 bg-red-950/80 backdrop-blur px-4 py-3 shadow-lg shadow-red-950/50"
            >
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                    <span class="text-xs font-bold uppercase tracking-wider text-red-400">TAMPERED</span>
                </div>
                <p class="mt-2 text-sm font-semibold text-red-100">{{ alert.title }}</p>
                <p v-if="alert.file_hash" class="mt-1 font-mono text-xs text-red-300/80 truncate">{{ alert.file_hash }}</p>
            </div>
        </div>

        <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></div>
                    <span class="font-bold text-indigo-400 tracking-wide">AEGISLOG</span>
                    <span class="text-slate-500 text-sm hidden sm:inline">Web3 Audit Vault</span>
                </div>
                <nav class="flex items-center space-x-6 text-sm">
                    <Link href="/" class="text-slate-300 hover:text-indigo-400 transition">Dashboard</Link>
                    <Link href="/audit-logs" class="text-slate-300 hover:text-indigo-400 transition">Audit Logs</Link>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-8">
            <slot />
        </main>

        <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-600">
            AegisLog Web3 — SHA-256 anchored on-chain via AuditVault.sol
        </footer>
    </div>
</template>
