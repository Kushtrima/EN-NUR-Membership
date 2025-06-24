<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Payment;
use App\Models\MembershipRenewal;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CreateExpiredTestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-expired-users {--clean : Clean existing test users first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create three test users with expired membership dates for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Clean up existing test users first
        $this->cleanupExistingTestUsers();

        $this->info('Creating three test users with expired memberships...');

        // Test users data
        $testUsers = [
            [
                'name' => 'Test User 1 - Expired 17 days ago',
                'email' => 'expired17@test.com',
                'expired_days_ago' => 17
            ],
            [
                'name' => 'Test User 2 - Expired 20 days ago',
                'email' => 'expired20@test.com',
                'expired_days_ago' => 20
            ],
            [
                'name' => 'Test User 3 - Just Expired',
                'email' => 'justexpired@test.com',
                'expired_days_ago' => 1
            ]
        ];

        foreach ($testUsers as $userData) {
            $this->info("Creating user: {$userData['name']}");

            // Create user
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('TestPassword123!'),
                'role' => User::ROLE_USER,
                'email_verified_at' => now(),
            ]);

            // Create payment record for the membership
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_type' => 'membership',
                'amount' => 35000, // CHF 350.00 in cents
                'currency' => 'chf',
                'status' => 'completed',
                'transaction_id' => 'test_txn_' . uniqid(),
                'payment_method' => 'stripe',
                'metadata' => json_encode(['test_data' => true]),
                'created_at' => Carbon::now()->subYear(), // Payment was made a year ago
                'updated_at' => Carbon::now()->subYear(),
            ]);

            // Calculate membership dates
            $membershipStartDate = Carbon::now()->subYear(); // Started a year ago
            $membershipEndDate = Carbon::now()->subDays($userData['expired_days_ago']); // Expired X days ago
            $daysUntilExpiry = Carbon::now()->diffInDays($membershipEndDate, false); // Negative for expired

            // Create membership renewal record
            $membershipRenewal = MembershipRenewal::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'membership_start_date' => $membershipStartDate,
                'membership_end_date' => $membershipEndDate,
                'days_until_expiry' => $daysUntilExpiry,
                'notifications_sent' => [], // No notifications sent yet
                'last_notification_sent_at' => null,
                'is_hidden' => false,
                'is_expired' => true, // All are expired
                'is_renewed' => false,
                'renewal_payment_id' => null,
                'admin_notes' => 'Test user created for expired membership testing',
            ]);

            $this->info("✓ Created user '{$user->name}' with membership expired {$userData['expired_days_ago']} days ago");
            $this->info("  Email: {$user->email}");
            $this->info("  Password: TestPassword123!");
            $this->info("  Membership expired on: {$membershipEndDate->format('Y-m-d')}");
            $this->info("  Days until expiry: {$daysUntilExpiry}");
            $this->info("");
        }

        $this->info('🎉 All three test users created successfully!');
        $this->info('');
        $this->info('You can now test the expired users functionality in the superadmin dashboard.');
        $this->info('Login credentials for all test users: Password is "TestPassword123!"');
        
        return 0;
    }

    /**
     * Clean up existing test users.
     */
    private function cleanupExistingTestUsers()
    {
        $this->info('Cleaning up existing test users...');

        $testEmails = [
            'expired17@test.com',
            'expired20@test.com',
            'justexpired@test.com'
        ];

        foreach ($testEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $this->info("- Removing existing user: {$user->name} ({$user->email})");
                // Delete related data first
                $user->membershipRenewals()->delete();
                $user->payments()->delete();
                $user->delete();
            }
        }

        $this->info('Cleanup completed.');
        $this->info('');
    }
} 