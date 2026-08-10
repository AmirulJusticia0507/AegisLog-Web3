<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
});

const styles = {
    pending: 'bg-amber-500/10 text-amber-400 border-amber-500/30',
    verified: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
    tampered: 'bg-red-500/10 text-red-500 border-red-500/30',
};

const label = computed(() => props.status.toUpperCase());
</script>

<template>
    <span
        class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-mono text-xs font-semibold"
        :class="styles[status] || styles.pending"
    >
        {{ label }}
    </span>
</template>
