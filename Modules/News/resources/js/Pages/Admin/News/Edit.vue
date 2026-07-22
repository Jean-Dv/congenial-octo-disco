<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import NewsForm from './Partials/NewsForm.vue';

const props = defineProps({ article: { type: Object, required: true }, categories: { type: Array, default: () => [] } });
const form = useForm({
    title: props.article.title, slug: props.article.slug, excerpt: props.article.excerpt,
    body_markdown: props.article.body_markdown, category_id: props.article.category_id,
    status: props.article.status, is_featured: props.article.is_featured, cover: null,
});
function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(`/admin/news/${props.article.id}`, { forceFormData: true });
}
</script>

<template>
    <Head :title="`Editar: ${article.title}`" />
    <ThemeAppLayout :title="`Editar noticia: ${article.title}`">
        <NewsForm :form="form" :categories="categories" :current-cover-url="article.cover_url" submit-label="Guardar cambios" @submit="submit" />
    </ThemeAppLayout>
</template>
