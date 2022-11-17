<?php

namespace Koffin\Core;

use Illuminate\Support\ServiceProvider;
use Koffin\Core\Providers\BladeServiceProvider;
use Koffin\Core\Providers\DbServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/koffinate/core.php', 'koffinate.core');
        $this->mergeConfigFrom(__DIR__.'/../config/koffinate/plugins.php', 'koffinate.plugins');

        $this->app->register(DbServiceProvider::class, true);
        $this->app->register(BladeServiceProvider::class, true);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/../config/koffinate/core.php' => config_path('koffinate/core.php')], 'config');
        $this->publishes([__DIR__.'/../config/koffinate/plugins.php' => config_path('koffinate/plugins.php')], 'config');
    }
}
