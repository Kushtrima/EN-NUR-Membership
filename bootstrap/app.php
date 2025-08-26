<?php

// Set PHP memory limit as early as possible
ini_set('memory_limit', '512M');

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Force HTTPS in production
        $middleware->web(prepend: [
            \App\Http\Middleware\ForceHttps::class,
        ]);
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'terms.accepted' => \App\Http\Middleware\EnsureTermsAccepted::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        // Database backup schedule
        $schedule->command('db:backup --compress --keep=30')
                 ->daily()
                 ->at('02:00')
                 ->name('daily-database-backup')
                 ->emailOutputOnFailure(config('mail.from.address'))
                 ->appendOutputTo(storage_path('logs/backup.log'));

        // Weekly full backup with cloud upload (if configured)
        $schedule->command('db:backup --compress --cloud --keep=12')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->name('weekly-cloud-backup')
                 ->emailOutputOnFailure(config('mail.from.address'))
                 ->appendOutputTo(storage_path('logs/backup.log'));

        // Database monitoring schedule
        $schedule->command('db:monitor --alert --threshold=500')
                 ->hourly()
                 ->name('database-monitoring')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/monitoring.log'));

        // Daily comprehensive monitoring report
        $schedule->command('db:monitor --alert --threshold=200')
                 ->daily()
                 ->at('08:00')
                 ->name('daily-monitoring-report')
                 ->emailOutputOnFailure(config('mail.from.address'))
                 ->appendOutputTo(storage_path('logs/monitoring.log'));

        // Clean up old log files
        $schedule->command('log:clear --force')
                 ->weekly()
                 ->mondays()
                 ->at('04:00')
                 ->name('log-cleanup');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create(); 