<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
});
</script>

<template>
    <Link
        :href="href"
        class="relative flex items-center gap-3 rounded px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aeris-primary-container/45"
        :class="active ? 'bg-aeris-primary-container/10 text-aeris-on-surface' : 'text-aeris-on-surface-muted hover:bg-aeris-primary-container/10 hover:text-aeris-on-surface'"
    >
        <span v-if="active" class="absolute left-0 top-1/2 h-5 w-0.5 -translate-y-1/2 rounded-full bg-aeris-primary-container" />
        <slot name="icon" />
        <span><slot /></span>
    </Link>
</template>
