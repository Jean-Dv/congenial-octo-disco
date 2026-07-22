<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ModuleSynchronizationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_synchronizes_discovered_modules(): void
    {
        $this->artisan('moon:sync-modules')->assertSuccessful();

        $this->assertDatabaseHas('modules', [
            'slug' => 'core',
            'enabled' => true,
        ]);
        $this->assertDatabaseHas('modules', [
            'slug' => 'public',
            'enabled' => true,
        ]);
        $this->assertDatabaseHas('modules', [
            'slug' => 'news',
            'enabled' => true,
        ]);
    }
}
