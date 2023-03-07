<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Koffin\Core\Support\Str;

if (! function_exists('f')) {
    /**
     * @param  string  $text
     * @return string
     */
    function f(string $text = ''): string
    {
        return stripslashes(nl2br($text));
    }
}

if (! function_exists('prettySize')) {
    /**
     * Human readable file size.
     *
     * @param  int  $bytes
     * @param  int  $decimals
     *
     * @return string
     */
    function prettySize(int $bytes, int $decimals = 2): string
    {
        $sz = 'BKMGTPE';
        $factor = (int) floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).$sz[$factor];
    }
}

if (! function_exists('setDefaultRequest')) {
    /**
     * Set Default Value for Request Input.
     *
     * @param string|array $name
     * @param null         $value
     * @param bool         $force
     *
     * @return void
     */
    function setDefaultRequest(string|array $name, mixed $value = null, bool $force = true): void
    {
        try {
            $request = app('request');

            if (is_array($name)) {
                $data = $name;
            } else {
                $data = [$name => $value];
            }

            if ($force) {
                $request->merge($data);
            } else {
                $request->mergeIfMissing($data);
            }
            $request->session()->flashInput($data);
        } catch (Exception $e) {
            // throw $e;
        }
    }
}

if (! function_exists('fromResource')) {
    /**
     * Generate an collection from resource.
     *
     * @param  \Illuminate\Http\Resources\Json\JsonResource  $resource
     *
     * @return mixed
     */
    function fromResource(\Illuminate\Http\Resources\Json\JsonResource $resource): mixed
    {
        return json_decode(json_encode($resource));
    }
}

if (! function_exists('vendor')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     *
     * @return string
     */
    function vendor(string $path): string
    {
        $vendorPath = config('koffinate.core.url.vendor');
        $vendorPath = $vendorPath !== '' ? $vendorPath : asset('vendor');

        if (preg_match('/(:\/\/)+/i', $path, $matches, PREG_UNMATCHED_AS_NULL, 1)) {
            $replacedCount = 0;
            $pattern = '/^(vendor:\/\/)/i';
            $path = preg_replace($pattern, '', $path, -1, $replacedCount);
            if ($replacedCount > 0) {
                $vendorPath .= '/assets';
            }

            $replacedCount = 0;
            $pattern = '/^(asset:\/\/)/i';
            $path = preg_replace($pattern, '', $path, -1, $replacedCount);
            if ($replacedCount > 0) {
                $vendorPath = asset('');
            }
        }

        if (isDev() && preg_match('/(app)((\.min)?\.css)$/i', $path)) {
            $path = preg_replace('/(app)((\.min)?\.css)$/i', '$1-dev$2', $path);
        }

        return $vendorPath.'/'.$path;
    }
}

if (! function_exists('document')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     *
     * @return string
     */
    function document(string $path): string
    {
        return config('koffinate.core.url.document', asset('files'))."/{$path}";
    }
}

if (! function_exists('plugins')) {
    /**
     * Retrive Application Plugins.
     * retriving from config's definitions.
     *
     * @param string|array|null $name
     * @param string            $base
     * @param string|array      $type
     *
     * @return void
     */
    function plugins(string|array|null $name = null, string $base = 'local', string|array $type = ['css', 'js']): void
    {
        if (! $name) {
            return;
        }
        if (! in_array($base, ['vendor', 'local'])) {
            return;
        }

        $name = (array) $name;
        $type = (array) $type;

        $rs = [];

        foreach ($name as $pkgKey => $pkgVal) {
            if (is_array($pkgVal)) {
                $rs = array_merge_recursive($rs, pluginAssets($pkgKey, $base, $type));

                foreach ($pkgVal as $pkey => $pval) {
                    $rs = array_merge_recursive($rs, pluginAssets($pval, $base, $type, $pkgKey.'.'.$pkey.'.'));
                }
            } else {
                $rs = array_merge_recursive($rs, pluginAssets($pkgVal, $base, $type));
            }
        }

        if (is_array($rs['css'])) {
            $css = implode('', $rs['css']);
        }
        if (is_array($rs['js'])) {
            $js = implode('', $rs['js']);
        }

        View::share(['pluginCss' => $css ?? '', 'pluginJs' => $js ?? '']);
    }
}

