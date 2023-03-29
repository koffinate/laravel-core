<?php

namespace Koffin\Core\Http\Middleware;

use Closure;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Koffin\Core\Foundation\Auth\User;

/**
 * Share active user for all views.
 *
 * @author      Yusron Arif <yusron.arif4@gmail.com>
 */
class ShareActiveUser
{
    /**
     * The view factory implementation.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param  \Illuminate\Contracts\View\Factory  $view
     */
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->view->share(
            'activeUser', (auth()->user() != null) ? auth()->user() : new User()
        );

        return $next($request);
    }
}
