<script setup>
import RealmForm from './Partials/RealmForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    realm: { type: Object, required: true },
    coreTypes: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.realm.name,
    slug: props.realm.slug,
    core_type: props.realm.core_type,
    gm_realm_id: props.realm.gm_realm_id,
    enabled: props.realm.enabled,
    auth_database: { ...props.realm.auth_database },
    characters_database: props.realm.characters_database ? { ...props.realm.characters_database } : null,
    remote_console: { ...props.realm.remote_console },
});

function submit() {
    form.put(`/admin/realms/${props.realm.id}`);
}
</script>

<template>
    <Head title="Editar reino" />

    <ThemeAppLayout :title="`Editar reino: ${realm.name}`">
        <RealmForm :form="form" :core-types="coreTypes" :is-editing="true" submit-label="Guardar cambios" @submit="submit" />
    </ThemeAppLayout>
</template>
