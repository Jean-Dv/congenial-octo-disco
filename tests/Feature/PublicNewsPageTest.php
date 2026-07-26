<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\Concerns\BootsNewsModule;
use Tests\TestCase;

final class PublicNewsPageTest extends TestCase
{
    use BootsNewsModule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootNewsModule();
        Storage::fake('public');
    }

    public function test_it_renders_published_news_in_home_archive_and_detail(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/news/categories', ['name' => 'Eventos', 'slug' => 'eventos'])->assertRedirect();
        $categoryId = (int) \DB::table('news_categories')->value('id');

        $this->actingAs($admin)->post('/admin/news', $this->articleData($categoryId, 'published', true))->assertRedirect('/admin/news');
        $this->post('/logout');

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home/Index', shouldExist: false)
            ->has('latestNews', 1)
            ->where('latestNews.0.slug', 'apertura-del-reino'));

        $this->get('/news')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/News/Index', shouldExist: false)
            ->where('enabledModules.core', true)
            ->where('enabledModules.public', true)
            ->where('enabledModules.news', true)
            ->where('featuredArticle.slug', 'apertura-del-reino')
            ->has('categories', 1));

        $this->get('/news/apertura-del-reino')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/News/Show', shouldExist: false)
            ->where('article.author', 'MoonAdmin')
            ->where('article.bodyHtml', fn (string $html) => str_contains($html, '<h1>Bienvenidos</h1>') && ! str_contains($html, '<script>')));
    }

    public function test_drafts_are_not_public_and_category_filter_is_applied(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/news/categories', ['name' => 'Parches', 'slug' => 'parches']);
        $categoryId = (int) \DB::table('news_categories')->value('id');
        $this->actingAs($admin)->post('/admin/news', $this->articleData($categoryId, 'draft', false));
        $this->post('/logout');

        $this->get('/news')->assertInertia(fn (Assert $page) => $page
            ->where('featuredArticle', null)
            ->has('articles.data', 0));
        $this->get('/news/apertura-del-reino')->assertNotFound();
        $this->get('/news?category=desconocida')->assertInertia(fn (Assert $page) => $page->has('articles.data', 0));
    }

    private function articleData(int $categoryId, string $status, bool $featured): array
    {
        return [
            'title' => 'Apertura del reino',
            'slug' => 'apertura-del-reino',
            'excerpt' => 'Todo lo que necesitas saber antes de entrar al nuevo reino.',
            'body_markdown' => "# Bienvenidos\n\n<script>alert('x')</script>Contenido seguro.",
            'category_id' => $categoryId,
            'status' => $status,
            'is_featured' => $featured,
            'cover' => UploadedFile::fake()->image('portada.jpg', 1200, 675),
        ];
    }

    private function admin(): UserModel
    {
        return UserModel::create([
            'name' => 'MoonAdmin', 'email' => 'admin@moonshard.local',
            'password' => Hash::make('MoonTest!2026'), 'locale' => 'es', 'is_admin' => true,
        ]);
    }
}
