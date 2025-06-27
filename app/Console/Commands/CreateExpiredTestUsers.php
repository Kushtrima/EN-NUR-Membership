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
    protected $signature = 'test:create-expired-users {--clean : Clean existing test users first} {--specific-user= : Create a specific test user} {--days= : Days until expiry for the specific test user} {--infinit : Create infinitdizzajn test scenario}';

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
        if ($this->option('clean')) {
            $this->cleanupExistingTestUsers();
        }

        if ($this->option('infinit')) {
            return $this->createInfinitTest();
        }

        if ($this->option('specific-user')) {
            return $this->createSpecificTestUser();
        }

        $this->createExpiredTestUsers();
        $this->displayResults();

        return Command::SUCCESS;
    }

    /**
     * Create a specific test user scenario.
     */
    private function createSpecificTestUser()
    {
        $email = $this->option('specific-user');
        $days = (int) $this->option('days', 15);
        
        $this->info("🎯 Setting up specific test user: {$email}");
        $this->info("Days until expiry: {$days}");
        
        // Find the user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ User {$email} not found!");
            return Command::FAILURE;
        }
        
        $this->info("✅ Found user: {$user->name} (ID: {$user->id})");
        
        // Clean up existing records for this user
        MembershipRenewal::where('user_id', $user->id)->delete();
        Payment::where('user_id', $user->id)->delete();
        $this->info("🗑️ Cleaned up existing records");
        
        // Calculate dates
        if ($days > 0) {
            $expiryDate = now()->addDays($days)->startOfDay();
            $status = 'expiring';
        } else {
            $expiryDate = now()->subDays(abs($days))->startOfDay();
            $status = 'expired';
        }
        
        $startDate = $expiryDate->copy()->subYear();
        
        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 35000, // CHF 350.00 in cents
            'currency' => 'CHF',
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'stripe_payment_intent_id' => 'pi_test_' . $status . '_' . $user->id,
            'metadata' => [
                'membership_start' => $startDate->toDateString(),
                'membership_end' => $expiryDate->toDateString(),
                'test_scenario' => $days . '_days_to_expiry'
            ],
            'created_at' => $startDate,
            'updated_at' => $startDate
        ]);
        
        $this->info("💳 Created payment: CHF 350.00 (ID: {$payment->id})");
        
        // Create membership renewal record first
        $renewal = MembershipRenewal::create([
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'membership_start_date' => $startDate,
            'membership_end_date' => $expiryDate,
            'days_until_expiry' => 0, // Will be updated below
            'is_expired' => false,
            'is_hidden' => false,
            'notifications_sent' => [],
            'last_notification_sent_at' => null,
            'created_at' => $startDate,
            'updated_at' => now()
        ]);
        
        // Update days until expiry using the model's method
        $renewal->updateDaysUntilExpiry();
        
        $this->info("📅 Created renewal record (ID: {$renewal->id})");
        $this->info("   Start: {$startDate->format('Y-m-d')}");
        $this->info("   End: {$expiryDate->format('Y-m-d')}");
        $this->info("   Days until expiry: " . (int) $renewal->days_until_expiry);
        
        // Show color that will be displayed
        $daysUntilExpiry = (int) $renewal->days_until_expiry;
        if ($daysUntilExpiry <= 0) {
            $color = '🔴 RED (Expired)';
        } elseif ($daysUntilExpiry <= 30) {
            $color = '🟠 ORANGE (Expiring within 30 days)';
        } else {
            $color = '🟢 GREEN (Active)';
        }
        
        $this->info("");
        $this->info("🎨 Color indicator: {$color}");
        $this->info("🎉 Test setup complete for {$email}!");
        
        return Command::SUCCESS;
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

    private function createExpiredTestUsers()
    {
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
                'password' => Hash::make(env('TEST_USER_PASSWORD', 'change-me')),
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
            $daysUntilExpiry = (int) Carbon::now()->diffInDays($membershipEndDate, false); // Negative for expired (cast to int)

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
    }

    /**
     * Create infinitdizzajn test scenario.
     */
    private function createInfinitTest()
    {
        $email = 'infinitdizzajn@gmail.com';
        $days = 15;
        
        $this->info("🎯 Setting up infinitdizzajn@gmail.com test scenario");
        $this->info("Target: 15 days until expiry (ORANGE indicator)");
        
        // Find the user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ User {$email} not found!");
            $this->info("Creating the user...");
            
            $user = User::create([
                'name' => 'Sara Test User',
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make(env('TEST_USER_PASSWORD', 'change-me')),
                'role' => 'user'
            ]);
            
            $this->info("✅ Created user: {$user->name} (ID: {$user->id})");
        } else {
            $this->info("✅ Found user: {$user->name} (ID: {$user->id})");
        }
        
        // Clean up existing records for this user
        $deletedRenewals = MembershipRenewal::where('user_id', $user->id)->count();
        $deletedPayments = Payment::where('user_id', $user->id)->count();
        
        MembershipRenewal::where('user_id', $user->id)->delete();
        Payment::where('user_id', $user->id)->delete();
        
        $this->info("🗑️ Cleaned up {$deletedRenewals} renewals and {$deletedPayments} payments");
        
        // Calculate dates - membership expires in exactly $days from now
        $expiryDate = now()->addDays($days)->startOfDay();
        $startDate = $expiryDate->copy()->subYear(); // Start exactly 1 year before expiry
        
        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 35000, // CHF 350.00 in cents
            'currency' => 'CHF',
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'stripe_payment_intent_id' => 'pi_test_infinit_' . time(),
            'metadata' => [
                'membership_start' => $startDate->toDateString(),
                'membership_end' => $expiryDate->toDateString(),
                'test_scenario' => '15_days_expiry_infinit',
                'created_via' => 'console_command'
            ],
            'created_at' => $startDate,
            'updated_at' => $startDate
        ]);
        
        $this->info("💳 Created payment: CHF 350.00 (ID: {$payment->id})");
        
        // Create membership renewal record
        $renewal = MembershipRenewal::create([
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'membership_start_date' => $startDate,
            'membership_end_date' => $expiryDate,
            'days_until_expiry' => 0, // Will be updated below
            'is_expired' => false,
            'is_hidden' => false,
            'notifications_sent' => [],
            'last_notification_sent_at' => null,
            'created_at' => $startDate,
            'updated_at' => now()
        ]);
        
        // Update days until expiry using the model's method
        $renewal->updateDaysUntilExpiry();
        $renewal->refresh(); // Refresh to get updated values
        
        $this->info("📅 Created renewal record (ID: {$renewal->id})");
        $this->info("   Start: {$startDate->format('Y-m-d')}");
        $this->info("   End: {$expiryDate->format('Y-m-d')}");
        
        // Calculate and show the result
        $daysUntilExpiry = (int) $renewal->days_until_expiry;
        $this->info("   Days until expiry: {$daysUntilExpiry}");
        
        // Show color that will be displayed
        if ($daysUntilExpiry <= 0) {
            $color = '🔴 RED (Expired)';
        } elseif ($daysUntilExpiry <= 30) {
            $color = '🟠 ORANGE (Expiring within 30 days)';
        } else {
            $color = '🟢 GREEN (Active)';
        }
        
        $this->info("");
        $this->info("🎨 Color indicator: {$color}");
        $this->info("🎉 infinitdizzajn@gmail.com test setup complete!");
        $this->info("");
        $this->info("📧 To test email notifications:");
        $this->info("   Visit: /admin/renewals/{$renewal->id}/notify");
        $this->info("   Or use the dashboard notification buttons");
        
        return Command::SUCCESS;
    }

    private function displayResults()
    {
        // Implementation of displayResults method
    }
} 