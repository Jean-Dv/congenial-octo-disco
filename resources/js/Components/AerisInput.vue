<script setup>
const model = defineModel({ default: '' });

defineProps({
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    autofocus: { type: Boolean, default: false },
});
</script>

<template>
    <input
        v-model="model"
        :type="type"
        :placeholder="placeholder"
        :autofocus="autofocus"
        class="w-full rounded border border-theme-border bg-theme-surface-container-low px-3.5 py-2.5 text-sm text-theme-on-surface outline-none transition placeholder:text-theme-on-surface-disabled focus:border-theme-primary-container focus:ring-2 focus:ring-theme-primary-container/30"
    />
</template>
