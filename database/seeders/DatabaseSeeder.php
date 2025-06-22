<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Payment;
use App\Models\MembershipRenewal;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create super admin user (always exists)
        User::firstOrCreate(
            ['email' => 'superadmin@mosque.ch'],
            [
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('superadmin123'),
                'role' => User::ROLE_SUPER_ADMIN,
            ]
        );

        // Create regular admin user
        User::firstOrCreate(
            ['email' => 'admin@mosque.ch'],
            [
                'name' => 'Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // Create a regular user for testing
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
            ]
        );

        // Create 3 test users with different membership statuses
        
        // 1. User with EXPIRED membership (RED)
        $expiredUser = User::create([
            'name' => 'John Expired',
            'email' => 'expired@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Create expired membership payment
        $expiredPayment = Payment::create([
            'user_id' => $expiredUser->id,
            'amount' => 35000, // CHF 350.00
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'transaction_id' => 'test_expired_' . uniqid(),
            'created_at' => now()->subMonths(13), // 13 months ago
        ]);

        // Create expired membership renewal
        MembershipRenewal::create([
            'user_id' => $expiredUser->id,
            'payment_id' => $expiredPayment->id,
            'membership_start_date' => now()->subMonths(13),
            'membership_end_date' => now()->subDays(30), // Expired 30 days ago
            'days_until_expiry' => -30,
            'is_renewed' => false,
            'is_hidden' => false,
        ]);

        // 2. User with EXPIRING membership (ORANGE)
        $expiringUser = User::create([
            'name' => 'Sarah Expiring',
            'email' => 'expiring@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Create expiring membership payment
        $expiringPayment = Payment::create([
            'user_id' => $expiringUser->id,
            'amount' => 35000, // CHF 350.00
            'payment_type' => 'membership',
            'payment_method' => 'paypal',
            'status' => 'completed',
            'transaction_id' => 'test_expiring_' . uniqid(),
            'created_at' => now()->subMonths(11), // 11 months ago
        ]);

        // Create expiring membership renewal
        MembershipRenewal::create([
            'user_id' => $expiringUser->id,
            'payment_id' => $expiringPayment->id,
            'membership_start_date' => now()->subMonths(11),
            'membership_end_date' => now()->addDays(15), // Expires in 15 days
            'days_until_expiry' => 15,
            'is_renewed' => false,
            'is_hidden' => false,
        ]);

        // 3. User REMOVED from dashboard (GRAY)
        $hiddenUser = User::create([
            'name' => 'Mike Hidden',
            'email' => 'hidden@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Create hidden user's membership payment
        $hiddenPayment = Payment::create([
            'user_id' => $hiddenUser->id,
            'amount' => 35000, // CHF 350.00
            'payment_type' => 'membership',
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'transaction_id' => 'test_hidden_' . uniqid(),
            'created_at' => now()->subMonths(10), // 10 months ago
        ]);

        // Create hidden membership renewal (removed from dashboard)
        MembershipRenewal::create([
            'user_id' => $hiddenUser->id,
            'payment_id' => $hiddenPayment->id,
            'membership_start_date' => now()->subMonths(10),
            'membership_end_date' => now()->addDays(20), // Expires in 20 days
            'days_until_expiry' => 20,
            'is_renewed' => false,
            'is_hidden' => false, // Changed to false so he appears in dashboard
            'admin_notes' => 'Test user - will be hidden after delete button test',
        ]);

        $this->command->info('✅ Created 3 test users:');
        $this->command->info('🔴 John Expired (expired@test.com) - RED background');
        $this->command->info('🟠 Sarah Expiring (expiring@test.com) - ORANGE background'); 
        $this->command->info('⚪ Mike Hidden (hidden@test.com) - GRAY background');
        $this->command->info('Password for all: password');
    }
} 