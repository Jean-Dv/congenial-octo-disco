<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CleanInstallationTest extends TestCase
{
    public function test_moon_install_loads_news_migrations_without_manual_provider_registration(): void
    {
        $this->artisan('moon:install')->assertSuccessful();

        $this->assertTrue(Schema::hasTable('news_categories'));
        $this->assertTrue(Schema::hasTable('news'));
        $this->assertDatabaseHas('modules', ['slug' => 'core', 'enabled' => true]);
        $this->assertDatabaseHas('modules', ['slug' => 'public', 'enabled' => true]);
        $this->assertDatabaseHas('modules', ['slug' => 'news', 'enabled' => true]);

        $this->artisan('moon:install')->assertSuccessful();
    }
}