if (! function_exists('pluginAssets')) {
    /**
     * Retrive Application Plugins's Assets.
     * retriving from config's definitions.
     *
     * @param string $names
     * @param string $base
     * @param array  $type
     * @param string $parent
     *
     * @return array
     */
    function pluginAssets(string $names, string $base = 'local', array $type = ['css', 'js'], string $parent = ''): array
    {
        $names = (array) $names;

        $localPath = preg_replace('/\/+$/', '', config('koffinate.core.plugins.public_path', 'plugins')).'/';
        $package = config('koffinate.core.plugins.config_path', 'koffinate.plugins').".{$parent}";
        $httpPattern = '/^(http[s?]:)/i';

        $rs = [];
        foreach ($names as $name) {
            foreach ($type as $t) {
                $rs[$t] = '';
                if (config()->has("{$package}{$name}.{$t}")) {
                    $legacyCondition = null;
                    if ($t === 'legacy') {
                        $legacyCondition = config("{$package}{$name}.legacy")['condition'];
                        $rs[$t] .= $legacyCondition[0];
                    }

                    foreach (config("{$package}{$name}.{$t}") as $file) {
                        if (preg_match($httpPattern, $file)) {
                            $src = $file;
                        } else {
                            $src = match ($base) {
                                'vendor' => vendor($file),
                                'local' => asset($localPath.$file),
                                default => null,
                            };
                        }

                        if ($src) {
                            if ($t === 'css') {
                                $rs[$t] .= "<link href='{$src}' rel='stylesheet'>";
                            }
                            if ($t === 'js') {
                                $rs[$t] .= "<script src='{$src}'></script>";
                            }
                        }

                        unset($src);
                    }

                    if ($legacyCondition) {
                        $rs[$t] .= $legacyCondition[1];
                    }
                }
            }
        }

        return $rs;
    }
}

if (! function_exists('trimAll')) {
    /**
     * @param null|string $string
     * @param string $type
     * @param string $pattern
     *
     * @return string
     * @throws Exception
     */
    function trimAll(?string $string, string $type = 'smart', string $pattern = '\W+'): string
    {
        if (! $string || trim($string) == '') {
            return '';
        }
        if (! in_array($type, ['smart', 'both', 'left', 'right', 'all'])) {
            throw new Exception('type of trim not valid, use smart|left|right|all instead.', 401);
        }

        try {
            return match ($type) {
                'both' => preg_replace('/^'.$pattern.'|'.$pattern.'$/i', '', $string),
                'left' => preg_replace('/^'.$pattern.'/i', '', $string),
                'right' => preg_replace('/'.$pattern.'$/i', '', $string),
                'all' => preg_replace('/'.$pattern.'/i', '', $string),
                default => preg_replace('/'.$pattern.'/i', ' ', preg_replace('/^'.$pattern.'|'.$pattern.'$/i', '', $string)),
            };
        } catch (\Exception $e) {
        }

        return '';
    }
}

if (! function_exists('carbon')) {
    /**
     * @param string|\DateTimeInterface|null $datetime
     * @param \DateTimeZone|string|null      $timezone
     * @param string|null                    $locale
     *
     * @return Carbon
     */
    function carbon(string|DateTimeInterface|null $datetime = null, string|DateTimeZone|null $timezone = null, ?string $locale = null): Carbon
    {
        if (auth()->check()) {
            if (! $timezone && auth()->user()?->timezone) {
                $timezone = auth()->user()->timezone;
            }
            if (! $locale && auth()->user()?->locale) {
                $locale = auth()->user()->locale;
            }
        }
        Carbon::setLocale($locale ?? 'id_ID');
        if (! $datetime) {
            return Carbon::now()->timezone($timezone);
        }

        return Carbon::parse($datetime)->timezone($timezone);
    }
}

if (! function_exists('isDev')) {
    /**
     * Development Mode Checker.
     *
     * @return bool
     */
    function isDev(): bool
    {
        if (\Illuminate\Support\Facades\Session::has('dev_mode')) {
            return \Illuminate\Support\Facades\Session::get('dev_mode', false);
        }

        $dev = env('APP_DEVMODE', 'off');

        return in_array(strtolower($dev), ['true', '1', 'on']);
    }
}

