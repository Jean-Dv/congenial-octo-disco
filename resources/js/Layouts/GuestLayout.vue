<script setup>
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-ink-950">
        <!-- Resplandor de fondo: unico elemento decorativo, evoca un portal -->
        <div
            class="pointer-events-none absolute left-1/2 top-0 h-[600px] w-[900px] -translate-x-1/2 -translate-y-1/3 rounded-full opacity-25 blur-3xl"
            style="background: radial-gradient(circle, var(--color-rune-500) 0%, transparent 70%)"
        />

        <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <div class="mb-8 flex items-center gap-2">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2C8 8 8 20 14 26C20 20 20 8 14 2Z" fill="var(--color-rune-500)" opacity="0.9" />
                    <circle cx="14" cy="14" r="3.2" fill="var(--color-ink-950)" />
                </svg>
                <span class="font-display text-lg font-semibold tracking-wide text-parchment-100">Moon</span>
            </div>

            <div class="w-full max-w-md rounded-2xl border border-ink-700 bg-ink-900/80 p-8 shadow-2xl shadow-black/40 backdrop-blur">
                <div v-if="title" class="mb-6 text-center">
                    <h1 class="font-display text-2xl font-semibold text-parchment-100">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-2 text-sm text-parchment-500">{{ subtitle }}</p>
                </div>

                <slot />
            </div>

            <p class="mt-8 text-xs text-parchment-500">Moon CMS &middot; Panel del reino</p>
        </div>
    </div>
</template>
