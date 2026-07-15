<script setup>
import { Head, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';

defineProps({
    realms: { type: Array, default: () => [] },
});

function destroyRealm(realm) {
    if (! confirm(`Eliminar el reino "${realm.name}"? Esto no borra cuentas de juego ya creadas.`)) {
        return;
    }

    router.delete(`/admin/realms/${realm.id}`);
}
</script>

<template>
    <Head title="Reinos" />

    <ThemeAppLayout title="Reinos">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-theme-on-surface-muted">
                Cada reino habilitado recibe una cuenta de juego automaticamente al registrarse un usuario.
            </p>

            <ThemeButton href="/admin/realms/create">
                <Plus class="h-4 w-4" />
                Nuevo reino
            </ThemeButton>
        </div>

        <ThemeTable>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Core</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="realm in realms" :key="realm.id">
                    <td>
                        <p class="font-medium text-theme-on-surface">{{ realm.name }}</p>
                        <p class="font-mono-data text-xs text-theme-on-surface-muted">{{ realm.slug }}</p>
                    </td>
                    <td>
                        <span class="text-theme-on-surface-muted">{{ realm.core_type_label }}</span>
                        <ThemeBadge
                            v-if="!realm.has_full_support"
                            tone="critical"
                            class="ml-2"
                            title="La estrategia de password para este core todavia no esta implementada"
                        >
                            sin implementar
                        </ThemeBadge>
                    </td>
                    <td>
                        <ThemeBadge :tone="realm.enabled ? 'active' : 'muted'">
                            {{ realm.enabled ? 'Habilitado' : 'Deshabilitado' }}
                        </ThemeBadge>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <ThemeIconButton :href="`/admin/realms/${realm.id}/edit`" title="Editar reino">
                                <Pencil class="h-4 w-4" />
                            </ThemeIconButton>
                            <ThemeIconButton title="Eliminar reino" variant="danger" @click="destroyRealm(realm)">
                                <Trash2 class="h-4 w-4" />
                            </ThemeIconButton>
                        </div>
                    </td>
                </tr>
                <tr v-if="realms.length === 0">
                    <td colspan="4" class="py-8 text-center text-theme-on-surface-muted">
                        Todavia no hay reinos configurados.
                    </td>
                </tr>
            </tbody>
        </ThemeTable>
    </ThemeAppLayout>
</template>
