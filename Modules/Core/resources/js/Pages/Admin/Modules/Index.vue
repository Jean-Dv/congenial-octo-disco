<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
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
        <p class="mb-6 text-sm text-aeris-on-surface-muted">
            Un modulo nuevo en <code class="font-mono-data text-aeris-on-surface">Modules/</code> aparece automaticamente,
            habilitado por defecto. Core es obligatorio y no se puede deshabilitar.
        </p>

        <div class="space-y-3">
            <AerisCard
                v-for="module in modules"
                :key="module.slug"
                as="article"
                padding="sm"
            >
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-base font-semibold text-aeris-on-surface">{{ module.name }}</p>
                            <span class="font-mono-data text-xs text-aeris-on-surface-muted">v{{ module.version }}</span>
                            <AerisBadge v-if="module.is_core" tone="active">obligatorio</AerisBadge>
                        </div>
                        <p class="mt-1 text-sm text-aeris-on-surface-muted">{{ module.description }}</p>
                    </div>

                    <AerisSwitch
                        :checked="module.enabled"
                        :disabled="module.is_core"
                        :label="`Cambiar estado de ${module.name}`"
                        @toggle="toggle(module)"
                    />
                </div>
            </AerisCard>
        </div>
    </AppLayout>
</template>
