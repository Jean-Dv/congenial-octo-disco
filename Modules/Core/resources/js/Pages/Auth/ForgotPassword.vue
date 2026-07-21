<script setup>
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: { type: String, default: null },
});

const form = useForm({ email: '' });

function submit() {
    form.post('/forgot-password');
}
</script>

<template>
    <Head title="Olvide mi contrasena" />

    <ThemeGuestLayout title="Olvidaste tu contrasena?" subtitle="Te enviamos un enlace para restablecerla">
        <ThemeAlert v-if="status" tone="success" class="mb-5">
            {{ status }}
        </ThemeAlert>

        <form class="space-y-5" @submit.prevent="submit">
            <ThemeField for="email" label="Correo electronico" :error="form.errors.email">
                <ThemeInput id="email" v-model="form.email" type="email" autofocus placeholder="tucorreo@ejemplo.com" />
            </ThemeField>

            <ThemeButton type="submit" :disabled="form.processing" block>Enviar enlace</ThemeButton>
        </form>
    </ThemeGuestLayout>
</template>
