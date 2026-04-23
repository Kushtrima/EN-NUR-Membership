<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\MembershipRenewal;
use App\Models\Payment;
use App\Models\User;
use App\Policies\MembershipRenewalPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * The model-to-policy map.
     *
     * Laravel 11 no longer auto-discovers policies from
     * app/Policies/ by convention — each model must be mapped
     * explicitly. Keep this block in sync when new policies land.
     */
    protected array $policies = [
        User::class              => UserPolicy::class,
        Payment::class           => PaymentPolicy::class,
        MembershipRenewal::class => MembershipRenewalPolicy::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set PHP memory limit as early as possible in the application lifecycle
        if (ini_get('memory_limit') !== '512M') {
            ini_set('memory_limit', '512M');
        }

        // Force HTTPS in production
        if (app()->environment('production')) {
            URL::forceScheme('https');

            // Trust proxies (Render uses proxies)
            request()->server->set('HTTPS', 'on');
        }

        // Register authorization policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
} 