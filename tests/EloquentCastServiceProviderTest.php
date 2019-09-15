<?php

namespace JnJairo\Laravel\EloquentCast\Tests;

use JnJairo\Laravel\EloquentCast\EloquentCastServiceProvider;
use JnJairo\Laravel\EloquentCast\Tests\OrchestraTestCase as TestCase;

/**
 * @testdox Eloquent cast service provider
 */
class EloquentCastServiceProviderTest extends TestCase
{
    public function test_boot_config() : void
    {
        $this->assertArrayHasKey(
            EloquentCastServiceProvider::class,
            EloquentCastServiceProvider::$publishes,
            'Publish class'
        );
        $this->assertContains(
            config_path('eloquent-cast.php'),
            EloquentCastServiceProvider::$publishes[EloquentCastServiceProvider::class],
            'Publish path'
        );
        $this->assertArrayHasKey('config', EloquentCastServiceProvider::$publishGroups, 'Publish group class');
        $this->assertContains(
            config_path('eloquent-cast.php'),
            EloquentCastServiceProvider::$publishGroups['config'],
            'Publish group path'
        );
    }

    public function test_register_config() : void
    {
        $this->assertSame(
            config('eloquent-cast'),
            require realpath(__DIR__ . '/../config/eloquent-cast.php'),
            'Configuration content'
        );
    }
}
