<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    realmStatuses: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout title="Dashboard">
        <div class="max-w-2xl">
            <AerisCard title="Tu cuenta de juego" tone="important">
                <p class="text-sm text-aeris-on-surface-muted">
                    Estado de tu personaje en cada reino. Si algun reino aparece "Pendiente", puedes entrar al panel con normalidad.
                </p>

                <div v-if="realmStatuses.length === 0" class="mt-6 text-sm text-aeris-on-surface-muted">
                    Todavia no hay ningun reino configurado en este CMS.
                </div>

                <ul v-else class="mt-6 divide-y divide-aeris-border">
                    <li v-for="(row, index) in realmStatuses" :key="index" class="flex items-center justify-between py-3">
                        <div>
                            <p class="text-sm font-medium text-aeris-on-surface">{{ row.realm_name }}</p>
                            <p v-if="row.last_error" class="mt-0.5 max-w-md text-xs text-aeris-error">{{ row.last_error }}</p>
                        </div>
                        <StatusBadge :status="row.status">{{ row.status_label }}</StatusBadge>
                    </li>
                </ul>
            </AerisCard>
        </div>
    </AppLayout>
</template>
