<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import NewsForm from './Partials/NewsForm.vue';

defineProps({ categories: { type: Array, default: () => [] } });

const form = useForm({ title: '', slug: '', excerpt: '', body_markdown: '', category_id: '', status: 'draft', is_featured: false, cover: null });
function submit() { form.post('/admin/news', { forceFormData: true }); }
</script>

<template>
    <Head title="Nueva noticia" />
    <ThemeAppLayout title="Nueva noticia">
        <ThemeAlert v-if="categories.length === 0" tone="warning" class="mb-6">
            Debes <a href="/admin/news/categories" class="theme-link">crear una categoria</a> antes de registrar noticias.
        </ThemeAlert>
        <NewsForm :form="form" :categories="categories" submit-label="Crear noticia" @submit="submit" />
    </ThemeAppLayout>
</template>
