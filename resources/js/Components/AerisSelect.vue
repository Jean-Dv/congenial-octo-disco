<script setup>
const model = defineModel({ default: '' });

defineProps({
    options: { type: Array, default: () => [] },
});
</script>

<template>
    <select
        v-model="model"
        class="w-full rounded border border-theme-border bg-theme-surface-container-low px-3.5 py-2.5 text-sm text-theme-on-surface outline-none transition focus:border-theme-primary-container focus:ring-2 focus:ring-theme-primary-container/30"
    >
        <slot>
            <option v-for="option in options" :key="option.value ?? option" :value="option.value ?? option">
                {{ option.label ?? option }}
            </option>
        </slot>
    </select>
</template>
