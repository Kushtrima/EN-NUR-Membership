<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Force HTTPS in production
        if (app()->environment('production') && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
} 