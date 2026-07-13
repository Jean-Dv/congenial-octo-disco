<script setup>
const model = defineModel({ type: Boolean, default: false });

defineProps({
    label: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
});
</script>

<template>
    <label class="flex items-center gap-2 text-sm text-aeris-on-surface-muted">
        <input
            v-model="model"
            type="checkbox"
            :disabled="disabled"
            class="rounded border-aeris-border bg-aeris-surface-container-low text-aeris-primary-container focus:ring-aeris-primary-container/35 disabled:cursor-not-allowed disabled:opacity-50"
        />
        <span><slot>{{ label }}</slot></span>
    </label>
</template>
