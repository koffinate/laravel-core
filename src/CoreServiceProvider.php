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
        $this->mergeConfigFrom(__DIR__.'/../config/yusronarif/core.php', 'yusronarif.core');
        $this->mergeConfigFrom(__DIR__.'/../config/yusronarif/plugins.php', 'yusronarif.plugins');

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
        $this->publishes([__DIR__.'/../config/yusronarif/core.php' => config_path('yusronarif/core.php')], 'config');
        $this->publishes([__DIR__.'/../config/yusronarif/plugins.php' => config_path('yusronarif/plugins.php')], 'config');
    }
}
