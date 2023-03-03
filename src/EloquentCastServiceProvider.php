<?php

namespace JnJairo\Laravel\EloquentCast;

use Illuminate\Support\ServiceProvider;

class EloquentCastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/eloquent-cast.php' => config_path('eloquent-cast.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/eloquent-cast.php', 'eloquent-cast');
    }
}
