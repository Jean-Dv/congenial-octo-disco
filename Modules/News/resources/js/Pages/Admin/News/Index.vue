<script setup>
import { Head, router } from '@inertiajs/vue3';
import { FolderTree, Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps({ articles: { type: Array, default: () => [] } });

function destroyArticle(article) {
    if (confirm(`Eliminar la noticia "${article.title}"? La portada tambien sera eliminada.`)) {
        router.delete(`/admin/news/${article.id}`);
    }
}
</script>

<template>
    <Head title="Noticias" />
    <ThemeAppLayout title="Noticias">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-theme-on-surface-muted">Administra las novedades que se muestran en Home y en el archivo publico.</p>
            <div class="flex gap-2">
                <ThemeButton href="/admin/news/categories" variant="secondary"><FolderTree class="h-4 w-4" /> Categorias</ThemeButton>
                <ThemeButton href="/admin/news/create"><Plus class="h-4 w-4" /> Nueva noticia</ThemeButton>
            </div>
        </div>

        <ThemeTable>
            <thead><tr><th>Noticia</th><th>Categoria</th><th>Estado</th><th>Publicada por</th><th></th></tr></thead>
            <tbody>
                <tr v-for="article in articles" :key="article.id">
                    <td>
                        <div class="flex items-center gap-3">
                            <img :src="article.cover_url" alt="" class="h-12 w-20 rounded object-cover">
                            <div><p class="font-medium text-theme-on-surface">{{ article.title }}</p><p class="font-mono-data text-xs text-theme-on-surface-muted">{{ article.slug }}</p></div>
                        </div>
                    </td>
                    <td class="text-theme-on-surface-muted">{{ article.category }}</td>
                    <td>
                        <ThemeBadge :tone="article.status === 'published' ? 'active' : 'muted'">{{ article.status === 'published' ? 'Publicada' : 'Borrador' }}</ThemeBadge>
                        <ThemeBadge v-if="article.is_featured" tone="critical" class="ml-2">Destacada</ThemeBadge>
                    </td>
                    <td class="text-theme-on-surface-muted">{{ article.author || '—' }}</td>
                    <td class="text-right">
                        <div class="flex justify-end gap-1">
                            <ThemeIconButton :href="`/admin/news/${article.id}/edit`" title="Editar noticia"><Pencil class="h-4 w-4" /></ThemeIconButton>
                            <ThemeIconButton title="Eliminar noticia" variant="danger" @click="destroyArticle(article)"><Trash2 class="h-4 w-4" /></ThemeIconButton>
                        </div>
                    </td>
                </tr>
                <tr v-if="articles.length === 0"><td colspan="5" class="py-8 text-center text-theme-on-surface-muted">Todavia no hay noticias.</td></tr>
            </tbody>
        </ThemeTable>
    </ThemeAppLayout>
</template>
