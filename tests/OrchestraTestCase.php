<?php

namespace JnJairo\Laravel\EloquentCast\Tests;

use JnJairo\Laravel\Cast\CastServiceProvider;
use JnJairo\Laravel\EloquentCast\EloquentCastServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class OrchestraTestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CastServiceProvider::class,
            EloquentCastServiceProvider::class,
        ];
    }
}
