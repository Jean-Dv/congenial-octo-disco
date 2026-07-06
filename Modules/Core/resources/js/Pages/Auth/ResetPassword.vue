<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Restablecer contraseña" />

    <GuestLayout title="Restablece tu contraseña" subtitle="Se actualizara en el panel y en cada reino">
        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electronico" />
                <TextInput id="email" v-model="form.email" type="email" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Nueva contraseña" />
                <TextInput id="password" v-model="form.password" type="password" autofocus placeholder="Maximo 16 caracteres" />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar nueva contraseña" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton :disabled="form.processing">Restablecer contraseña</PrimaryButton>
        </form>
    </GuestLayout>
</template>
