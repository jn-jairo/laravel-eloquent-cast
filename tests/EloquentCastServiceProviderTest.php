<?php

use JnJairo\Laravel\EloquentCast\EloquentCastServiceProvider;

it('was published', function () {
    expect(EloquentCastServiceProvider::$publishes)
        ->toHaveKey(EloquentCastServiceProvider::class);

    expect(EloquentCastServiceProvider::$publishes[EloquentCastServiceProvider::class])
        ->toContain(config_path('eloquent-cast.php'));

    expect(EloquentCastServiceProvider::$publishGroups)
        ->toHaveKey('config');

    expect(EloquentCastServiceProvider::$publishGroups['config'])
        ->toContain(config_path('eloquent-cast.php'));
});

it('has registered the configuration file', function () {
    expect(config('eloquent-cast'))
        ->toBe(require realpath(__DIR__ . '/../config/eloquent-cast.php'));
});
