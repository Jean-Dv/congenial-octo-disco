<script setup>
const props = defineProps({
    status: { type: String, required: true },
});

const styles = {
    pending: 'text-theme-on-surface-muted',
    in_progress: 'text-theme-primary',
    ready: 'text-theme-primary',
    failed: 'text-theme-error',
};
</script>

<template>
    <span class="status-pulse inline-flex items-center gap-2 text-sm font-medium" :class="styles[props.status] ?? 'text-theme-on-surface-muted'">
        <span class="h-2 w-2 rounded-full" :class="{
            'bg-theme-on-surface-muted': status === 'pending',
            'bg-theme-primary-container': status === 'in_progress' || status === 'ready',
            'bg-theme-error': status === 'failed',
        }" />
        <slot />
    </span>
</template>
