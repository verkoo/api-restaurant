<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfNoAdmin
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
        if (! $request->user()) {
            return redirect('/login');
        }
        if (! $request->user()->hasRole('admin')) {
            return redirect('/tpv');
        }
        
        return $next($request);
    }
}
