<?php

namespace Koffin\Core\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('plugins', function ($arguments) {
            /* dd([$arguments, preg_replace('/^(")|(")$/s', '', $arguments), (array)$arguments]);
            return false;
            $args = $this->getArguments($arguments);
            $args[1] = $args[1] ?? "'vendor'";
            $args[2] = $args[2] ?? "['css', 'js']"; */

            return "<?php plugins( {$arguments} ); ?>";
        });
    }

    /**
     * Get argument array from argument string.
     *
     * @param  string  $argumentString
     * @return array
     */
    private function getArguments($argumentString, $limit = PHP_INT_MAX)
    {
        return explode(', ', str_replace(['(', ')'], '', $argumentString), $limit);
    }
}
