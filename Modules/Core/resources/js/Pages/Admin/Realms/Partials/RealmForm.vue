<script setup>
import { ref } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    coreTypes: { type: Array, required: true },
    submitLabel: { type: String, default: 'Guardar' },
});

const emit = defineEmits(['submit']);

const includeCharactersDb = ref(!!(props.form.characters_database && props.form.characters_database.host));

function toggleCharactersDb() {
    includeCharactersDb.value = !includeCharactersDb.value;
    if (!includeCharactersDb.value) {
        props.form.characters_database = null;
    } else if (!props.form.characters_database) {
        props.form.characters_database = { host: '', port: 3306, database: '', username: '', password: '' };
    }
}
</script>

<template>
    <form class="space-y-8" @submit.prevent="emit('submit')">
        <AerisCard title="Datos generales" tone="important">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <AerisField label="Nombre" :error="form.errors['name']">
                    <AerisInput v-model="form.name" placeholder="Reino Azuremyst" />
                </AerisField>
                <AerisField label="Slug" :error="form.errors['slug']">
                    <AerisInput v-model="form.slug" placeholder="azuremyst" />
                </AerisField>
                <AerisField label="Core" :error="form.errors['core_type']">
                    <AerisSelect v-model="form.core_type">
                        <option v-for="core in coreTypes" :key="core.value" :value="core.value">
                            {{ core.label }}{{ core.has_full_support ? '' : ' (sin implementar todavia)' }}
                        </option>
                    </AerisSelect>
                </AerisField>
                <AerisField label="RealmID para GM (account_access)" :error="form.errors['gm_realm_id']">
                    <AerisInput v-model="form.gm_realm_id" type="number" placeholder="-1 (todos los reinos)" />
                </AerisField>
            </div>

            <AerisCheckbox v-model="form.enabled" class="mt-5">
                Reino habilitado (se aprovisionan cuentas de juego automaticamente aqui)
            </AerisCheckbox>
        </AerisCard>

        <AerisCard title='Base de datos "auth"' subtitle="Se guarda cifrada. Se resuelve en caliente, no toca tu config/database.php.">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <AerisField label="Host" :error="form.errors['auth_database.host']">
                    <AerisInput v-model="form.auth_database.host" placeholder="127.0.0.1" />
                </AerisField>
                <AerisField label="Puerto" :error="form.errors['auth_database.port']">
                    <AerisInput v-model="form.auth_database.port" type="number" placeholder="3306" />
                </AerisField>
                <AerisField label="Base de datos" :error="form.errors['auth_database.database']">
                    <AerisInput v-model="form.auth_database.database" placeholder="auth" />
                </AerisField>
                <AerisField label="Usuario" :error="form.errors['auth_database.username']">
                    <AerisInput v-model="form.auth_database.username" placeholder="trinity" />
                </AerisField>
                <AerisField label="Contrasena" :error="form.errors['auth_database.password']">
                    <AerisInput v-model="form.auth_database.password" type="password" />
                </AerisField>
            </div>
        </AerisCard>

        <AerisCard title='Base de datos "characters"' subtitle="Opcional en esta version del core: la usaran los modulos futuros que lean personajes.">
            <template #actions>
                <AerisButton type="button" variant="ghost" size="sm" @click="toggleCharactersDb">
                    {{ includeCharactersDb ? 'Quitar' : 'Anadir' }}
                </AerisButton>
            </template>

            <div v-if="includeCharactersDb" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <AerisField label="Host">
                    <AerisInput v-model="form.characters_database.host" placeholder="127.0.0.1" />
                </AerisField>
                <AerisField label="Puerto">
                    <AerisInput v-model="form.characters_database.port" type="number" placeholder="3306" />
                </AerisField>
                <AerisField label="Base de datos">
                    <AerisInput v-model="form.characters_database.database" placeholder="characters" />
                </AerisField>
                <AerisField label="Usuario">
                    <AerisInput v-model="form.characters_database.username" placeholder="trinity" />
                </AerisField>
                <AerisField label="Contrasena">
                    <AerisInput v-model="form.characters_database.password" type="password" />
                </AerisField>
            </div>
        </AerisCard>

        <AerisCard title="Consola remota (SOAP)" subtitle='Cuenta GM (rango 3+) del worldserver, usada para "server info", kick y comandos de personaje.'>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <AerisField label="Host" :error="form.errors['remote_console.host']">
                    <AerisInput v-model="form.remote_console.host" placeholder="127.0.0.1" />
                </AerisField>
                <AerisField label="Puerto" :error="form.errors['remote_console.port']">
                    <AerisInput v-model="form.remote_console.port" type="number" placeholder="7878" />
                </AerisField>
                <AerisField label="Usuario GM" :error="form.errors['remote_console.username']">
                    <AerisInput v-model="form.remote_console.username" />
                </AerisField>
                <AerisField label="Contrasena" :error="form.errors['remote_console.password']">
                    <AerisInput v-model="form.remote_console.password" type="password" />
                </AerisField>
            </div>
        </AerisCard>

        <AerisButton type="submit" :disabled="form.processing">{{ submitLabel }}</AerisButton>
    </form>
</template>
