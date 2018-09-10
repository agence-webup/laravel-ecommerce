<?php

namespace Webup\Ecommerce\Cart\Middleware;

use Closure;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;

class ShareCartFromSession
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
     * @return void
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
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->session()->has('cart')) {
            $request->session()->put('cart', new \Webup\Ecommerce\Cart\Entities\Cart((string) Str::uuid()));
        }

        $this->view->share('cart', $request->session()->get('cart'));

        return $next($request);
    }
}
