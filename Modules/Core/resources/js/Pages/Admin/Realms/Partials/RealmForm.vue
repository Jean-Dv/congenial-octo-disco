<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    coreTypes: { type: Array, required: true },
    submitLabel: { type: String, default: 'Guardar' },
    isEditing: { type: Boolean, default: false },
});

const emit = defineEmits(['submit']);

const includeCharactersDb = ref(!!(props.form.characters_database && props.form.characters_database.host));

watch(
    () => props.form.connection_type,
    (connectionType) => {
        if (connectionType === 'ssh' && !props.form.ssh_tunnel) {
            props.form.ssh_tunnel = {
                host: '',
                port: 22,
                username: '',
                private_key: '',
                private_key_passphrase: '',
            };
        }

        if (connectionType === 'direct') {
            props.form.ssh_tunnel = null;
        }
    },
);

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
        <ThemeCard title="Datos generales" tone="important">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <ThemeField label="Nombre" :error="form.errors['name']">
                    <ThemeInput v-model="form.name" placeholder="Reino Azuremyst" />
                </ThemeField>
                <ThemeField label="Slug" :error="form.errors['slug']">
                    <ThemeInput v-model="form.slug" placeholder="azuremyst" />
                </ThemeField>
                <ThemeField label="Core" :error="form.errors['core_type']">
                    <ThemeSelect v-model="form.core_type">
                        <option v-for="core in coreTypes" :key="core.value" :value="core.value">
                            {{ core.label }}{{ core.has_full_support ? '' : ' (sin implementar todavia)' }}
                        </option>
                    </ThemeSelect>
                </ThemeField>
                <ThemeField label="RealmID para GM (account_access)" :error="form.errors['gm_realm_id']">
                    <ThemeInput v-model="form.gm_realm_id" type="number" placeholder="-1 (todos los reinos)" />
                </ThemeField>
            </div>

            <ThemeCheckbox v-model="form.enabled" class="mt-5">
                Reino habilitado (se aprovisionan cuentas de juego automaticamente aqui)
            </ThemeCheckbox>
        </ThemeCard>

        <ThemeCard
            title="Acceso a las bases de datos"
            subtitle="Usa conexión directa si MySQL es accesible desde el CMS, o un túnel SSH si está en una red privada."
        >
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <ThemeField label="Modo de conexión" :error="form.errors['connection_type']">
                    <ThemeSelect v-model="form.connection_type">
                        <option value="direct">Conexión directa</option>
                        <option value="ssh">Túnel SSH</option>
                    </ThemeSelect>
                </ThemeField>
            </div>

            <div v-if="form.connection_type === 'ssh' && form.ssh_tunnel" class="mt-5 space-y-5">
                <p v-if="form.errors['ssh_tunnel']" class="text-sm text-theme-error">
                    {{ form.errors['ssh_tunnel'] }}
                </p>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <ThemeField label="Servidor SSH" :error="form.errors['ssh_tunnel.host']">
                        <ThemeInput v-model="form.ssh_tunnel.host" placeholder="bastion.example.com" />
                    </ThemeField>
                    <ThemeField label="Puerto SSH" :error="form.errors['ssh_tunnel.port']">
                        <ThemeInput v-model="form.ssh_tunnel.port" type="number" placeholder="22" />
                    </ThemeField>
                    <ThemeField label="Usuario SSH" :error="form.errors['ssh_tunnel.username']">
                        <ThemeInput v-model="form.ssh_tunnel.username" placeholder="moon-cms" />
                    </ThemeField>
                    <ThemeField label="Passphrase de la llave (opcional)" :error="form.errors['ssh_tunnel.private_key_passphrase']">
                        <ThemeInput
                            v-model="form.ssh_tunnel.private_key_passphrase"
                            type="password"
                            :placeholder="isEditing ? 'Dejar vacía para conservarla junto con la llave' : ''"
                        />
                    </ThemeField>
                </div>

                <ThemeField label="Llave privada SSH" :error="form.errors['ssh_tunnel.private_key']">
                    <textarea
                        v-model="form.ssh_tunnel.private_key"
                        rows="8"
                        spellcheck="false"
                        autocomplete="off"
                        class="w-full rounded border border-theme-border bg-theme-surface-container-low px-3.5 py-2.5 font-mono-data text-sm text-theme-on-surface outline-none transition placeholder:text-theme-on-surface-disabled focus:border-theme-primary-container focus:ring-2 focus:ring-theme-primary-container/30"
                        :placeholder="isEditing ? 'Dejar vacía para conservar la llave actual' : '-----BEGIN OPENSSH PRIVATE KEY-----'"
                    />
                </ThemeField>

                <p class="text-xs text-theme-on-surface-muted">
                    La llave y su passphrase se guardan cifradas. Los hosts de auth y characters son los destinos vistos desde este servidor SSH.
                </p>
            </div>
        </ThemeCard>

        <ThemeCard title='Base de datos "auth"' subtitle="Se guarda cifrada. Se resuelve en caliente, no toca tu config/database.php.">
            <p v-if="form.errors['auth_database']" class="mb-4 text-sm text-theme-error">
                {{ form.errors['auth_database'] }}
            </p>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <ThemeField label="Host" :error="form.errors['auth_database.host']">
                    <ThemeInput v-model="form.auth_database.host" placeholder="127.0.0.1" />
                </ThemeField>
                <ThemeField label="Puerto" :error="form.errors['auth_database.port']">
                    <ThemeInput v-model="form.auth_database.port" type="number" placeholder="3306" />
                </ThemeField>
                <ThemeField label="Base de datos" :error="form.errors['auth_database.database']">
                    <ThemeInput v-model="form.auth_database.database" placeholder="auth" />
                </ThemeField>
                <ThemeField label="Usuario" :error="form.errors['auth_database.username']">
                    <ThemeInput v-model="form.auth_database.username" placeholder="trinity" />
                </ThemeField>
                <ThemeField label="Contrasena" :error="form.errors['auth_database.password']">
                    <ThemeInput v-model="form.auth_database.password" type="password" :placeholder="isEditing ? 'Dejar vacia para conservarla' : ''" />
                </ThemeField>
            </div>
        </ThemeCard>

        <ThemeCard title='Base de datos "characters"' subtitle="Opcional en esta version del core: la usaran los modulos futuros que lean personajes.">
            <template #actions>
                <ThemeButton type="button" variant="ghost" size="sm" @click="toggleCharactersDb">
                    {{ includeCharactersDb ? 'Quitar' : 'Anadir' }}
                </ThemeButton>
            </template>

            <p v-if="form.errors['characters_database']" class="mb-4 text-sm text-theme-error">
                {{ form.errors['characters_database'] }}
            </p>

            <div v-if="includeCharactersDb" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <ThemeField label="Host">
                    <ThemeInput v-model="form.characters_database.host" placeholder="127.0.0.1" />
                </ThemeField>
                <ThemeField label="Puerto">
                    <ThemeInput v-model="form.characters_database.port" type="number" placeholder="3306" />
                </ThemeField>
                <ThemeField label="Base de datos">
                    <ThemeInput v-model="form.characters_database.database" placeholder="characters" />
                </ThemeField>
                <ThemeField label="Usuario">
                    <ThemeInput v-model="form.characters_database.username" placeholder="trinity" />
                </ThemeField>
                <ThemeField label="Contrasena">
                    <ThemeInput v-model="form.characters_database.password" type="password" :placeholder="isEditing ? 'Dejar vacia para conservarla' : ''" />
                </ThemeField>
            </div>
        </ThemeCard>

        <ThemeCard title="Consola remota (SOAP)" subtitle='Cuenta GM (rango 3+) del worldserver, usada para "server info", kick y comandos de personaje.'>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <ThemeField label="Host" :error="form.errors['remote_console.host']">
                    <ThemeInput v-model="form.remote_console.host" placeholder="127.0.0.1" />
                </ThemeField>
                <ThemeField label="Puerto" :error="form.errors['remote_console.port']">
                    <ThemeInput v-model="form.remote_console.port" type="number" placeholder="7878" />
                </ThemeField>
                <ThemeField label="Usuario GM" :error="form.errors['remote_console.username']">
                    <ThemeInput v-model="form.remote_console.username" />
                </ThemeField>
                <ThemeField label="Contrasena" :error="form.errors['remote_console.password']">
                    <ThemeInput v-model="form.remote_console.password" type="password" :placeholder="isEditing ? 'Dejar vacia para conservarla' : ''" />
                </ThemeField>
            </div>
        </ThemeCard>

        <ThemeButton type="submit" :disabled="form.processing">{{ submitLabel }}</ThemeButton>
    </form>
</template>
