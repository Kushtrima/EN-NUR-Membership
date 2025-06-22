<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
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
        $schedule->command('log:clear')
                 ->weekly()
                 ->mondays()
                 ->at('04:00')
                 ->name('log-cleanup');

        // Check membership renewals daily
        $schedule->command('membership:check-renewals')
                 ->daily()
                 ->at('06:00')
                 ->name('membership-renewal-check')
                 ->emailOutputOnFailure(config('mail.from.address'))
                 ->appendOutputTo(storage_path('logs/membership-renewals.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 