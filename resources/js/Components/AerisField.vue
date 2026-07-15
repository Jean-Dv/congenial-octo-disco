<script setup>
const props = defineProps({
    for: { type: String, default: '' },
    label: { type: String, default: '' },
    help: { type: String, default: '' },
    error: { type: String, default: '' },
});
</script>

<template>
    <div>
        <label v-if="label" :for="props.for || undefined" class="mb-1.5 block font-mono-data text-xs font-bold uppercase text-theme-on-surface-variant">
            {{ label }}
        </label>

        <slot />

        <p v-if="help && !error" class="mt-1.5 text-xs text-theme-on-surface-muted">{{ help }}</p>
        <p v-if="error" class="mt-1.5 text-sm text-theme-error">{{ error }}</p>
    </div>
</template>
