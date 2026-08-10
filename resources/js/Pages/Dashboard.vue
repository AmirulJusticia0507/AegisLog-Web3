<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useAccount, useConnect } from '@wagmi/vue';
import StatusBadge from '../Components/StatusBadge.vue';
import { useTamperAlerts } from '../composables/useTamperAlerts';

const props = defineProps({
    stats: { type: Object, required: true },
    chart: { type: Object, required: true },
    recentLogs: { type: Array, default: () => [] },
});

const { address, isConnected } = useAccount();
const { connect, connectors } = useConnect();
const { alerts } = useTamperAlerts();

const liveTamperedIds = computed(() => new Set(alerts.value.map((a) => a.id)));

const rows = computed(() =>
    props.recentLogs.map((log) => ({
        ...log,
        live_tampered: liveTamperedIds.value.has(log.id),
    }))
);

const shortAddress = computed(() => {
    if (!address.value) return null;

    return `${address.value.slice(0, 6)}...${address.value.slice(-4)}`;
});

const chartDays = computed(() => Object.keys(props.chart).slice(-14));

const chartPoints = computed(() => chartDays.value.map((day) => props.chart[day]));

const maxValue = computed(() => {
    const peaks = chartPoints.value.map((p) => p.total).concat([1]);

    return Math.max(...peaks);
});

const polyline = computed(() => {
    const width = 100;
    const height = 40;
    const step = width / Math.max(1, chartPoints.value.length - 1);

    return chartPoints.value
        .map((p, i) => {
            const x = i * step;
            const y = height - (p.total / maxValue.value) * height;

            return `${i === 0 ? 'M' : 'L'}${x.toFixed(2)},${y.toFixed(2)}`;
        })
        .join(' ');
});

const bars = computed(() =>
    chartPoints.value.map((p, i) => ({
        height: (p.tampered / maxValue.value) * 40,
        index: i,
    }))
);

const statsCards = computed(() => [
    { label: 'Total Logs', value: props.stats.total, accent: 'text-indigo-400', border: 'border-indigo-800/60' },
    { label: 'Anchored On-Chain', value: props.stats.anchored, accent: 'text-emerald-400', border: 'border-emerald-800/60' },
    { label: 'Pending', value: props.stats.pending, accent: 'text-amber-400', border: 'border-amber-800/60' },
    { label: 'Tamper Events', value: props.stats.tampered, accent: 'text-red-400', border: 'border-red-800/60' },
]);
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

                <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div
                        v-for="card in statsCards"
                        :key="card.label"
                        class="rounded-lg border border-slate-800 bg-slate-950/60 p-4"
                    >
                        <p class="text-xs uppercase tracking-wider text-slate-500">{{ card.label }}</p>
                        <p class="mt-2 font-mono text-2xl font-semibold" :class="card.accent">{{ card.value }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-100">Anchoring Pipeline</h2>
                    <span class="font-mono text-xs text-slate-500">cron: audit:verify (1 min)</span>
                </div>
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

            <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-100">Volume &amp; Tamper Trend</h2>
                    <span class="font-mono text-xs text-slate-500">14 hari terakhir</span>
                </div>

                <div class="mt-4">
                    <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="h-32 w-full">
                        <path
                            d="M0,40 L100,40"
                            class="stroke-slate-800"
                            stroke-width="0.4"
                            fill="none"
                        />
                        <path
                            :d="polyline"
                            class="stroke-indigo-400"
                            stroke-width="1"
                            fill="none"
                        />
                        <rect
                            v-for="bar in bars"
                            :key="bar.index"
                            :x="bar.index * (100 / Math.max(1, chartPoints.length - 1)) + 1"
                            :y="40 - bar.height"
                            :width="100 / Math.max(1, chartPoints.length) / 3"
                            :height="bar.height"
                            class="fill-red-500/70"
                        />
                    </svg>

                    <div class="mt-1 flex justify-between font-mono text-[10px] text-slate-600">
                        <span>{{ chartDays[0] }}</span>
                        <span>{{ chartDays[chartDays.length - 1] }}</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center space-x-4 text-xs text-slate-500">
                    <span class="flex items-center space-x-1.5">
                        <span class="h-0.5 w-4 bg-indigo-400 inline-block"></span>
                        <span>Anchored total</span>
                    </span>
                    <span class="flex items-center space-x-1.5">
                        <span class="h-2 w-2 bg-red-500/70 inline-block"></span>
                        <span>Tampered</span>
                    </span>
                </div>
            </div>

            <div class="p-6 rounded-xl border border-slate-800 bg-slate-900/80">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-100">Recent Audit Logs</h2>
                    <span class="font-mono text-xs text-slate-500">live sync</span>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-slate-500 border-b border-slate-800">
                                <th class="py-2 pr-4">Judul</th>
                                <th class="py-2 pr-4">Pengunggah</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in rows" :key="log.id" class="border-b border-slate-800/60">
                                <td class="py-3 pr-4 text-slate-200 font-medium">
                                    <span class="flex items-center space-x-2">
                                        <span
                                            v-if="log.live_tampered"
                                            class="w-2 h-2 rounded-full bg-red-500 animate-pulse"
                                        ></span>
                                        <span>{{ log.title }}</span>
                                    </span>
                                </td>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-400">
                                    {{ log.user?.wallet_address?.slice(0, 10) }}…
                                </td>
                                <td class="py-3">
                                    <StatusBadge :status="log.live_tampered ? 'tampered' : log.integrity_status" />
                                </td>
                            </tr>
                            <tr v-if="rows.length === 0">
                                <td colspan="3" class="py-8 text-center text-sm text-slate-500">
                                    Belum ada log audit.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
