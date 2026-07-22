<script setup>
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    currentCoverUrl: { type: String, default: '' },
    submitLabel: { type: String, default: 'Guardar' },
});

const emit = defineEmits(['submit']);
const slugTouched = ref(!!props.form.slug);
const previewHtml = ref('');
const previewLoading = ref(false);

function slugify(value) {
    return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
        .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
}

watch(() => props.form.title, (title) => {
    if (!slugTouched.value) props.form.slug = slugify(title);
});

function chooseCover(event) {
    props.form.cover = event.target.files[0] ?? null;
}

async function preview() {
    previewLoading.value = true;
    try {
        const response = await axios.post('/admin/news/preview', { body_markdown: props.form.body_markdown });
        previewHtml.value = response.data.html;
    } finally {
        previewLoading.value = false;
    }
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <ThemeCard title="Contenido" tone="important">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <ThemeField label="Titulo" :error="form.errors.title">
                    <ThemeInput v-model="form.title" placeholder="Actualizacion del servidor" />
                </ThemeField>
                <ThemeField label="Slug" :error="form.errors.slug">
                    <ThemeInput v-model="form.slug" placeholder="actualizacion-del-servidor" @input="slugTouched = true" />
                </ThemeField>
                <ThemeField label="Categoria" :error="form.errors.category_id">
                    <ThemeSelect v-model="form.category_id">
                        <option value="" disabled>Selecciona una categoria</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </ThemeSelect>
                </ThemeField>
                <ThemeField label="Estado" :error="form.errors.status">
                    <ThemeSelect v-model="form.status">
                        <option value="draft">Borrador</option>
                        <option value="published">Publicada</option>
                    </ThemeSelect>
                </ThemeField>
            </div>

            <ThemeField class="mt-5" label="Resumen" :error="form.errors.excerpt">
                <textarea v-model="form.excerpt" maxlength="500" rows="3" class="news-textarea" placeholder="Resumen que aparecera en las tarjetas publicas" />
                <p class="mt-1 text-right text-xs text-theme-on-surface-disabled">{{ form.excerpt.length }}/500</p>
            </ThemeField>

            <ThemeField class="mt-5" label="Contenido Markdown" :error="form.errors.body_markdown">
                <textarea v-model="form.body_markdown" rows="16" class="news-textarea font-mono-data" placeholder="# Encabezado&#10;&#10;Contenido de la noticia..." />
            </ThemeField>

            <div class="mt-3 flex justify-end">
                <ThemeButton type="button" variant="secondary" size="sm" :disabled="previewLoading" @click="preview">
                    {{ previewLoading ? 'Generando...' : 'Actualizar vista previa' }}
                </ThemeButton>
            </div>

            <div v-if="previewHtml" class="news-markdown-preview mt-4 rounded border border-theme-border bg-theme-surface-container-lowest p-5" v-html="previewHtml" />
        </ThemeCard>

        <ThemeCard title="Portada y visibilidad">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <ThemeField label="Imagen de portada" :error="form.errors.cover">
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded border border-theme-border bg-theme-surface-container-low p-2.5 text-sm text-theme-on-surface-muted file:mr-4 file:rounded file:border-0 file:bg-theme-primary-container file:px-3 file:py-2 file:font-semibold file:text-theme-on-primary-container" @change="chooseCover">
                    </ThemeField>
                    <p class="mt-2 text-xs text-theme-on-surface-disabled">JPG, PNG o WebP. Maximo 5 MiB.</p>
                </div>
                <img v-if="currentCoverUrl" :src="currentCoverUrl" alt="Portada actual" class="aspect-video w-full rounded border border-theme-border object-cover">
            </div>

            <ThemeCheckbox v-model="form.is_featured" class="mt-5" :disabled="form.status !== 'published'">
                Mostrar como noticia destacada (reemplazara a la destacada actual)
            </ThemeCheckbox>
        </ThemeCard>

        <div class="flex gap-3">
            <ThemeButton type="submit" :disabled="form.processing || categories.length === 0">{{ submitLabel }}</ThemeButton>
            <ThemeButton href="/admin/news" variant="ghost">Cancelar</ThemeButton>
        </div>
    </form>
</template>

<style scoped>
.news-textarea {
    width: 100%;
    border: 1px solid var(--color-theme-border);
    border-radius: .25rem;
    background: var(--color-theme-surface-container-low);
    padding: .75rem .875rem;
    color: var(--color-theme-on-surface);
    outline: none;
}
.news-textarea:focus { border-color: var(--color-theme-primary-container); }
.news-markdown-preview :deep(h1), .news-markdown-preview :deep(h2), .news-markdown-preview :deep(h3) { margin: 1rem 0 .5rem; font-weight: 700; color: var(--color-theme-on-surface); }
.news-markdown-preview :deep(p), .news-markdown-preview :deep(ul), .news-markdown-preview :deep(ol) { margin: .75rem 0; color: var(--color-theme-on-surface-muted); }
.news-markdown-preview :deep(a) { color: var(--color-theme-primary); text-decoration: underline; }
</style>
