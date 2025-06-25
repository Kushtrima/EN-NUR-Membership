<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetupProductionAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:setup-production {email} {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and verify super admin for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name') ?: 'SUPER ADMIN';

        $this->info('🚀 Setting up production super admin...');

        // Find or create the admin
        $admin = User::where('email', $email)->first();

        if (!$admin) {
            $this->error("❌ Admin with email {$email} not found!");
            $this->info("Please create the admin account first or use the correct email.");
            return 1;
        }

        // Verify email if not already verified
        if (!$admin->email_verified_at) {
            $admin->email_verified_at = now();
            $this->info("✅ Email verified for {$admin->name}");
        }

        // Ensure super admin role
        if ($admin->role !== 'super_admin') {
            $admin->role = 'super_admin';
            $this->info("✅ Role updated to super_admin");
        }

        // Update name if provided
        if ($name !== 'SUPER ADMIN' && $admin->name !== $name) {
            $admin->name = $name;
            $this->info("✅ Name updated to {$name}");
        }

        $admin->save();

        $this->info('');
        $this->info('🎉 Production super admin setup complete!');
        $this->info("   Name: {$admin->name}");
        $this->info("   Email: {$admin->email}");
        $this->info("   Role: {$admin->role}");
        $this->info("   Verified: " . ($admin->email_verified_at ? '✅ Yes' : '❌ No'));
        $this->info('');
        $this->info('🌈 Admin dashboard with expired users color system is ready!');

        return 0;
    }
} 