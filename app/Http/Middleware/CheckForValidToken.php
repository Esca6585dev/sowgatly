<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckForValidToken
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => 'error'
            ], 200);
        }

        return $next($request);
    }
}