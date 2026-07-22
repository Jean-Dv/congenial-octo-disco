<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Trash2, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

defineProps({ categories: { type: Array, default: () => [] } });
const editingId = ref(null);
const slugTouched = ref(false);
const form = useForm({ name: '', slug: '' });

function slugify(value) {
    return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
}
watch(() => form.name, (name) => { if (!slugTouched.value) form.slug = slugify(name); });

function edit(category) {
    editingId.value = category.id;
    form.name = category.name;
    form.slug = category.slug;
    slugTouched.value = true;
    form.clearErrors();
}
function reset() {
    editingId.value = null;
    slugTouched.value = false;
    form.reset();
    form.clearErrors();
}
function submit() {
    if (editingId.value) form.put(`/admin/news/categories/${editingId.value}`, { onSuccess: reset });
    else form.post('/admin/news/categories', { onSuccess: reset });
}
function destroyCategory(category) {
    if (confirm(`Eliminar la categoria "${category.name}"?`)) router.delete(`/admin/news/categories/${category.id}`);
}
</script>

<template>
    <Head title="Categorias de noticias" />
    <ThemeAppLayout title="Categorias de noticias">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <ThemeCard :title="editingId ? 'Editar categoria' : 'Nueva categoria'" tone="important">
                <form class="space-y-4" @submit.prevent="submit">
                    <ThemeField label="Nombre" :error="form.errors.name"><ThemeInput v-model="form.name" placeholder="Actualizaciones" /></ThemeField>
                    <ThemeField label="Slug" :error="form.errors.slug"><ThemeInput v-model="form.slug" placeholder="actualizaciones" @input="slugTouched = true" /></ThemeField>
                    <div class="flex gap-2">
                        <ThemeButton type="submit" :disabled="form.processing">{{ editingId ? 'Guardar' : 'Crear categoria' }}</ThemeButton>
                        <ThemeButton v-if="editingId" type="button" variant="ghost" @click="reset"><X class="h-4 w-4" /> Cancelar</ThemeButton>
                    </div>
                </form>
            </ThemeCard>

            <div class="lg:col-span-2">
                <div class="mb-4 flex justify-end"><ThemeButton href="/admin/news" variant="ghost">Volver a noticias</ThemeButton></div>
                <ThemeTable>
                    <thead><tr><th>Nombre</th><th>Noticias</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="category in categories" :key="category.id">
                            <td><p class="font-medium text-theme-on-surface">{{ category.name }}</p><p class="font-mono-data text-xs text-theme-on-surface-muted">{{ category.slug }}</p></td>
                            <td class="text-theme-on-surface-muted">{{ category.articles_count }}</td>
                            <td><div class="flex justify-end gap-1"><ThemeIconButton title="Editar categoria" @click="edit(category)"><Pencil class="h-4 w-4" /></ThemeIconButton><ThemeIconButton title="Eliminar categoria" variant="danger" @click="destroyCategory(category)"><Trash2 class="h-4 w-4" /></ThemeIconButton></div></td>
                        </tr>
                        <tr v-if="categories.length === 0"><td colspan="3" class="py-8 text-center text-theme-on-surface-muted">Todavia no hay categorias.</td></tr>
                    </tbody>
                </ThemeTable>
            </div>
        </div>
    </ThemeAppLayout>
</template>
