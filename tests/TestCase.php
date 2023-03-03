<?php

namespace JnJairo\Laravel\EloquentCast\Tests;

use JnJairo\Laravel\Cast\CastServiceProvider;
use JnJairo\Laravel\EloquentCast\EloquentCastServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CastServiceProvider::class,
            EloquentCastServiceProvider::class,
        ];
    }
}
