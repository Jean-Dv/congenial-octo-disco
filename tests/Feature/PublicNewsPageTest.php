<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class PublicNewsPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('public.news')) {
            Route::middleware('web')->group(base_path('Modules/Public/routes/web.php'));
            Route::getRoutes()->refreshNameLookups();
        }
    }

    public function test_it_renders_the_public_news_archive_placeholder(): void
    {
        $this->get(route('public.news'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/News/Index', shouldExist: false)
                ->missing('articles')
            );
    }

    public function test_it_exposes_the_expected_public_news_url(): void
    {
        $this->assertSame('/news', route('public.news', absolute: false));
    }
}
