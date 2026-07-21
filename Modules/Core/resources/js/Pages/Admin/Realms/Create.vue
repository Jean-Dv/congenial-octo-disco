<script setup>
import RealmForm from './Partials/RealmForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    coreTypes: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    slug: '',
    core_type: 'trinitycore',
    gm_realm_id: -1,
    enabled: true,
    auth_database: { host: '', port: 3306, database: 'auth', username: '', password: '' },
    characters_database: null,
    remote_console: { host: '', port: 7878, username: '', password: '' },
});

function submit() {
    form.post('/admin/realms');
}
</script>

<template>
    <Head title="Nuevo reino" />

    <ThemeAppLayout title="Nuevo reino">
        <RealmForm :form="form" :core-types="coreTypes" submit-label="Crear reino" @submit="submit" />
    </ThemeAppLayout>
</template>
