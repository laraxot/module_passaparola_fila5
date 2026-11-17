<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\Passaparola\Providers\PassaparolaServiceProvider;
use Modules\Xot\Tests\XotBaseTestCase;

/**
 * Base test case del modulo Passaparola. Vietato RefreshDatabase / DatabaseMigrations (dati sacri).
 */
abstract class TestCase extends XotBaseTestCase
{
    /** @return array<int, class-string<ServiceProvider>> */
    protected function getPackageProviders(Application $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            PassaparolaServiceProvider::class,
        ];
    }
}
