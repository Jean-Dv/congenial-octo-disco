<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_is_available(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->get('/up')->assertOk();
    }
}
