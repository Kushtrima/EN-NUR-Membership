<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\MembershipRenewal;
use Carbon\Carbon;

class CheckMembershipRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:check-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update membership renewals, create renewal records for new memberships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting membership renewal check...');

        // Step 1: Create renewal records for completed membership payments that don't have one
        $this->createMissingRenewalRecords();

        // Step 2: Update days until expiry for all active renewals
        $this->updateExpiryDays();

        // Step 3: Check for renewed memberships and mark them as renewed
        $this->checkForRenewedMemberships();

        $this->info('Membership renewal check completed successfully.');
    }

    /**
     * Create renewal records for membership payments that don't have one.
     */
    private function createMissingRenewalRecords()
    {
        $this->info('Creating missing renewal records...');

        // Get all completed membership payments that don't have a renewal record
        $membershipPayments = Payment::where('payment_type', 'membership')
            ->where('status', Payment::STATUS_COMPLETED)
            ->whereDoesntHave('membershipRenewal')
            ->with('user')
            ->get();

        $createdCount = 0;

        foreach ($membershipPayments as $payment) {
            // Calculate membership dates (1 year from payment date)
            $startDate = $payment->created_at->toDateString();
            $endDate = $payment->created_at->addYear()->toDateString();
            $daysUntilExpiry = max(0, Carbon::now()->diffInDays($endDate, false));

            MembershipRenewal::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'membership_start_date' => $startDate,
                'membership_end_date' => $endDate,
                'days_until_expiry' => $daysUntilExpiry,
                'is_expired' => $daysUntilExpiry <= 0,
            ]);

            $createdCount++;
        }

        $this->info("Created {$createdCount} new renewal records.");
    }

    /**
     * Update days until expiry for all active renewal records.
     */
    private function updateExpiryDays()
    {
        $this->info('Updating expiry days for active renewals...');

        $activeRenewals = MembershipRenewal::where('is_renewed', false)->get();
        $updatedCount = 0;

        foreach ($activeRenewals as $renewal) {
            $oldDays = $renewal->days_until_expiry;
            $renewal->updateDaysUntilExpiry();
            
            if ($oldDays !== $renewal->days_until_expiry) {
                $updatedCount++;
            }
        }

        $this->info("Updated {$updatedCount} renewal records.");
    }

    /**
     * Check for renewed memberships and mark them as renewed.
     */
    private function checkForRenewedMemberships()
    {
        $this->info('Checking for renewed memberships...');

        $renewedCount = 0;

        // Get all active renewal records
        $activeRenewals = MembershipRenewal::where('is_renewed', false)
            ->with('user')
            ->get();

        foreach ($activeRenewals as $renewal) {
            // Check if user has made a new membership payment after the original one
            $newMembershipPayment = Payment::where('user_id', $renewal->user_id)
                ->where('payment_type', 'membership')
                ->where('status', Payment::STATUS_COMPLETED)
                ->where('created_at', '>', $renewal->payment->created_at)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($newMembershipPayment) {
                // Mark the old renewal as renewed
                $renewal->update([
                    'is_renewed' => true,
                    'renewal_payment_id' => $newMembershipPayment->id,
                ]);

                // Create a new renewal record for the new membership
                $startDate = $newMembershipPayment->created_at->toDateString();
                $endDate = $newMembershipPayment->created_at->addYear()->toDateString();
                $daysUntilExpiry = max(0, Carbon::now()->diffInDays($endDate, false));

                MembershipRenewal::create([
                    'user_id' => $newMembershipPayment->user_id,
                    'payment_id' => $newMembershipPayment->id,
                    'membership_start_date' => $startDate,
                    'membership_end_date' => $endDate,
                    'days_until_expiry' => $daysUntilExpiry,
                    'is_expired' => $daysUntilExpiry <= 0,
                ]);

                $renewedCount++;
            }
        }

        $this->info("Processed {$renewedCount} renewed memberships.");
    }
}
