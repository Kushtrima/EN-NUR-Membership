<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for guests
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Skip check for routes that don't require terms acceptance
        $exemptRoutes = [
            'terms.show',
            'terms.accept',
            'terms.full',
            'terms.privacy',
            'logout',
            'verification.*',
            'password.*',
        ];

        foreach ($exemptRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        // Check if user has verified email
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Check if user has accepted terms
        if (!$user->hasAcceptedTerms()) {
            return redirect()->route('terms.show')
                ->with('info', 'Please accept our Terms and Conditions to continue using the platform.');
        }

        return $next($request);
    }
}
