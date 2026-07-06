<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    modules: { type: Array, default: () => [] },
});

function toggle(module) {
    if (module.is_core) {
        return;
    }

    router.patch(`/admin/modules/${module.slug}`, { enabled: !module.enabled }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Modulos" />

    <AppLayout title="Modulos">
        <p class="mb-6 text-sm text-parchment-500">
            Un modulo nuevo (carpeta añadida en <code class="font-mono-data">Modules/</code>) aparece aqui automaticamente,
            habilitado por defecto. "Core" es obligatorio y no se puede deshabilitar.
        </p>

        <div class="space-y-3">
            <div
                v-for="module in modules"
                :key="module.slug"
                class="flex items-center justify-between rounded-xl border border-ink-700 bg-ink-900 p-5"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-display text-base font-semibold text-parchment-100">{{ module.name }}</p>
                        <span class="font-mono-data text-xs text-parchment-500">v{{ module.version }}</span>
                        <span v-if="module.is_core" class="rounded-full bg-rune-500/15 px-2 py-0.5 text-xs text-rune-400">obligatorio</span>
                    </div>
                    <p class="mt-1 text-sm text-parchment-500">{{ module.description }}</p>
                </div>

                <button
                    type="button"
                    :disabled="module.is_core"
                    @click="toggle(module)"
                    class="relative h-6 w-11 shrink-0 rounded-full transition disabled:cursor-not-allowed disabled:opacity-50"
                    :class="module.enabled ? 'bg-rune-500' : 'bg-ink-700'"
                >
                    <span
                        class="absolute top-0.5 h-5 w-5 rounded-full bg-parchment-100 transition"
                        :class="module.enabled ? 'left-5' : 'left-0.5'"
                    />
                </button>
            </div>
        </div>
    </AppLayout>
</template>
