<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\Concerns\BootsNewsModule;
use Tests\TestCase;

final class PublicDownloadsPageTest extends TestCase
{
    use BootsNewsModule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootNewsModule();
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

    public function test_authenticated_users_can_open_public_pages(): void
    {
        $user = UserModel::create([
            'name' => 'MoonPlayer',
            'email' => 'player@moonshard.local',
            'password' => Hash::make('MoonTest!2026'),
            'locale' => 'es',
            'is_admin' => false,
        ]);

        $this->actingAs($user)->get('/')->assertOk();
        $this->actingAs($user)->get('/news')->assertOk();
        $this->actingAs($user)->get('/downloads')->assertOk();
    }
}
