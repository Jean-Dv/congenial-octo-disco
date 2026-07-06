<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    realms: { type: Array, default: () => [] },
});

function destroyRealm(realm) {
    if (! confirm(`¿Eliminar el reino "${realm.name}"? Esto no borra cuentas de juego ya creadas.`)) {
        return;
    }

    router.delete(`/admin/realms/${realm.id}`);
}
</script>

<template>
    <Head title="Reinos" />

    <AppLayout title="Reinos">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-parchment-500">Cada reino habilitado recibe una cuenta de juego automaticamente al registrarse un usuario.</p>
            <Link
                href="/admin/realms/create"
                class="inline-flex items-center gap-2 rounded-lg bg-rune-500 px-4 py-2 text-sm font-semibold text-ink-950 hover:bg-rune-400"
            >
                <Plus class="h-4 w-4" />
                Nuevo reino
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-ink-700 bg-ink-900">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ink-700 text-xs uppercase tracking-wider text-parchment-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nombre</th>
                        <th class="px-5 py-3 font-medium">Core</th>
                        <th class="px-5 py-3 font-medium">Estado</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-700">
                    <tr v-for="realm in realms" :key="realm.id">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-parchment-100">{{ realm.name }}</p>
                            <p class="font-mono-data text-xs text-parchment-500">{{ realm.slug }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-parchment-300">{{ realm.core_type_label }}</span>
                            <span
                                v-if="!realm.has_full_support"
                                class="ml-2 rounded-full bg-garnet-500/15 px-2 py-0.5 text-xs text-garnet-400"
                                title="La estrategia de password para este core todavia no esta implementada"
                            >
                                sin implementar
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span :class="realm.enabled ? 'text-spectral-400' : 'text-parchment-500'">
                                {{ realm.enabled ? 'Habilitado' : 'Deshabilitado' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/realms/${realm.id}/edit`" class="rounded-md p-2 text-parchment-500 hover:bg-ink-800 hover:text-parchment-100">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button @click="destroyRealm(realm)" class="rounded-md p-2 text-parchment-500 hover:bg-ink-800 hover:text-garnet-400">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="realms.length === 0">
                        <td colspan="4" class="px-5 py-8 text-center text-parchment-500">Todavia no hay reinos configurados.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
