<script setup>
import { Head, Link } from '@inertiajs/vue3';
defineProps({ article: { type: Object, required: true } });
</script>

<template>
    <Head :title="article.title" />
    <ThemePublicLayout>
        <main class="min-h-screen pt-18" style="background-color: var(--color-theme-public-bg)">
            <header class="relative min-h-110 overflow-hidden">
                <img :src="article.coverUrl" :alt="article.title" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-[linear-gradient(0deg,var(--color-theme-public-bg),rgba(8,15,24,.35)),linear-gradient(90deg,rgba(8,15,24,.9),rgba(8,15,24,.2))]" />
                <div class="relative z-10 mx-auto flex min-h-110 max-w-5xl items-end px-5 pb-14 sm:px-8">
                    <div class="max-w-3xl space-y-4">
                        <Link href="/news" class="inline-flex items-center gap-1 text-sm font-semibold" style="color: var(--color-theme-public-primary)"><span class="material-symbols-outlined text-lg">arrow_back</span> Volver a noticias</Link>
                        <div class="flex items-center gap-3 font-mono-data text-xs uppercase tracking-wider" style="color:var(--color-theme-public-text-secondary)"><span style="color:var(--color-theme-public-primary)">{{ article.category.name }}</span><time :datetime="article.publishedAt">{{ article.publishedAtLabel }}</time></div>
                        <h1 class="text-4xl font-bold leading-tight text-white sm:text-6xl">{{ article.title }}</h1>
                        <p class="text-sm" style="color: var(--color-theme-public-text-secondary)">Publicado por {{ article.author }}</p>
                    </div>
                </div>
            </header>
            <article class="news-body mx-auto max-w-4xl px-5 py-14 sm:px-8 sm:py-20" v-html="article.bodyHtml" />
        </main>
    </ThemePublicLayout>
</template>

<style scoped>
.news-body { color:var(--color-theme-public-text-secondary); font-size:1.05rem; line-height:1.8; }
.news-body :deep(h1), .news-body :deep(h2), .news-body :deep(h3) { margin:2rem 0 1rem; color:white; font-weight:700; line-height:1.25; }
.news-body :deep(h1) { font-size:2.25rem; }.news-body :deep(h2) { font-size:1.75rem; }.news-body :deep(h3) { font-size:1.35rem; }
.news-body :deep(p), .news-body :deep(ul), .news-body :deep(ol), .news-body :deep(blockquote) { margin:1rem 0; }
.news-body :deep(ul) { list-style:disc; padding-left:1.5rem; }.news-body :deep(ol) { list-style:decimal; padding-left:1.5rem; }
.news-body :deep(a) { color:var(--color-theme-public-primary); text-decoration:underline; }
.news-body :deep(blockquote) { border-left:3px solid var(--color-theme-public-primary); padding-left:1rem; font-style:italic; }
.news-body :deep(code) { border-radius:.2rem; background:var(--color-theme-public-surface-container-high); padding:.15rem .35rem; }
.news-body :deep(pre) { overflow:auto; border:1px solid var(--color-theme-public-outline-variant); border-radius:.4rem; background:var(--color-theme-public-surface-container-lowest); padding:1rem; }
.news-body :deep(pre code) { background:transparent; padding:0; }
</style>
