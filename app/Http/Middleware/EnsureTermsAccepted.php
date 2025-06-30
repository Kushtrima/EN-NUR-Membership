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
            'debug-terms',
        ];

        foreach ($exemptRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }
        
        // Also exempt specific paths
        $exemptPaths = ['/terms', '/privacy', '/terms/accept', '/debug-terms'];
        if (in_array($request->path(), $exemptPaths)) {
            return $next($request);
        }

        // Check if user has verified email
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Auto-accept terms for existing users (created before terms requirement)
        // This handles existing users who were using the platform before terms were required
        if (!$user->hasAcceptedTerms() && $user->created_at < now()->subDays(1)) {
            $user->update([
                'terms_accepted_at' => $user->created_at, // Use their registration date
                'terms_version' => '1.0',
                'terms_accepted_ip' => $request->ip(),
            ]);
            // Refresh the user model to reflect the changes
            $user->refresh();
        }

        // Check if user has accepted terms (after potential auto-acceptance)
        if (!$user->hasAcceptedTerms()) {
            return redirect()->route('terms.show')
                ->with('info', 'Please accept our Terms and Conditions to continue using the platform.');
        }

        return $next($request);
    }
}
