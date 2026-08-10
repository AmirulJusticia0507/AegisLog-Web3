<script setup>
import { Head } from '@inertiajs/vue3';
import { useAccount, useConnect } from '@wagmi/vue';
import { computed } from 'vue';

const { address, isConnected } = useAccount();
const { connect, connectors } = useConnect();

const shortAddress = computed(() => {
    if (!address.value) return null;

    return `${address.value.slice(0, 6)}...${address.value.slice(-4)}`;
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
                <h1 class="text-2xl font-bold text-slate-100">Security Audit Vault</h1>
                <p class="mt-2 text-sm text-slate-400">
                    Platform penjangkaran hash SHA-256 berkas audit ke blockchain. Manipulasi data off-chain
                    akan terdeteksi real-time dan ditandai <span class="text-red-500 font-semibold">TAMPERED</span>.
                </p>

                <div class="mt-6 grid sm:grid-cols-3 gap-4">
                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-500">Status Vault</p>
                        <p class="mt-2 font-mono text-sm font-semibold text-emerald-400">VALID</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-500">Logs Anchored</p>
                        <p class="mt-2 font-mono text-sm font-semibold text-indigo-400">0</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-500">Tamper Events</p>
                        <p class="mt-2 font-mono text-sm font-semibold text-amber-400">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
                <h2 class="text-lg font-semibold text-slate-100">Anchoring Pipeline</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-400">
                    <li class="flex items-start space-x-3">
                        <span class="font-mono text-indigo-400">01</span>
                        <span>Scan / upload laporan audit (PDF/JSON/LOG)</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="font-mono text-indigo-400">02</span>
                        <span>Hitung hash SHA-256 di browser (Web Crypto API)</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="font-mono text-indigo-400">03</span>
                        <span>Sign transaksi via wallet, hash dikunci on-chain (AuditVault.sol)</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="font-mono text-indigo-400">04</span>
                        <span>Worker memverifikasi berkala hash DB vs on-chain</span>
                    </li>
                </ol>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
                <h2 class="text-sm uppercase tracking-wider text-slate-500">Web3 Session</h2>

                <div v-if="isConnected" class="mt-4 rounded-lg border border-emerald-800/60 bg-emerald-950/40 px-4 py-3">
                    <p class="text-xs text-emerald-400">Wallet connected</p>
                    <p class="mt-1 font-mono text-sm text-slate-200">{{ shortAddress }}</p>
                </div>

                <button
                    v-else
                    v-for="connector in connectors"
                    :key="connector.uid"
                    @click="connect({ connector })"
                    class="mt-4 w-full rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white transition"
                >
                    Connect {{ connector.name }}
                </button>
            </div>

            <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
                <h2 class="text-sm uppercase tracking-wider text-slate-500">Sample Hash</h2>
                <div class="mt-4 flex items-center space-x-2 font-mono text-xs bg-slate-950/80 px-3 py-1.5 rounded border border-slate-800 text-slate-300">
                    <span class="text-indigo-400 font-bold">SHA256:</span>
                    <span class="truncate w-48">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</span>
                </div>
            </div>
        </aside>
    </div>
</template>
