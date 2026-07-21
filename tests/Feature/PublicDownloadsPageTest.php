<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class PublicDownloadsPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('public.downloads')) {
            Route::middleware('web')->group(base_path('Modules/Public/routes/web.php'));
            Route::getRoutes()->refreshNameLookups();
        }
    }

    public function test_it_renders_the_static_public_downloads_page(): void
    {
        $this->get(route('public.downloads'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Downloads/Index', shouldExist: false)
                ->missing('downloads')
                ->missing('requirements')
            );
    }

    public function test_it_exposes_the_expected_public_downloads_url(): void
    {
        $this->assertSame('/downloads', route('public.downloads', absolute: false));
    }
}
