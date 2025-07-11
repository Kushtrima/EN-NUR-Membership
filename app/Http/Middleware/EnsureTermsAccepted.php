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

        // ALWAYS skip terms check for super admins
        if ($user->isSuperAdmin()) {
            // Auto-accept terms for super admin if not already accepted
            if (!$user->hasAcceptedTerms()) {
                try {
                    $user->update([
                        'terms_accepted_at' => $user->created_at ?? now(),
                        'terms_version' => '1.0',
                        'terms_accepted_ip' => $request->ip(),
                    ]);
                } catch (\Exception $e) {
                    // If update fails, still let super admin through
                }
            }
            return $next($request);
        }

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
        // Skip email verification for users created with usernames (admin-created users)
        if (!$user->hasVerifiedEmail() && !$user->username) {
            return redirect()->route('verification.notice');
        }

        // Auto-accept terms for existing users (created before terms requirement)
        if (!$user->hasAcceptedTerms() && $user->created_at < now()->subDays(1)) {
            try {
                $user->update([
                    'terms_accepted_at' => $user->created_at,
                    'terms_version' => '1.0',
                    'terms_accepted_ip' => $request->ip(),
                ]);
                $user->refresh();
            } catch (\Exception $e) {
                // If update fails, continue to terms page
            }
        }

        // Check if user has accepted terms (after potential auto-acceptance)
        if (!$user->hasAcceptedTerms()) {
            return redirect()->route('terms.show')
                ->with('info', 'Please accept our Terms and Conditions to continue using the platform.');
        }

        return $next($request);
    }
}
