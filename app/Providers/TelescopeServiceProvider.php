<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');
        $isDevelopment = $this->app->environment(['local', 'staging']);

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal, $isDevelopment) {
            // In local environment, log everything
            if ($isLocal) {
                return true;
            }

            // In production/staging, only log important events
            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag() ||
                   // Log payment-related requests for debugging
                   $this->isPaymentRelated($entry) ||
                   // Log admin actions
                   $this->isAdminAction($entry) ||
                   // Log slow queries
                   $this->isSlowQuery($entry);
        });

        // Tag specific operations for easier filtering
        Telescope::tag(function (IncomingEntry $entry) {
            $tags = [];

            // Tag payment operations
            if ($this->isPaymentRelated($entry)) {
                $tags[] = 'payment';
            }

            // Tag admin operations
            if ($this->isAdminAction($entry)) {
                $tags[] = 'admin';
            }

            // Tag membership operations
            if ($this->isMembershipRelated($entry)) {
                $tags[] = 'membership';
            }

            return $tags;
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        // Always hide sensitive parameters
        Telescope::hideRequestParameters([
            '_token',
            'password',
            'password_confirmation',
            'current_password',
            // Payment sensitive data
            'card_number',
            'card_cvc',
            'card_expiry',
            'stripe_token',
            'paypal_payment_id',
            'bank_account',
            // User sensitive data
            'ssn',
            'tax_id',
            'date_of_birth',
        ]);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'authorization',
            'stripe-signature',
            'paypal-auth-signature',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user = null) {
            // In local environment, allow all authenticated users
            if ($this->app->environment('local')) {
                return $user !== null;
            }

            // In production/staging, only allow admins and super admins
            return $user && $user->isAdmin();
        });
    }

    /**
     * Check if the entry is payment-related.
     */
    protected function isPaymentRelated(IncomingEntry $entry): bool
    {
        if ($entry->type === 'request') {
            $uri = $entry->content['uri'] ?? '';
            return str_contains($uri, 'payment') || 
                   str_contains($uri, 'stripe') || 
                   str_contains($uri, 'paypal') || 
                   str_contains($uri, 'twint') || 
                   str_contains($uri, 'webhook');
        }

        if ($entry->type === 'job') {
            $job = $entry->content['name'] ?? '';
            return str_contains($job, 'Payment') || str_contains($job, 'Invoice');
        }

        return false;
    }

    /**
     * Check if the entry is admin-related.
     */
    protected function isAdminAction(IncomingEntry $entry): bool
    {
        if ($entry->type === 'request') {
            $uri = $entry->content['uri'] ?? '';
            return str_contains($uri, 'admin/') || str_contains($uri, 'dashboard');
        }

        return false;
    }

    /**
     * Check if the entry is membership-related.
     */
    protected function isMembershipRelated(IncomingEntry $entry): bool
    {
        if ($entry->type === 'request') {
            $uri = $entry->content['uri'] ?? '';
            return str_contains($uri, 'membership') || str_contains($uri, 'renewal');
        }

        if ($entry->type === 'job') {
            $job = $entry->content['name'] ?? '';
            return str_contains($job, 'Membership') || str_contains($job, 'Renewal');
        }

        return false;
    }

    /**
     * Check if the entry is a slow query.
     */
    protected function isSlowQuery(IncomingEntry $entry): bool
    {
        if ($entry->type === 'query') {
            $time = $entry->content['time'] ?? 0;
            return $time > 200; // Log queries slower than 200ms
        }

        return false;
    }
}
