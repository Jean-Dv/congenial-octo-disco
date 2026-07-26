<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Core\Domain\Realm\Ports\RealmConnectivityVerifierInterface;
use Modules\Core\Infrastructure\Persistence\Connection\NullRealmConnectivityVerifier;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(
            RealmConnectivityVerifierInterface::class,
            new NullRealmConnectivityVerifier,
        );
    }

    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
