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
        $this->info("Current days until expiry: {$renewal->days_until_expiry}");
        $this->info("Current is_expired: " . ($renewal->is_expired ? 'YES' : 'NO'));
        
        // Make expired exactly 15 days ago (within dashboard filter range)
        $expiredDate = now()->subDays(15)->format('Y-m-d');
        $renewal->membership_end_date = $expiredDate;
        $renewal->days_until_expiry = -15;
        $renewal->is_expired = true;
        $renewal->is_hidden = false;
        $renewal->is_renewed = false;
        $renewal->save();
        
        $this->info("✅ Updated end date: {$renewal->membership_end_date}");
        $this->info("✅ Updated days until expiry: {$renewal->days_until_expiry}");
        $this->info("✅ Updated is_expired: " . ($renewal->is_expired ? 'YES' : 'NO'));
        
        // Test dashboard filter logic
        $calculated = $renewal->calculateDaysUntilExpiry();
        $willAppear = ($calculated <= 30 && $calculated > -30 && !$renewal->is_hidden && !$renewal->is_renewed);
        
        $this->info("🔍 Dashboard filter test:");
        $this->info("- Calculated days: {$calculated}");
        $this->info("- Will appear in dashboard: " . ($willAppear ? 'YES ✅' : 'NO ❌'));
        
        $this->info('🎉 User is now EXPIRED and ready for testing!');
        
        return 0;
    }
} 