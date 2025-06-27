<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CleanSetupSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:clean-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean ALL admin accounts and create only the specified super admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 CLEANING ALL ADMIN ACCOUNTS...');
        $this->info('=====================================');

        // Step 1: Find and delete ALL admin and super_admin accounts
        $this->cleanAllAdminAccounts();

        // Step 2: Create the ONE and ONLY super admin
        $this->createSuperAdmin();

        // Step 3: Verify the setup
        $this->verifySetup();

        $this->info('');
        $this->info('🎉 SETUP COMPLETE!');
        $this->info('You now have ONLY ONE super admin account.');

        return 0;
    }

    private function cleanAllAdminAccounts()
    {
        $this->info('');
        $this->info('🗑️ Removing ALL existing admin accounts...');

        // Find all admin and super_admin users
        $adminUsers = User::whereIn('role', ['admin', 'super_admin'])->get();

        if ($adminUsers->count() === 0) {
            $this->info('✅ No existing admin accounts found.');
            return;
        }

        $this->info("Found {$adminUsers->count()} admin account(s) to remove:");

        foreach ($adminUsers as $admin) {
            $this->info("  - Deleting: {$admin->name} ({$admin->email}) - Role: {$admin->role}");
            
            // Delete related data first to avoid foreign key constraints
            $admin->membershipRenewals()->delete();
            $admin->payments()->delete();
            $admin->delete();
        }

        $this->info('✅ All admin accounts removed.');
    }

    private function createSuperAdmin()
    {
        $this->info('');
        $this->info('👑 Creating YOUR super admin account...');

        $superAdmin = User::create([
            'name' => 'SUPER ADMIN',
            'email' => 'kushtrim.m.arifi@gmail.com',
                            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'change-me')),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->info('✅ Super Admin created successfully!');
        $this->info("   ID: {$superAdmin->id}");
        $this->info("   Name: {$superAdmin->name}");
        $this->info("   Email: {$superAdmin->email}");
        $this->info("   Role: {$superAdmin->role}");
    }

    private function verifySetup()
    {
        $this->info('');
        $this->info('🔍 VERIFICATION:');

        // Check total admin count
        $adminCount = User::whereIn('role', ['admin', 'super_admin'])->count();
        $this->info("Total admin accounts: {$adminCount}");

        if ($adminCount !== 1) {
            $this->error("❌ ERROR: Expected 1 admin account, found {$adminCount}");
            return;
        }

        // Verify the specific account
        $superAdmin = User::where('email', 'kushtrim.m.arifi@gmail.com')->first();
        
        if (!$superAdmin) {
            $this->error('❌ ERROR: Super admin account not found!');
            return;
        }

        if ($superAdmin->role !== User::ROLE_SUPER_ADMIN) {
            $this->error("❌ ERROR: Account role is '{$superAdmin->role}', expected 'super_admin'");
            return;
        }

        // Test password
        $passwordWorks = Hash::check(env('SUPER_ADMIN_PASSWORD', 'change-me'), $superAdmin->password);
        if (!$passwordWorks) {
            $this->error('❌ ERROR: Password verification failed!');
            return;
        }

        $this->info('✅ Verification PASSED!');
        $this->info('');
        $this->info('🔐 LOGIN CREDENTIALS:');
        $this->info('📧 Email: kushtrim.m.arifi@gmail.com');
        $this->info('🔑 Password: [CONFIGURED_VIA_SUPER_ADMIN_PASSWORD_ENV]');
        $this->info('🌐 URL: https://en-nur-membership.onrender.com/login');
    }
} 