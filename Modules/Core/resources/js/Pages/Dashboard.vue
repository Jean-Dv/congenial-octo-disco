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
            <div class="rounded-xl border border-ink-700 bg-ink-900 p-6">
                <h2 class="font-display text-base font-semibold text-parchment-100">Tu cuenta de juego</h2>
                <p class="mt-1 text-sm text-parchment-500">
                    Estado de tu personaje en cada reino. Si algun reino aparece "Pendiente", puedes entrar al panel con
                    normalidad: se esta creando en segundo plano.
                </p>

                <div v-if="realmStatuses.length === 0" class="mt-6 text-sm text-parchment-500">
                    Todavia no hay ningun reino configurado en este CMS.
                </div>

                <ul v-else class="mt-6 divide-y divide-ink-700">
                    <li v-for="(row, index) in realmStatuses" :key="index" class="flex items-center justify-between py-3">
                        <div>
                            <p class="text-sm font-medium text-parchment-100">{{ row.realm_name }}</p>
                            <p v-if="row.last_error" class="mt-0.5 max-w-md text-xs text-garnet-400">{{ row.last_error }}</p>
                        </div>
                        <StatusBadge :status="row.status">{{ row.status_label }}</StatusBadge>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
