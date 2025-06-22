<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database for production.
     * Only creates essential admin accounts without test data.
     */
    public function run(): void
    {
        // Create super admin user for production
        // IMPORTANT: Change these credentials immediately after first login!
        
        User::firstOrCreate(
            ['email' => 'admin@ennur.ch'], // Use your actual domain
            [
                'name' => 'EN NUR Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('ENnur2025!Admin'), // Strong temporary password
                'role' => User::ROLE_SUPER_ADMIN,
            ]
        );

        $this->command->info('✅ Production admin created successfully');
        $this->command->info('🔐 Admin Login: admin@ennur.ch');
        $this->command->info('🔑 Temporary Password: ENnur2025!Admin');
        $this->command->info('⚠️  CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN!');
    }
} 