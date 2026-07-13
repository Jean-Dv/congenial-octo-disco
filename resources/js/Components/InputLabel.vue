<script setup>
const props = defineProps({
    value: { type: String, default: '' },
    for: { type: String, default: '' },
});
</script>

<template>
    <label :for="props.for || undefined" class="mb-1.5 block font-mono-data text-xs font-bold uppercase text-aeris-on-surface-variant">
        <slot>{{ value }}</slot>
    </label>
</template>
