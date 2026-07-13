<script setup>
const props = defineProps({
    status: { type: String, required: true },
});

const styles = {
    pending: 'text-aeris-on-surface-muted',
    in_progress: 'text-aeris-primary',
    ready: 'text-aeris-primary',
    failed: 'text-aeris-error',
};
</script>

<template>
    <span class="status-pulse inline-flex items-center gap-2 text-sm font-medium" :class="styles[props.status] ?? 'text-aeris-on-surface-muted'">
        <span class="h-2 w-2 rounded-full" :class="{
            'bg-aeris-on-surface-muted': status === 'pending',
            'bg-aeris-primary-container': status === 'in_progress' || status === 'ready',
            'bg-aeris-error': status === 'failed',
        }" />
        <slot />
    </span>
</template>
