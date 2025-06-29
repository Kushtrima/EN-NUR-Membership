<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 FINDING THE EXACT PROBLEM WITH infinitdizzajn@gmail.com\n";
echo "================================================================\n\n";

// 1. Find the user
$user = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
if (!$user) {
    echo "❌ User not found!\n";
    exit;
}

echo "✅ User found: {$user->name} (ID: {$user->id})\n";

// 2. Find ALL their renewals
$renewals = \App\Models\MembershipRenewal::where('user_id', $user->id)->get();
echo "📊 Total renewals for this user: {$renewals->count()}\n";

foreach ($renewals as $renewal) {
    echo "\n🔄 Renewal ID: {$renewal->id}\n";
    echo "- End Date: {$renewal->membership_end_date}\n";
    echo "- Days Until Expiry (stored): {$renewal->days_until_expiry}\n";
    echo "- Days Until Expiry (calculated): " . $renewal->calculateDaysUntilExpiry() . "\n";
    echo "- Is Expired: " . ($renewal->is_expired ? 'YES' : 'NO') . "\n";
    echo "- Is Hidden: " . ($renewal->is_hidden ? 'YES' : 'NO') . "\n";
    echo "- Is Renewed: " . ($renewal->is_renewed ? 'YES' : 'NO') . "\n";
    
    // Test the EXACT dashboard filter
    $calculated = $renewal->calculateDaysUntilExpiry();
    $passesFilter = ($calculated <= 30 && $calculated > -30 && !$renewal->is_hidden && !$renewal->is_renewed);
    echo "- Passes Dashboard Filter: " . ($passesFilter ? 'YES ✅' : 'NO ❌') . "\n";
    
    if (!$passesFilter) {
        echo "  📋 Filter breakdown:\n";
        echo "    - \$calculated <= 30: " . ($calculated <= 30 ? 'YES' : 'NO') . "\n";
        echo "    - \$calculated > -30: " . ($calculated > -30 ? 'YES' : 'NO') . "\n";
        echo "    - !is_hidden: " . (!$renewal->is_hidden ? 'YES' : 'NO') . "\n";
        echo "    - !is_renewed: " . (!$renewal->is_renewed ? 'YES' : 'NO') . "\n";
    }
}

echo "\n🎛️ TESTING SUPER ADMIN DASHBOARD LOGIC:\n";

// Test the EXACT code from DashboardController.php - using stored days_until_expiry
$renewalsNeedingAttention = \App\Models\MembershipRenewal::where('days_until_expiry', '<=', 30)
    ->where('is_hidden', false)
    ->where('is_renewed', false)
    ->with(['user', 'payment'])
    ->get();

echo "- Total renewals from DashboardController query: {$renewalsNeedingAttention->count()}\n";

$infinitInResults = $renewalsNeedingAttention->where('user_id', $user->id)->first();
echo "- infinitdizzajn in results: " . ($infinitInResults ? 'YES ✅' : 'NO ❌') . "\n";

if ($infinitInResults) {
    echo "- Found in dashboard query!\n";
} else {
    echo "- NOT found in dashboard query!\n";
    echo "  This means the stored days_until_expiry value is wrong!\n";
    
    // Show the issue
    $userRenewal = \App\Models\MembershipRenewal::where('user_id', $user->id)->first();
    if ($userRenewal) {
        echo "\n🚨 PROBLEM IDENTIFIED:\n";
        echo "- Stored days_until_expiry: {$userRenewal->days_until_expiry}\n";
        echo "- Dashboard query looks for: days_until_expiry <= 30\n";
        echo "- User's value: {$userRenewal->days_until_expiry}\n";
        echo "- Passes <= 30 test: " . ($userRenewal->days_until_expiry <= 30 ? 'YES' : 'NO') . "\n";
        
        if ($userRenewal->days_until_expiry > 30) {
            echo "❌ STORED VALUE IS TOO HIGH! Need to update days_until_expiry in database.\n";
        } elseif ($userRenewal->days_until_expiry <= -30) {
            echo "❌ STORED VALUE IS TOO LOW! Need to update days_until_expiry in database.\n";
        }
    }
}

echo "\n🔧 SOLUTION:\n";
echo "The dashboard uses the STORED 'days_until_expiry' value, not the calculated one!\n";
echo "We need to update the stored value to match the calculated value.\n"; 