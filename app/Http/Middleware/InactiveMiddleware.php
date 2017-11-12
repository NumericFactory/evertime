<?php

namespace App\Http\Middleware;

use Closure;

class InactiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = \Auth::user();
        return ($user->max_timers_count < 1) ? redirect('/canceled') : $next($request);
    }
}
