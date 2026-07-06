<script setup>
const props = defineProps({
    status: { type: String, required: true },
});

const styles = {
    pending: 'text-parchment-500',
    in_progress: 'text-spectral-400',
    ready: 'text-spectral-400',
    failed: 'text-garnet-400',
};
</script>

<template>
    <span class="status-pulse inline-flex items-center gap-2 text-sm font-medium" :class="styles[props.status] ?? 'text-parchment-500'">
        <span class="h-2 w-2 rounded-full" :class="{
            'bg-parchment-500': status === 'pending',
            'bg-spectral-400': status === 'in_progress' || status === 'ready',
            'bg-garnet-400': status === 'failed',
        }" />
        <slot />
    </span>
</template>
