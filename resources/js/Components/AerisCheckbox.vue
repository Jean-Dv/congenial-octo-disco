<script setup>
const model = defineModel({ type: Boolean, default: false });

defineProps({
    label: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
});
</script>

<template>
    <label class="flex items-center gap-2 text-sm text-theme-on-surface-muted">
        <input
            v-model="model"
            type="checkbox"
            :disabled="disabled"
            class="rounded border-theme-border bg-theme-surface-container-low text-theme-primary-container focus:ring-theme-primary-container/35 disabled:cursor-not-allowed disabled:opacity-50"
        />
        <span><slot>{{ label }}</slot></span>
    </label>
</template>
