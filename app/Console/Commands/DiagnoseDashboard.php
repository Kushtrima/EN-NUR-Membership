<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Payment;
use App\Models\MembershipRenewal;

class DiagnoseDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:diagnose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose current dashboard state - users, admins, payments, renewals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 DASHBOARD DIAGNOSTIC REPORT');
        $this->info('================================');
        $this->info('');

        // Check all users
        $this->checkUsers();
        $this->info('');

        // Check admin accounts
        $this->checkAdmins();
        $this->info('');

        // Check payments
        $this->checkPayments();
        $this->info('');

        // Check membership renewals
        $this->checkMembershipRenewals();
        $this->info('');

        // Check test users specifically
        $this->checkTestUsers();

        return 0;
    }

    private function checkUsers()
    {
        $this->info('👥 ALL USERS:');
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->error('❌ No users found in database!');
            return;
        }

        foreach ($users as $user) {
            $verified = $user->email_verified_at ? '✅' : '❌';
            $this->info("  - ID: {$user->id} | {$user->name} | {$user->email} | Role: {$user->role} | Verified: {$verified}");
        }
        
        $this->info("Total users: {$users->count()}");
    }

    private function checkAdmins()
    {
        $this->info('🔐 ADMIN ACCOUNTS:');
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        
        if ($admins->count() === 0) {
            $this->error('❌ No admin accounts found!');
            return;
        }

        foreach ($admins as $admin) {
            $verified = $admin->email_verified_at ? '✅' : '❌';
            $this->info("  - {$admin->name} | {$admin->email} | Role: {$admin->role} | Verified: {$verified}");
        }
        
        $this->info("Total admins: {$admins->count()}");
    }

    private function checkPayments()
    {
        $this->info('💳 PAYMENTS:');
        $payments = Payment::with('user')->get();
        
        if ($payments->count() === 0) {
            $this->warn('⚠️ No payments found in database');
            return;
        }

        $membershipPayments = $payments->where('payment_type', 'membership');
        $donationPayments = $payments->where('payment_type', 'donation');
        $completedPayments = $payments->where('status', 'completed');

        $this->info("  - Total payments: {$payments->count()}");
        $this->info("  - Membership payments: {$membershipPayments->count()}");
        $this->info("  - Donation payments: {$donationPayments->count()}");
        $this->info("  - Completed payments: {$completedPayments->count()}");

        // Show recent payments
        $recentPayments = $payments->sortByDesc('created_at')->take(5);
        $this->info("  Recent payments:");
        foreach ($recentPayments as $payment) {
            $this->info("    - {$payment->user->name} | {$payment->payment_type} | CHF " . ($payment->amount/100) . " | {$payment->status}");
        }
    }

    private function checkMembershipRenewals()
    {
        $this->info('🔄 MEMBERSHIP RENEWALS:');
        $renewals = MembershipRenewal::with('user')->get();
        
        if ($renewals->count() === 0) {
            $this->warn('⚠️ No membership renewals found in database');
            return;
        }

        $activeRenewals = $renewals->where('is_renewed', false);
        $expiredRenewals = $renewals->where('is_expired', true);
        $hiddenRenewals = $renewals->where('is_hidden', true);

        $this->info("  - Total renewals: {$renewals->count()}");
        $this->info("  - Active renewals: {$activeRenewals->count()}");
        $this->info("  - Expired renewals: {$expiredRenewals->count()}");
        $this->info("  - Hidden renewals: {$hiddenRenewals->count()}");

        // Show renewals requiring attention
        $needingAttention = $renewals->filter(function ($renewal) {
            $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
            return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30 && !$renewal->is_hidden && !$renewal->is_renewed;
        });

        $this->info("  - Renewals needing attention (dashboard): {$needingAttention->count()}");
        
        if ($needingAttention->count() > 0) {
            $this->info("  Renewals in dashboard:");
            foreach ($needingAttention as $renewal) {
                $days = $renewal->calculateDaysUntilExpiry();
                $status = $days <= 0 ? 'EXPIRED' : "{$days} days left";
                $this->info("    - {$renewal->user->name} | {$status} | End: {$renewal->membership_end_date}");
            }
        }
    }

    private function checkTestUsers()
    {
        $this->info('🧪 TEST USERS:');
        $testEmails = [
            'expired17@test.com',
            'expired20@test.com',
            'justexpired@test.com'
        ];

        $foundTestUsers = 0;
        foreach ($testEmails as $email) {
            $user = User::where('email', $email)->with(['payments', 'membershipRenewals'])->first();
            if ($user) {
                $foundTestUsers++;
                $renewals = $user->membershipRenewals->count();
                $payments = $user->payments->count();
                $this->info("  ✅ {$user->name} | Payments: {$payments} | Renewals: {$renewals}");
                
                if ($renewals > 0) {
                    $renewal = $user->membershipRenewals->first();
                    $days = $renewal->calculateDaysUntilExpiry();
                    $this->info("    - Days until expiry: {$days} | Expired: " . ($renewal->is_expired ? 'Yes' : 'No'));
                }
            } else {
                $this->info("  ❌ {$email} - Not found");
            }
        }

        if ($foundTestUsers === 0) {
            $this->warn('⚠️ No test users found. Run: php artisan test:create-expired-users');
        } else {
            $this->info("Found {$foundTestUsers}/3 test users");
        }
    }
} 