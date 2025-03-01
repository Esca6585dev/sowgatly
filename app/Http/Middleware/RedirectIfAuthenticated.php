<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            switch ($guard) {
                case 'admin':
                    # if guard admin redirect Admin Home
                    if (Auth::guard($guard)->check()) {
                        return redirect(RouteServiceProvider::ADMIN_HOME);
                    }
                    break;

                default:
                    # if guard user redirect Home
                    if (Auth::guard($guard)->check()) {
                        return $next($request);
                    }
                    break;
            }

        }
        return $next($request);
    }
}
