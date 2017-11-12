<?php

namespace App\Http\Middleware;

use Closure;

class CheckRegisterRoute
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
        $route =  \Route::currentRouteAction();
        // If Register route then redirect to login
        if($route == 'App\Http\Controllers\Auth\AuthController@showRegistrationForm'){
            return redirect('login');
        }
        return $next($request);
    }
}
