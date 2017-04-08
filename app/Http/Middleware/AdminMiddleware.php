<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ip() == '127.0.0.1') {
            return $next($request);
        }

        if (\Auth::check() && $user = \Auth::getUser()) {
            if ($user->id === 6049413) {
                return $next($request);
            }
        }

        return redirect('/');
    }
}
