<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\Concerns\BootsNewsModule;
use Tests\TestCase;

final class NewsAdministrationTest extends TestCase
{
    use BootsNewsModule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootNewsModule();
        Storage::fake('public');
    }

    public function test_admin_can_create_publish_update_and_delete_news_with_its_cover(): void
    {
        $admin = $this->user(true);
        $this->actingAs($admin)->post('/admin/news/categories', ['name' => 'Eventos', 'slug' => 'eventos'])->assertSessionHasNoErrors();
        $categoryId = (int) \DB::table('news_categories')->value('id');

        $this->actingAs($admin)->post('/admin/news', $this->payload($categoryId))->assertRedirect('/admin/news');
        $article = \DB::table('news')->first();
        $this->assertSame($admin->id, $article->published_by);
        $this->assertNotNull($article->published_at);
        Storage::disk('public')->assertExists($article->cover_path);

        $newCover = UploadedFile::fake()->image('nueva.jpg', 1200, 675);
        $this->actingAs($admin)->post("/admin/news/{$article->id}", [
            ...$this->payload($categoryId, cover: $newCover), '_method' => 'put', 'title' => 'Evento actualizado',
        ])->assertRedirect('/admin/news');
        Storage::disk('public')->assertMissing($article->cover_path);

        $newPath = \DB::table('news')->where('id', $article->id)->value('cover_path');
        $this->actingAs($admin)->delete("/admin/news/{$article->id}")->assertRedirect('/admin/news');
        Storage::disk('public')->assertMissing($newPath);
        $this->assertDatabaseMissing('news', ['id' => $article->id]);
    }

    public function test_only_one_published_article_can_be_featured(): void
    {
        $admin = $this->user(true);
        $this->actingAs($admin)->post('/admin/news/categories', ['name' => 'General', 'slug' => 'general']);
        $categoryId = (int) \DB::table('news_categories')->value('id');
        $this->actingAs($admin)->post('/admin/news', $this->payload($categoryId));
        $this->actingAs($admin)->post('/admin/news', [
            ...$this->payload($categoryId), 'title' => 'Segunda noticia', 'slug' => 'segunda-noticia',
        ]);

        $this->assertSame(1, \DB::table('news')->where('is_featured', true)->count());
        $this->assertDatabaseHas('news', ['slug' => 'segunda-noticia', 'is_featured' => true]);
    }

    public function test_used_categories_cannot_be_deleted_and_regular_users_are_forbidden(): void
    {
        $admin = $this->user(true);
        $regular = $this->user(false);
        $this->actingAs($admin)->post('/admin/news/categories', ['name' => 'General', 'slug' => 'general']);
        $categoryId = (int) \DB::table('news_categories')->value('id');
        $this->actingAs($admin)->post('/admin/news', $this->payload($categoryId));

        $this->actingAs($admin)->delete("/admin/news/categories/{$categoryId}")->assertSessionHas('error');
        $this->assertDatabaseHas('news_categories', ['id' => $categoryId]);
        $this->actingAs($regular)->get('/admin/news')->assertForbidden();
    }

    public function test_news_routes_return_not_found_when_the_module_is_disabled(): void
    {
        $admin = $this->user(true);
        \DB::table('modules')->where('slug', 'news')->update(['enabled' => false]);

        $this->actingAs($admin)->get('/admin/news')->assertNotFound();
        $this->post('/logout');
        $this->get('/news')->assertNotFound();
    }

    public function test_news_requires_a_valid_cover_and_unique_slug(): void
    {
        $admin = $this->user(true);
        $this->actingAs($admin)->post('/admin/news/categories', ['name' => 'General', 'slug' => 'general']);
        $categoryId = (int) \DB::table('news_categories')->value('id');

        $this->actingAs($admin)->post('/admin/news', [
            ...$this->payload($categoryId),
            'cover' => UploadedFile::fake()->create('documento.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('cover');

        $this->actingAs($admin)->post('/admin/news', $this->payload($categoryId))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/news', $this->payload($categoryId))->assertSessionHasErrors('slug');
    }

    private function payload(int $categoryId, ?UploadedFile $cover = null): array
    {
        return [
            'title' => 'Primera noticia', 'slug' => 'primera-noticia',
            'excerpt' => 'Resumen completo de la primera noticia del servidor.',
            'body_markdown' => '# Contenido', 'category_id' => $categoryId,
            'status' => 'published', 'is_featured' => true,
            'cover' => $cover ?? UploadedFile::fake()->image('portada.jpg', 1200, 675),
        ];
    }

    private function user(bool $admin): UserModel
    {
        return UserModel::create([
            'name' => $admin ? 'MoonAdmin' : 'MoonPlayer',
            'email' => $admin ? 'admin@moonshard.local' : 'player@moonshard.local',
            'password' => Hash::make('MoonTest!2026'), 'locale' => 'es', 'is_admin' => $admin,
        ]);
    }
}
