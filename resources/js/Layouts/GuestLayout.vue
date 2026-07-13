<script setup>
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div class="aeris-grid-shell min-h-screen overflow-hidden">
        <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <div class="mb-8">
                <AerisLogo />
            </div>

            <AerisCard class="w-full max-w-md" tone="important">
                <div v-if="title" class="mb-6 text-center">
                    <h1 class="text-2xl font-semibold text-aeris-on-surface">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-2 text-sm text-aeris-on-surface-muted">{{ subtitle }}</p>
                </div>

                <slot />
            </AerisCard>

            <p class="mt-8 text-xs text-aeris-on-surface-muted">Moon CMS &middot; Panel del reino</p>
        </div>
    </div>
</template>
