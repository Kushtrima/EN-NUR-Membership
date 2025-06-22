<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MembershipRenewal;
use Carbon\Carbon;

class TestRenewalScenarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:test-scenarios {--reset : Reset all renewals to original dates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test scenarios for membership renewals (for testing purposes only)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('reset')) {
            $this->resetRenewals();
            return;
        }

        $this->info('Creating test renewal scenarios...');

        $renewals = MembershipRenewal::with('user')->get();

        if ($renewals->count() === 0) {
            $this->error('No renewal records found. Run "php artisan membership:check-renewals" first.');
            return;
        }

        // Create different test scenarios
        $scenarios = [
            'expired' => 'Expired (0 days)',
            'critical' => 'Critical (1 day)',
            'urgent' => 'Urgent (5 days)', 
            'warning' => 'Warning (15 days)',
            'normal' => 'Normal (25 days)'
        ];

        $scenarioIndex = 0;
        foreach ($renewals as $renewal) {
            $scenarioKeys = array_keys($scenarios);
            $scenario = $scenarioKeys[$scenarioIndex % count($scenarioKeys)];
            
            switch ($scenario) {
                case 'expired':
                    $endDate = Carbon::now()->subDays(5);
                    $daysUntilExpiry = 0;
                    $isExpired = true;
                    break;
                case 'critical':
                    $endDate = Carbon::now()->addDay();
                    $daysUntilExpiry = 1;
                    $isExpired = false;
                    break;
                case 'urgent':
                    $endDate = Carbon::now()->addDays(5);
                    $daysUntilExpiry = 5;
                    $isExpired = false;
                    break;
                case 'warning':
                    $endDate = Carbon::now()->addDays(15);
                    $daysUntilExpiry = 15;
                    $isExpired = false;
                    break;
                case 'normal':
                    $endDate = Carbon::now()->addDays(25);
                    $daysUntilExpiry = 25;
                    $isExpired = false;
                    break;
            }

            $renewal->update([
                'membership_end_date' => $endDate->toDateString(),
                'days_until_expiry' => $daysUntilExpiry,
                'is_expired' => $isExpired,
                'is_hidden' => false,
                'notifications_sent' => null,
                'last_notification_sent_at' => null,
            ]);

            $this->info("Updated {$renewal->user->name} to {$scenarios[$scenario]} scenario");
            $scenarioIndex++;
        }

        $this->info('Test scenarios created successfully!');
        $this->info('Visit the admin dashboard to see the renewal notifications.');
        $this->info('Use --reset to restore original dates.');
    }

    /**
     * Reset renewals to their original dates.
     */
    private function resetRenewals()
    {
        $this->info('Resetting renewals to original dates...');

        $renewals = MembershipRenewal::with('payment')->get();

        foreach ($renewals as $renewal) {
            // Recalculate based on original payment date
            $startDate = $renewal->payment->created_at->toDateString();
            $endDate = $renewal->payment->created_at->addYear()->toDateString();
            $daysUntilExpiry = max(0, Carbon::now()->diffInDays($endDate, false));

            $renewal->update([
                'membership_start_date' => $startDate,
                'membership_end_date' => $endDate,
                'days_until_expiry' => $daysUntilExpiry,
                'is_expired' => $daysUntilExpiry <= 0,
                'is_hidden' => false,
                'notifications_sent' => null,
                'last_notification_sent_at' => null,
            ]);

            $this->info("Reset {$renewal->user->name} to original dates");
        }

        $this->info('All renewals reset to original dates.');
    }
}
