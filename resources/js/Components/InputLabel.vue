<script setup>
defineProps({
    value: { type: String, default: '' },
    for: { type: String, default: '' },
});
</script>

<template>
    <label :for="$attrs.for" class="mb-1.5 block text-sm font-medium text-parchment-300">
        <slot>{{ value }}</slot>
    </label>
</template>