if (! function_exists('hasRoute')) {
    /**
     * Existing Route by Name.
     *
     * @param  string  $name
     *
     * @return bool
     */
    function hasRoute(string $name): bool
    {
        return app('router')->has($name);
    }
}

if (! function_exists('routed')) {
    /**
     * Existing Route by Name
     * with '#' fallback.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @return string
     */
    function routed(string $name, array $parameters = [], bool $absolute = true): string
    {
        if (app('router')->has($name)) {
            return app('url')->route($name, $parameters, $absolute);
        }

        return '#';
    }
}

if (! function_exists('activeRoute')) {
    /**
     * @param string $route
     * @param array  $params
     *
     * @return bool
     */
    function activeRoute(string $route = '', array $params = []): bool
    {
        if (empty($route = trim($route))) {
            return false;
        }

        try {
            if (request()->routeIs($route, "{$route}.*")) {
                if (empty($params)) {
                    return true;
                }

                $requestRoute = request()->route();
                $paramNames = $requestRoute->parameterNames();

                foreach ($params as $key => $value) {
                    if (is_int($key)) {
                        $key = $paramNames[$key];
                    }

                    if (
                        $requestRoute->parameter($key) instanceof \Illuminate\Database\Eloquent\Model
                        && $value instanceof \Illuminate\Database\Eloquent\Model
                        && $requestRoute->parameter($key)->id != $value->id
                    ) {
                        return false;
                    }

                    if (is_object($requestRoute->parameter($key))) {
                        // try to check param is enum type
                        try {
                            if ($requestRoute->parameter($key)->value && $requestRoute->parameter($key)->value != $value) {
                                return false;
                            }
                        } catch (Exception $e) {
                            return false;
                        }
                    } else {
                        if ($requestRoute->parameter($key) != $value) {
                            return false;
                        }
                    }
                }

                return true;
            }
        } catch (Exception $e) {
        }

        return false;
    }
}

if (! function_exists('activeCss')) {
    /**
     * @param string $route
     * @param array  $params
     * @param string $cssClass
     *
     * @return string
     */
    function activeCss(string $route = '', array $params = [], string $cssClass = 'active current'): string
    {
        return activeRoute($route, $params) ? $cssClass : '';
    }
}

if (! function_exists('getRawSql')) {
    /**
     * @param \Illuminate\Database\Query\Builder|\Koffin\Core\Database\Query\Builder $query
     *
     * @return string
     */
    function getRawSql(\Illuminate\Database\Query\Builder|\Koffin\Core\Database\Query\Builder $query): string
    {
        return Str::replaceArray('?', $query->getBindings(), $query->toSql());
    }
}

if (! function_exists('getErrors')) {
    /**
     * Feedback CSS Class.
     *
     * @param string|null $key
     * @param string|null $bag
     *
     * @return ?ViewErrorBag
     */
    function getErrors(?string $key = null, ?string $bag = null): ?ViewErrorBag
    {
        $errors = session('errors');
        if (empty($key) || empty($errors)) {
            return null;
        }
        if ($bag) {
            if (empty($errors->$bag->all())) {
                return null;
            }
            $errors = $errors->$bag;
        }

        return $errors;
    }
}

if (! function_exists('hasError')) {
    /**
     * Feedback CSS Class.
     *
     * @param string|array|null $key
     * @param string|null $bag
     *
     * @return bool
     */
    function hasError(string|array|null $key = null, ?string $bag = null): bool
    {
        if (($errors = getErrors($key, $bag)) instanceof ViewErrorBag === false) {
            return false;
        }

        return $errors->has($key);
    }
}

if (! function_exists('paginateStyleReset')) {
    /**
     * Style reset paginate.
     *
     * @param $datas
     *
     * @return string
     */
    function paginateStyleReset($datas): string
    {
        try {
            if (method_exists($datas, 'perPage') && method_exists($datas, 'currentPage')) {
                return 'counter-reset: _rownum '.($datas->perPage() * ($datas->currentPage() - 1)).';';
            }
        } catch (Exception $e) {
        }

        return '';
    }
}
