<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
    }
} 