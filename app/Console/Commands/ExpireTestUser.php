<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MembershipRenewal;

class ExpireTestUser extends Command
{
    protected $signature = 'user:expire-test-user';
    protected $description = 'Make infinitdizzajn@gmail.com expired for testing';

    public function handle()
    {
        $this->info('🔧 Making infinitdizzajn@gmail.com expired for testing...');
        
        $user = User::where('email', 'infinitdizzajn@gmail.com')->first();
        
        if (!$user) {
            $this->error('❌ User not found!');
            return 1;
        }
        
        $renewal = MembershipRenewal::where('user_id', $user->id)->first();
        
        if (!$renewal) {
            $this->error('❌ No membership renewal found!');
            return 1;
        }
        
        $this->info("Current end date: {$renewal->membership_end_date}");
        
        // Make expired (1 year ago)
        $renewal->membership_end_date = '2024-01-01';
        $renewal->save();
        
        $this->info("✅ Updated end date: {$renewal->membership_end_date}");
        $this->info('🎉 User is now EXPIRED and ready for testing!');
        
        return 0;
    }
} 