<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetupSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:setup-super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all existing super admins and create the specified super admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Super Admin...');

        // Find and remove all existing super admins
        $existingSuperAdmins = User::where('role', User::ROLE_SUPER_ADMIN)->get();
        
        if ($existingSuperAdmins->count() > 0) {
            $this->info("Found {$existingSuperAdmins->count()} existing super admin(s). Removing them...");
            
            foreach ($existingSuperAdmins as $admin) {
                $this->info("- Removing: {$admin->name} ({$admin->email})");
                $admin->delete();
            }
        }

        // Create the new super admin
        $superAdmin = User::create([
            'name' => 'SUPER ADMIN',
            'email' => 'kushtrim.m.arifi@gmail.com',
                            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'change-me')),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->info('✅ Super Admin created successfully!');
        $this->info('');
        $this->info('Super Admin Details:');
        $this->info('📧 Email: kushtrim.m.arifi@gmail.com');
        $this->info('👤 Name: SUPER ADMIN');
        $this->info('🔑 Password: [CONFIGURED_VIA_SUPER_ADMIN_PASSWORD_ENV]');
        $this->info('🔐 Role: super_admin');
        $this->info('');
        $this->info('🎉 You can now login with these credentials!');
        
        return 0;
    }
} 