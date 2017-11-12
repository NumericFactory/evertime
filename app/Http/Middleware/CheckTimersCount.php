<?php

namespace App\Http\Middleware;

use Closure;

class CheckTimersCount
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
        
        if(\Auth::check()){
            $user = \Auth::user();
            $timers_count = $user->timers()->count();
            
            if($timers_count >= $user->max_timers_count){
                return redirect()->route('timers.index')->with('error', 'Sorry you’ve reached your max Timers.');
            }
        }
        return $next($request);
    }
}
