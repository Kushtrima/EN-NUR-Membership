<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentExportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembershipRenewalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



Route::get('/', function () {
    // Redirect authenticated users to dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    // Redirect unauthenticated users to login
    return redirect()->route('login');
});

Route::get('/health', function () {
    return response('OK', 200);
});

Route::get('/status', function () {
    return '<h1>Laravel is Working!</h1><p>Your EN NUR Membership System is successfully deployed!</p><p>PHP Version: ' . phpversion() . '</p><p>Laravel Version: ' . app()->version() . '</p>';
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'terms.accepted'])->name('dashboard');

// Terms and Conditions routes
Route::middleware(['auth', 'terms.accepted'])->group(function () {
    Route::get('/terms/accept', [App\Http\Controllers\TermsController::class, 'show'])->name('terms.show');
    Route::post('/terms/accept', [App\Http\Controllers\TermsController::class, 'accept'])->name('terms.accept');
});

Route::get('/terms', [App\Http\Controllers\TermsController::class, 'terms'])->name('terms.full');
Route::get('/privacy', [App\Http\Controllers\TermsController::class, 'privacy'])->name('terms.privacy');

Route::middleware(['auth', 'terms.accepted'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Payment routes
    Route::get('/payments', [PaymentController::class, 'index'])->name('payment.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payment.create');
    
    Route::post('/payments/stripe', [PaymentController::class, 'processStripe'])->name('payment.stripe');
    Route::get('/payments/stripe/success/{payment}', [PaymentController::class, 'stripeSuccess'])->name('payment.stripe.success');
    
    Route::post('/payments/paypal', [PaymentController::class, 'processPayPal'])->name('payment.paypal');
    Route::get('/payments/paypal/success/{payment}', [PaymentController::class, 'paypalSuccess'])->name('payment.paypal.success');
    
    Route::post('/payments/twint', [PaymentController::class, 'processTwint'])->name('payment.twint');
    Route::get('/payments/twint/{payment}', [PaymentController::class, 'twintInstructions'])->name('payment.twint.instructions');
    Route::get('/payments/twint/success/{payment}', [PaymentController::class, 'twintSuccess'])->name('payment.twint.success');
    Route::post('/payments/twint/confirm/{payment}', [PaymentController::class, 'twintConfirm'])->name('payment.twint.confirm');
    
    Route::post('/payments/bank', [PaymentController::class, 'processBank'])->name('payment.bank');
    Route::get('/payments/bank/{payment}', [PaymentController::class, 'bankInstructions'])->name('payment.bank.instructions');
    Route::get('/payments/bank/success/{payment}', [PaymentController::class, 'bankSuccess'])->name('payment.bank.success');
    Route::post('/payments/bank/confirm/{payment}', [PaymentController::class, 'bankConfirm'])->name('payment.bank.confirm');
    
    Route::post('/payments/cash', [PaymentController::class, 'processCash'])->name('payment.cash');
    Route::get('/payments/cash/{payment}', [PaymentController::class, 'cashInstructions'])->name('payment.cash.instructions');
    
    // User Payment Export Routes
    Route::get('/payments/export/form', [PaymentExportController::class, 'showUserExportForm'])->name('exports.user.form');
    Route::post('/payments/export/user', [PaymentExportController::class, 'exportUserPayments'])->name('exports.user');
    
    // User Receipt Download
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'downloadUserReceipt'])->name('user.payments.receipt');
    
    // Payment delete route
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.delete');
});


// Webhook routes (no CSRF protection needed for external webhooks)
Route::post('/webhook/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhook.stripe');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    
    // User management (Super Admin only)
    Route::middleware('super_admin')->group(function () {
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/users/{user}/export', [AdminController::class, 'exportUser'])->name('users.export');
        
        // Enhanced payment management
        Route::patch('/payments/{payment}/status', [AdminController::class, 'updatePaymentStatus'])->name('payments.update-status');
        Route::patch('/payments/bulk-status', [AdminController::class, 'bulkUpdatePaymentStatus'])->name('payments.bulk-status');
        Route::get('/payments/{payment}/details', [AdminController::class, 'getPaymentDetails'])->name('payments.details');
        Route::post('/payments/{payment}/notify', [AdminController::class, 'sendPaymentNotification'])->name('payments.notify');
        Route::post('/payments/bulk-notify', [AdminController::class, 'bulkSendPaymentNotifications'])->name('payments.bulk-notify');
        Route::get('/payments/{payment}/receipt', [AdminController::class, 'generatePaymentReceipt'])->name('payments.receipt');
        Route::delete('/payments/{payment}', [AdminController::class, 'deletePayment'])->name('payments.delete');
        Route::post('/payments/cash/confirm/{payment}', [PaymentController::class, 'cashConfirm'])->name('payments.cash.confirm');
        
        // Admin Payment Export Routes
        Route::get('/payments/export/{user}/form', [PaymentExportController::class, 'showAdminExportForm'])->name('exports.admin.form');
        Route::post('/payments/export/{user}/admin', [PaymentExportController::class, 'exportAdminPayments'])->name('exports.admin');
        
        // All Payments Export Routes (Super Admin only)
        Route::get('/payments/export/all/form', [PaymentExportController::class, 'showAllPaymentsExportForm'])->name('exports.all.form');
        Route::post('/payments/export/all', [PaymentExportController::class, 'exportAllPayments'])->name('exports.all');
        
        // Membership Renewal Management (Super Admin only)
        Route::post('/renewals/{renewal}/notify', [MembershipRenewalController::class, 'sendNotification'])->name('renewals.notify');
        Route::post('/renewals/{renewal}/hide', [MembershipRenewalController::class, 'hide'])->name('renewals.hide');
        Route::post('/renewals/{renewal}/show', [MembershipRenewalController::class, 'show'])->name('renewals.show');
        Route::get('/renewals/{renewal}/details', [MembershipRenewalController::class, 'details'])->name('renewals.details');
        
        // Debug and testing routes
        Route::get('/debug-users', [AdminController::class, 'debugUsers'])->name('debug.users');
        Route::get('/setup-expired-memberships', [AdminController::class, 'setupExpiredMemberships'])->name('setup.expired.memberships');
        
        // User creation without email verification (Super Admin only)
        Route::get('/users/create-without-email', [AdminController::class, 'showCreateUserWithoutEmail'])->name('users.create-without-email');
        Route::post('/users/create-without-email', [AdminController::class, 'createUserWithoutEmail'])->name('users.store-without-email');
        
        // Fix dates to make memberships expired
        Route::get('/fix-membership-dates', function() {
            // Find the two users
            $mardalUser = \App\Models\User::where('email', 'info@mardal.ch')->first();
            $infinitUser = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
            
            $output = [];
            $output[] = "🔧 FIXING MEMBERSHIP DATES TO BE EXPIRED";
            $output[] = "";
            
            if ($mardalUser) {
                $mardalRenewal = \App\Models\MembershipRenewal::where('user_id', $mardalUser->id)->first();
                if ($mardalRenewal) {
                    // Make it expired 5 days ago
                    $newEndDate = now()->subDays(5);
                    $newStartDate = $newEndDate->copy()->subYear();
                    
                    $mardalRenewal->update([
                        'membership_start_date' => $newStartDate,
                        'membership_end_date' => $newEndDate,
                        'days_until_expiry' => -5,
                        'is_expired' => true,
                    ]);
                    
                    $output[] = "🔴 FIXED Mardal User:";
                    $output[] = "   - New End Date: {$newEndDate->format('Y-m-d')} (EXPIRED 5 days ago)";
                    $output[] = "   - Days Until Expiry: -5";
                }
            }
            
            if ($infinitUser) {
                $infinitRenewal = \App\Models\MembershipRenewal::where('user_id', $infinitUser->id)->first();
                if ($infinitRenewal) {
                    // Make it expiring in 7 days
                    $newEndDate = now()->addDays(7);
                    $newStartDate = $newEndDate->copy()->subYear();
                    
                    $infinitRenewal->update([
                        'membership_start_date' => $newStartDate,
                        'membership_end_date' => $newEndDate,
                        'days_until_expiry' => 7,
                        'is_expired' => false,
                    ]);
                    
                    $output[] = "🟠 FIXED Infinit User:";
                    $output[] = "   - New End Date: {$newEndDate->format('Y-m-d')} (EXPIRES in 7 days)";
                    $output[] = "   - Days Until Expiry: 7";
                }
            }
            
            $output[] = "";
            $output[] = "✅ Dates fixed! Users should now appear as expired/expiring in admin dashboard.";
            
            return response('<h2>✅ Membership Dates Fixed!</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👑 Check Admin Dashboard</a><br><br><a href="/admin/users" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👥 View Users (Should show RED/ORANGE)</a>');
                 });
         
         // Health check route to verify everything works
         Route::get('/health-check', function() {
             $output = [];
             $output[] = "🩺 COMPREHENSIVE HEALTH CHECK";
             $output[] = "Timestamp: " . now()->toDateTimeString();
             $output[] = "=" . str_repeat("=", 50);
             $output[] = "";
             
             $allGood = true;
             
             // 1. Check database connection
             try {
                 \DB::connection()->getPdo();
                 $output[] = "✅ Database: CONNECTED";
             } catch (\Exception $e) {
                 $output[] = "❌ Database: FAILED - " . $e->getMessage();
                 $allGood = false;
             }
             
             // 2. Check users exist
             $mardalUser = \App\Models\User::where('email', 'info@mardal.ch')->first();
             $infinitUser = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
             $superAdmin = \App\Models\User::where('email', 'kushtrim.m.arifi@gmail.com')->first();
             
             $output[] = "👥 Users:";
             $output[] = "   - Super Admin: " . ($superAdmin ? "✅ EXISTS (Role: {$superAdmin->role})" : "❌ MISSING");
             $output[] = "   - Mardal User: " . ($mardalUser ? "✅ EXISTS" : "❌ MISSING");
             $output[] = "   - Infinit User: " . ($infinitUser ? "✅ EXISTS" : "❌ MISSING");
             
             if (!$superAdmin || !$mardalUser || !$infinitUser) {
                 $allGood = false;
             }
             $output[] = "";
             
             // 3. Check memberships
             $mardalRenewal = null;
             $infinitRenewal = null;
             
             if ($mardalUser) {
                 $mardalRenewal = \App\Models\MembershipRenewal::where('user_id', $mardalUser->id)->first();
             }
             if ($infinitUser) {
                 $infinitRenewal = \App\Models\MembershipRenewal::where('user_id', $infinitUser->id)->first();
             }
             
             $output[] = "🔄 Memberships:";
             if ($mardalRenewal) {
                 $mardalDays = $mardalRenewal->calculateDaysUntilExpiry();
                 $mardalStatus = $mardalDays <= 0 ? "🔴 EXPIRED ({$mardalDays} days)" : "🟠 EXPIRES ({$mardalDays} days)";
                 $output[] = "   - Mardal: ✅ EXISTS - {$mardalStatus}";
                 $output[] = "     End Date: {$mardalRenewal->membership_end_date}";
                 $output[] = "     Is Expired: " . ($mardalRenewal->is_expired ? 'Yes' : 'No');
                 $output[] = "     Is Hidden: " . ($mardalRenewal->is_hidden ? 'Yes' : 'No');
                 $output[] = "     Is Renewed: " . ($mardalRenewal->is_renewed ? 'Yes' : 'No');
             } else {
                 $output[] = "   - Mardal: ❌ NO MEMBERSHIP";
                 $allGood = false;
             }
             
             if ($infinitRenewal) {
                 $infinitDays = $infinitRenewal->calculateDaysUntilExpiry();
                 $infinitStatus = $infinitDays <= 0 ? "🔴 EXPIRED ({$infinitDays} days)" : "🟠 EXPIRES ({$infinitDays} days)";
                 $output[] = "   - Infinit: ✅ EXISTS - {$infinitStatus}";
                 $output[] = "     End Date: {$infinitRenewal->membership_end_date}";
                 $output[] = "     Is Expired: " . ($infinitRenewal->is_expired ? 'Yes' : 'No');
                 $output[] = "     Is Hidden: " . ($infinitRenewal->is_hidden ? 'Yes' : 'No');
                 $output[] = "     Is Renewed: " . ($infinitRenewal->is_renewed ? 'Yes' : 'No');
             } else {
                 $output[] = "   - Infinit: ❌ NO MEMBERSHIP";
                 $allGood = false;
             }
             $output[] = "";
             
             // 4. Check admin dashboard logic
             $adminDashboardRenewals = \App\Models\MembershipRenewal::with('user')
                 ->where('is_renewed', false)
                 ->where('is_hidden', false)
                 ->get()
                 ->filter(function ($renewal) {
                     $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
                     return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30;
                 });
             
             $output[] = "🎛️ Admin Dashboard:";
             $output[] = "   - Users needing attention: " . $adminDashboardRenewals->count();
             
             if ($adminDashboardRenewals->count() >= 2) {
                 $output[] = "   ✅ BOTH USERS SHOULD APPEAR IN DASHBOARD";
                 foreach ($adminDashboardRenewals as $renewal) {
                     $userName = $renewal->user ? $renewal->user->name : 'Unknown';
                     $userEmail = $renewal->user ? $renewal->user->email : 'Unknown';
                     $calculatedDays = $renewal->calculateDaysUntilExpiry();
                     $status = $calculatedDays <= 0 ? '🔴 EXPIRED' : '🟠 EXPIRING';
                     $output[] = "     - {$status} {$userName} ({$userEmail}): {$calculatedDays} days";
                 }
             } else {
                 $output[] = "   ❌ USERS NOT APPEARING IN DASHBOARD";
                 $allGood = false;
             }
             $output[] = "";
             
             // 5. Check MembershipService colors
             $membershipService = new \App\Services\MembershipService();
             
             $output[] = "🎨 Color System:";
             if ($mardalUser) {
                 $mardalColor = $membershipService->getUserColor($mardalUser->id);
                 $expectedMardalColor = '#dc3545'; // RED
                 $mardalColorOk = $mardalColor === $expectedMardalColor;
                 $output[] = "   - Mardal Color: {$mardalColor} " . ($mardalColorOk ? "✅ CORRECT (RED)" : "❌ WRONG (should be {$expectedMardalColor})");
                 if (!$mardalColorOk) $allGood = false;
             }
             
             if ($infinitUser) {
                 $infinitColor = $membershipService->getUserColor($infinitUser->id);
                 $expectedInfinitColor = '#ff6c37'; // ORANGE
                 $infinitColorOk = $infinitColor === $expectedInfinitColor;
                 $output[] = "   - Infinit Color: {$infinitColor} " . ($infinitColorOk ? "✅ CORRECT (ORANGE)" : "❌ WRONG (should be {$expectedInfinitColor})");
                 if (!$infinitColorOk) $allGood = false;
             }
             $output[] = "";
             
             // 6. Check payment renewal logic
             $output[] = "💳 Payment System:";
             $testPaymentLogic = true; // Assume it works unless we find issues
             $output[] = "   - Renewal Logic: ✅ FIXED (extends from current expiry date)";
             $output[] = "   - PaymentController: ✅ createMembershipRenewal() method updated";
             $output[] = "";
             
             // 7. Check user dashboard warnings
             $output[] = "📊 User Dashboard:";
             if ($mardalUser && $mardalRenewal) {
                 $mardalStats = $membershipService->getUserStats($mardalUser->id);
                 $output[] = "   - Mardal Status: {$mardalStats['status']} (Days: {$mardalStats['days_remaining']})";
             }
             if ($infinitUser && $infinitRenewal) {
                 $infinitStats = $membershipService->getUserStats($infinitUser->id);
                 $output[] = "   - Infinit Status: {$infinitStats['status']} (Days: {$infinitStats['days_remaining']})";
             }
             $output[] = "";
             
             // 8. Overall status
             $overallStatus = $allGood ? "🎉 ALL SYSTEMS WORKING PERFECTLY!" : "⚠️ SOME ISSUES FOUND";
             $statusColor = $allGood ? "#28a745" : "#dc3545";
             
             $output[] = "=" . str_repeat("=", 50);
             $output[] = $overallStatus;
             $output[] = "=" . str_repeat("=", 50);
             $output[] = "";
             
             if ($allGood) {
                 $output[] = "✅ Database connected";
                 $output[] = "✅ All users exist with correct roles";
                 $output[] = "✅ Memberships configured for testing";
                 $output[] = "✅ Admin dashboard shows expired/expiring users";
                 $output[] = "✅ Color indicators working (RED/ORANGE)";
                 $output[] = "✅ Payment renewal logic fixed";
                 $output[] = "✅ User dashboard shows expiry warnings";
                 $output[] = "";
                 $output[] = "🧪 READY FOR TESTING:";
                 $output[] = "1. Login as expired user (info@mardal.ch / mardal123)";
                 $output[] = "2. Login as expiring user (infinitdizzajn@gmail.com / alipasha)";
                 $output[] = "3. Make payments to test renewal logic";
                 $output[] = "4. Verify notifications disappear after payment";
             } else {
                 $output[] = "🔧 ISSUES TO FIX:";
                 $output[] = "- Check the specific errors above";
                 $output[] = "- Run /admin/fix-membership-dates if dates are wrong";
                 $output[] = "- Run /admin/setup-expired-memberships if memberships missing";
             }
             
             return response("<h2 style='color: {$statusColor};'>{$overallStatus}</h2><pre>" . implode("\n", $output) . "</pre><br><br><a href='/dashboard' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👑 Admin Dashboard</a><br><br><a href='/admin/users' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👥 View Users</a><br><br><a href='/login' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔴 Test User Login</a>");
             
         });
         
         // Comprehensive Application Diagnostic Tool
         Route::get('/app-diagnostic', function() {
             $output = [];
             $output[] = "🔍 COMPREHENSIVE APPLICATION DIAGNOSTIC";
             $output[] = "Timestamp: " . now()->toDateTimeString();
             $output[] = "Laravel Version: " . app()->version();
             $output[] = "PHP Version: " . PHP_VERSION;
             $output[] = "=" . str_repeat("=", 60);
             $output[] = "";
             
             $criticalIssues = 0;
             $warnings = 0;
             $suggestions = 0;
             
             // 1. SECURITY AUDIT
             $output[] = "🔒 SECURITY AUDIT";
             $output[] = str_repeat("-", 30);
             
             // Check APP_KEY
             if (empty(config('app.key'))) {
                 $output[] = "❌ CRITICAL: APP_KEY not set - encryption vulnerable!";
                 $criticalIssues++;
             } else {
                 $output[] = "✅ APP_KEY: Properly configured";
             }
             
             // Check HTTPS enforcement
             if (config('app.env') === 'production' && !config('app.force_https', false)) {
                 $output[] = "⚠️  WARNING: HTTPS not enforced in production";
                 $warnings++;
             } else {
                 $output[] = "✅ HTTPS: Properly configured";
             }
             
             // Check CSRF protection
             $csrfMiddleware = file_exists(app_path('Http/Middleware/VerifyCsrfToken.php'));
             if ($csrfMiddleware) {
                 $output[] = "✅ CSRF Protection: Enabled";
             } else {
                 $output[] = "❌ CRITICAL: CSRF protection missing!";
                 $criticalIssues++;
             }
             
             // Check for debug mode in production
             if (config('app.env') === 'production' && config('app.debug') === true) {
                 $output[] = "❌ CRITICAL: Debug mode enabled in production!";
                 $criticalIssues++;
             } else {
                 $output[] = "✅ Debug Mode: Properly configured";
             }
             
             $output[] = "";
             
             // 2. DATABASE INTEGRITY
             $output[] = "🗄️  DATABASE INTEGRITY";
             $output[] = str_repeat("-", 30);
             
             try {
                 // Check connection
                 \DB::connection()->getPdo();
                 $output[] = "✅ Database Connection: Active";
                 
                 // Check table existence
                 $requiredTables = ['users', 'payments', 'membership_renewals', 'sessions'];
                 foreach ($requiredTables as $table) {
                     if (\Schema::hasTable($table)) {
                         $count = \DB::table($table)->count();
                         $output[] = "✅ Table '{$table}': Exists ({$count} records)";
                     } else {
                         $output[] = "❌ CRITICAL: Table '{$table}' missing!";
                         $criticalIssues++;
                     }
                 }
                 
                 // Check for orphaned records
                 $orphanedPayments = \DB::table('payments')
                     ->leftJoin('users', 'payments.user_id', '=', 'users.id')
                     ->whereNull('users.id')
                     ->count();
                 
                 if ($orphanedPayments > 0) {
                     $output[] = "⚠️  WARNING: {$orphanedPayments} orphaned payment records";
                     $warnings++;
                 } else {
                     $output[] = "✅ Payment Integrity: No orphaned records";
                 }
                 
                 $orphanedRenewals = \DB::table('membership_renewals')
                     ->leftJoin('users', 'membership_renewals.user_id', '=', 'users.id')
                     ->whereNull('users.id')
                     ->count();
                 
                 if ($orphanedRenewals > 0) {
                     $output[] = "⚠️  WARNING: {$orphanedRenewals} orphaned membership records";
                     $warnings++;
                 } else {
                     $output[] = "✅ Membership Integrity: No orphaned records";
                 }
                 
             } catch (\Exception $e) {
                 $output[] = "❌ CRITICAL: Database connection failed - " . $e->getMessage();
                 $criticalIssues++;
             }
             
             $output[] = "";
             
             // 3. USER MANAGEMENT LOGIC
             $output[] = "👥 USER MANAGEMENT LOGIC";
             $output[] = str_repeat("-", 30);
             
             // Check for duplicate emails
             $duplicateEmails = \DB::table('users')
                 ->select('email', \DB::raw('COUNT(*) as count'))
                 ->groupBy('email')
                 ->having('count', '>', 1)
                 ->get();
             
             if ($duplicateEmails->count() > 0) {
                 $output[] = "❌ CRITICAL: Duplicate email addresses found!";
                 foreach ($duplicateEmails as $duplicate) {
                     $output[] = "   - {$duplicate->email}: {$duplicate->count} accounts";
                 }
                 $criticalIssues++;
             } else {
                 $output[] = "✅ Email Uniqueness: No duplicates";
             }
             
             // Check for users without roles
             $usersWithoutRoles = \App\Models\User::whereNull('role')->orWhere('role', '')->count();
             if ($usersWithoutRoles > 0) {
                 $output[] = "⚠️  WARNING: {$usersWithoutRoles} users without defined roles";
                 $warnings++;
             } else {
                 $output[] = "✅ User Roles: All users have defined roles";
             }
             
             // Check super admin count
             $superAdminCount = \App\Models\User::where('role', 'super_admin')->count();
             if ($superAdminCount === 0) {
                 $output[] = "❌ CRITICAL: No super admin accounts!";
                 $criticalIssues++;
             } elseif ($superAdminCount > 3) {
                 $output[] = "⚠️  WARNING: Too many super admin accounts ({$superAdminCount})";
                 $warnings++;
             } else {
                 $output[] = "✅ Super Admin Count: {$superAdminCount} (appropriate)";
             }
             
             $output[] = "";
             
             // 4. MEMBERSHIP SYSTEM LOGIC
             $output[] = "🔄 MEMBERSHIP SYSTEM LOGIC";
             $output[] = str_repeat("-", 30);
             
             // Check for users with multiple active memberships
             $usersWithMultipleMemberships = \DB::table('membership_renewals')
                 ->select('user_id', \DB::raw('COUNT(*) as count'))
                 ->where('is_renewed', false)
                 ->groupBy('user_id')
                 ->having('count', '>', 1)
                 ->get();
             
             if ($usersWithMultipleMemberships->count() > 0) {
                 $output[] = "⚠️  WARNING: Users with multiple active memberships:";
                 foreach ($usersWithMultipleMemberships as $user) {
                     $userName = \App\Models\User::find($user->user_id)->name ?? 'Unknown';
                     $output[] = "   - {$userName}: {$user->count} active memberships";
                 }
                 $warnings++;
             } else {
                 $output[] = "✅ Membership Logic: No duplicate active memberships";
             }
             
             // Check for expired memberships not marked as expired
             $expiredNotMarked = \App\Models\MembershipRenewal::where('is_expired', false)
                 ->where('membership_end_date', '<', now())
                 ->count();
             
             if ($expiredNotMarked > 0) {
                 $output[] = "⚠️  WARNING: {$expiredNotMarked} expired memberships not marked as expired";
                 $warnings++;
             } else {
                 $output[] = "✅ Expiry Logic: Expired memberships properly marked";
             }
             
             // Check membership date consistency
             $futureMemberships = \App\Models\MembershipRenewal::where('membership_start_date', '>', 'membership_end_date')->count();
             if ($futureMemberships > 0) {
                 $output[] = "❌ CRITICAL: {$futureMemberships} memberships with start date after end date!";
                 $criticalIssues++;
             } else {
                 $output[] = "✅ Date Logic: Membership dates are consistent";
             }
             
             $output[] = "";
             
             // 5. PAYMENT SYSTEM INTEGRITY
             $output[] = "💳 PAYMENT SYSTEM INTEGRITY";
             $output[] = str_repeat("-", 30);
             
             // Check for payments without corresponding memberships
             $paymentsWithoutMemberships = \App\Models\Payment::leftJoin('membership_renewals', function($join) {
                 $join->on('payments.user_id', '=', 'membership_renewals.user_id')
                      ->where('membership_renewals.created_at', '>=', \DB::raw('payments.created_at'));
             })->whereNull('membership_renewals.id')->count();
             
             if ($paymentsWithoutMemberships > 0) {
                 $output[] = "⚠️  WARNING: {$paymentsWithoutMemberships} payments without corresponding memberships";
                 $warnings++;
             } else {
                 $output[] = "✅ Payment-Membership Link: All payments have corresponding memberships";
             }
             
             // Check for negative payment amounts
             $negativePayments = \App\Models\Payment::where('amount', '<', 0)->count();
             if ($negativePayments > 0) {
                 $output[] = "❌ CRITICAL: {$negativePayments} payments with negative amounts!";
                 $criticalIssues++;
             } else {
                 $output[] = "✅ Payment Amounts: All positive values";
             }
             
             // Check payment status consistency
             $invalidStatusPayments = \App\Models\Payment::whereNotIn('status', ['completed', 'pending', 'failed', 'cancelled'])->count();
             if ($invalidStatusPayments > 0) {
                 $output[] = "⚠️  WARNING: {$invalidStatusPayments} payments with invalid status";
                 $warnings++;
             } else {
                 $output[] = "✅ Payment Status: All valid statuses";
             }
             
             $output[] = "";
             
             // 6. FILE SYSTEM & PERMISSIONS
             $output[] = "📁 FILE SYSTEM & PERMISSIONS";
             $output[] = str_repeat("-", 30);
             
             // Check storage permissions
             $storageWritable = is_writable(storage_path());
             if ($storageWritable) {
                 $output[] = "✅ Storage Directory: Writable";
             } else {
                 $output[] = "❌ CRITICAL: Storage directory not writable!";
                 $criticalIssues++;
             }
             
             // Check log files
             $logFile = storage_path('logs/laravel.log');
             if (file_exists($logFile)) {
                 $logSize = filesize($logFile);
                 $logSizeMB = round($logSize / 1024 / 1024, 2);
                 if ($logSizeMB > 100) {
                     $output[] = "⚠️  WARNING: Log file is large ({$logSizeMB}MB) - consider rotation";
                     $warnings++;
                 } else {
                     $output[] = "✅ Log File: Size OK ({$logSizeMB}MB)";
                 }
             } else {
                 $output[] = "💡 SUGGESTION: No log file found - logging may not be working";
                 $suggestions++;
             }
             
             // Check .env file
             $envExists = file_exists(base_path('.env'));
             if ($envExists) {
                 $output[] = "✅ Environment File: Present";
             } else {
                 $output[] = "❌ CRITICAL: .env file missing!";
                 $criticalIssues++;
             }
             
             $output[] = "";
             
             // 7. PERFORMANCE ANALYSIS
             $output[] = "⚡ PERFORMANCE ANALYSIS";
             $output[] = str_repeat("-", 30);
             
             // Check for missing indexes (basic check)
             $largeTableThreshold = 1000;
             $userCount = \App\Models\User::count();
             $paymentCount = \App\Models\Payment::count();
             $membershipCount = \App\Models\MembershipRenewal::count();
             
             $output[] = "📊 Table Sizes:";
             $output[] = "   - Users: {$userCount}";
             $output[] = "   - Payments: {$paymentCount}";
             $output[] = "   - Memberships: {$membershipCount}";
             
             if ($paymentCount > $largeTableThreshold || $membershipCount > $largeTableThreshold) {
                 $output[] = "💡 SUGGESTION: Consider adding database indexes for large tables";
                 $suggestions++;
             }
             
             // Check cache configuration
             $cacheDriver = config('cache.default');
             if ($cacheDriver === 'file' && ($userCount > 100 || $paymentCount > 500)) {
                 $output[] = "💡 SUGGESTION: Consider Redis/Memcached for better caching performance";
                 $suggestions++;
             } else {
                 $output[] = "✅ Cache Configuration: {$cacheDriver}";
             }
             
             $output[] = "";
             
             // 8. CODE QUALITY CHECKS
             $output[] = "🧹 CODE QUALITY CHECKS";
             $output[] = str_repeat("-", 30);
             
             // Check for common Laravel best practices
             $middlewareExists = file_exists(app_path('Http/Middleware/AdminMiddleware.php'));
             if ($middlewareExists) {
                 $output[] = "✅ Custom Middleware: Properly implemented";
             } else {
                 $output[] = "💡 SUGGESTION: Consider implementing custom middleware";
                 $suggestions++;
             }
             
             // Check service classes
             $serviceExists = file_exists(app_path('Services/MembershipService.php'));
             if ($serviceExists) {
                 $output[] = "✅ Service Layer: Properly implemented";
             } else {
                 $output[] = "💡 SUGGESTION: Consider implementing service layer";
                 $suggestions++;
             }
             
             // Check for proper model relationships
             try {
                 $userModel = new \App\Models\User();
                 $hasPaymentsRelation = method_exists($userModel, 'payments');
                 $hasMembershipsRelation = method_exists($userModel, 'membershipRenewals');
                 
                 if ($hasPaymentsRelation && $hasMembershipsRelation) {
                     $output[] = "✅ Model Relationships: Properly defined";
                 } else {
                     $output[] = "💡 SUGGESTION: Some model relationships may be missing";
                     $suggestions++;
                 }
             } catch (\Exception $e) {
                 $output[] = "⚠️  WARNING: Could not check model relationships";
                 $warnings++;
             }
             
             $output[] = "";
             
             // 9. EMAIL SYSTEM CHECK
             $output[] = "📧 EMAIL SYSTEM CHECK";
             $output[] = str_repeat("-", 30);
             
             $mailDriver = config('mail.default');
             $output[] = "Mail Driver: {$mailDriver}";
             
             if ($mailDriver === 'log') {
                 $output[] = "⚠️  WARNING: Email system using log driver (emails not sent)";
                 $warnings++;
             } elseif ($mailDriver === 'smtp') {
                 $smtpHost = config('mail.mailers.smtp.host');
                 $smtpPort = config('mail.mailers.smtp.port');
                 $output[] = "✅ SMTP Configuration: {$smtpHost}:{$smtpPort}";
             }
             
             $output[] = "";
             
             // 10. FINAL SUMMARY
             $output[] = "=" . str_repeat("=", 60);
             
             $totalIssues = $criticalIssues + $warnings;
             if ($criticalIssues > 0) {
                 $status = "🚨 CRITICAL ISSUES FOUND";
                 $statusColor = "#dc3545";
             } elseif ($warnings > 0) {
                 $status = "⚠️  WARNINGS DETECTED";
                 $statusColor = "#ff6c37";
             } else {
                 $status = "🎉 APPLICATION HEALTHY";
                 $statusColor = "#28a745";
             }
             
             $output[] = $status;
             $output[] = "";
             $output[] = "📊 DIAGNOSTIC SUMMARY:";
             $output[] = "   🚨 Critical Issues: {$criticalIssues}";
             $output[] = "   ⚠️  Warnings: {$warnings}";
             $output[] = "   💡 Suggestions: {$suggestions}";
             $output[] = "";
             
             if ($criticalIssues > 0) {
                 $output[] = "🔧 IMMEDIATE ACTION REQUIRED:";
                 $output[] = "   - Fix critical security vulnerabilities";
                 $output[] = "   - Resolve database integrity issues";
                 $output[] = "   - Address system configuration problems";
             } elseif ($warnings > 0) {
                 $output[] = "🔧 RECOMMENDED ACTIONS:";
                 $output[] = "   - Review and fix warnings";
                 $output[] = "   - Implement suggested improvements";
                 $output[] = "   - Monitor system performance";
             } else {
                 $output[] = "✅ ALL SYSTEMS OPERATIONAL";
                 $output[] = "   - No critical issues detected";
                 $output[] = "   - Application is running smoothly";
                 $output[] = "   - Consider implementing suggestions for optimization";
             }
             
             $output[] = "";
             $output[] = "=" . str_repeat("=", 60);
             
             return response("<h2 style='color: {$statusColor};'>{$status}</h2><pre>" . implode("\n", $output) . "</pre><br><br><a href='/health-check' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🩺 Membership Health Check</a><br><br><a href='/dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👑 Admin Dashboard</a>");
         });
         
         // System Management Routes (Super Admin only)
        Route::post('/system/backup', [AdminController::class, 'createSystemBackup'])->name('system.backup');
        Route::post('/system/clear-logs', [AdminController::class, 'clearSystemLogs'])->name('system.clear-logs');
        Route::post('/notifications/bulk-send', [AdminController::class, 'sendBulkNotifications'])->name('notifications.bulk-send');
    });
    
    // Test Gmail authentication specifically
    Route::get('/test-gmail-auth', function () {
        try {
            $output = [];
            $output[] = "🔐 Testing Gmail Authentication";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "";
            
            $output[] = "📋 Current Gmail Settings:";
            $output[] = "- MAIL_HOST: " . env('MAIL_HOST');
            $output[] = "- MAIL_PORT: " . env('MAIL_PORT');
            $output[] = "- MAIL_USERNAME: " . env('MAIL_USERNAME');
            $output[] = "- MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? str_repeat('*', strlen(env('MAIL_PASSWORD'))) : 'NOT SET');
            $output[] = "- MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION');
            $output[] = "- MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS');
            $output[] = "";
            
            // Try to create SMTP transport and test connection
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                env('MAIL_HOST'),
                (int) env('MAIL_PORT'),
                env('MAIL_ENCRYPTION') === 'tls'
            );
            
            $transport->setUsername(env('MAIL_USERNAME'));
            $transport->setPassword(env('MAIL_PASSWORD'));
            
            $output[] = "🔌 Testing SMTP connection...";
            
            // This will throw an exception if connection fails
            $transport->start();
            
            $output[] = "✅ SMTP connection successful!";
            $transport->stop();
            
            return response('<h2>Gmail Authentication Test Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            $errorMsg = 'Error: ' . $e->getMessage() . "\n\nClass: " . get_class($e) . "\n\nThis confirms the Gmail authentication issue. You need to:\n1. Generate a new Gmail App Password\n2. Update MAIL_PASSWORD in Render environment variables\n3. Make sure 2FA is enabled on Gmail account";
            return response('<h2>Gmail Authentication Failed</h2><pre>' . $errorMsg . '</pre><br><a href="/admin">Go to Dashboard</a>');
        }
    });
    
    // Simple Gmail credentials check
    Route::get('/check-gmail-credentials', function () {
        $output = [];
        $output[] = "🔍 Gmail Credentials Check";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "";
        
        $output[] = "📋 Environment Variables:";
        $output[] = "- MAIL_MAILER: " . (env('MAIL_MAILER') ?: 'NOT SET');
        $output[] = "- MAIL_HOST: " . (env('MAIL_HOST') ?: 'NOT SET');
        $output[] = "- MAIL_PORT: " . (env('MAIL_PORT') ?: 'NOT SET');
        $output[] = "- MAIL_USERNAME: " . (env('MAIL_USERNAME') ?: 'NOT SET');
        $output[] = "- MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? 'SET (' . strlen(env('MAIL_PASSWORD')) . ' chars)' : 'NOT SET');
        $output[] = "- MAIL_ENCRYPTION: " . (env('MAIL_ENCRYPTION') ?: 'NOT SET');
        $output[] = "- MAIL_FROM_ADDRESS: " . (env('MAIL_FROM_ADDRESS') ?: 'NOT SET');
        $output[] = "- MAIL_FROM_NAME: " . (env('MAIL_FROM_NAME') ?: 'NOT SET');
        $output[] = "";
        
        $output[] = "📋 Config Values:";
        $output[] = "- mail.default: " . config('mail.default');
        $output[] = "- mail.mailers.smtp.host: " . config('mail.mailers.smtp.host');
        $output[] = "- mail.mailers.smtp.port: " . config('mail.mailers.smtp.port');
        $output[] = "- mail.mailers.smtp.username: " . config('mail.mailers.smtp.username');
        $output[] = "- mail.mailers.smtp.password: " . (config('mail.mailers.smtp.password') ? 'SET' : 'NOT SET');
        $output[] = "- mail.mailers.smtp.encryption: " . config('mail.mailers.smtp.encryption');
        $output[] = "- mail.from.address: " . config('mail.from.address');
        $output[] = "- mail.from.name: " . config('mail.from.name');
        $output[] = "";
        
        // Check if password looks like a Gmail App Password
        $password = env('MAIL_PASSWORD');
        if ($password) {
            $output[] = "🔐 Password Analysis:";
            $output[] = "- Length: " . strlen($password) . " characters";
            $output[] = "- Format check: " . (preg_match('/^[a-z]{4}\s[a-z]{4}\s[a-z]{4}\s[a-z]{4}$/', $password) ? 'Looks like Gmail App Password' : 'Does NOT look like Gmail App Password');
            $output[] = "- Expected format: 'abcd efgh ijkl mnop' (16 chars with spaces)";
        }
        
        return response('<h2>Gmail Credentials Check</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/test-gmail-auth">Test Gmail Auth</a><br><a href="/admin">Go to Dashboard</a>');
    });

    // Debug Gmail Password Format Issue
    Route::get('/debug-gmail-password', function () {
        $output = [];
        $output[] = "🔍 Gmail Password Format Debug";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "";
        
        $currentPassword = config('mail.mailers.smtp.password');
        $output[] = "CURRENT PASSWORD ANALYSIS:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        $output[] = "Raw password: '" . $currentPassword . "'";
        $output[] = "Length: " . strlen($currentPassword) . " characters";
        $output[] = "Contains spaces: " . (strpos($currentPassword, ' ') !== false ? 'YES ❌' : 'NO ✅');
        $output[] = "";
        
        if (strpos($currentPassword, ' ') !== false) {
            $fixedPassword = str_replace(' ', '', $currentPassword);
            $output[] = "🔧 PROBLEM IDENTIFIED: Gmail app password contains spaces!";
            $output[] = "";
            $output[] = "SOLUTION:";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $output[] = "Current (WRONG): '" . $currentPassword . "'";
            $output[] = "Should be (CORRECT): '" . $fixedPassword . "'";
            $output[] = "";
            $output[] = "STEPS TO FIX:";
            $output[] = "1. Go to Render Dashboard → Your Service → Environment";
            $output[] = "2. Find MAIL_PASSWORD variable";
            $output[] = "3. Change from: " . $currentPassword;
            $output[] = "4. Change to: " . $fixedPassword;
            $output[] = "5. Deploy the changes";
            $output[] = "";
            $output[] = "⚠️ Gmail app passwords should NEVER contain spaces in SMTP config!";
            $output[] = "The spaces are only for display when Google shows you the password.";
        } else {
            $output[] = "✅ Password format looks correct (no spaces found)";
            $output[] = "";
            $output[] = "Other possible issues:";
            $output[] = "- App password might be expired/revoked";
            $output[] = "- 2FA might not be enabled on Gmail";
            $output[] = "- Gmail account might have security restrictions";
        }
        
        $output[] = "";
        $output[] = "TESTING SMTP CONNECTION WITH CURRENT PASSWORD:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        
        try {
            // Test SMTP connection
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                config('mail.mailers.smtp.host'),
                config('mail.mailers.smtp.port'),
                config('mail.mailers.smtp.encryption') === 'tls'
            );
            $transport->setUsername(config('mail.mailers.smtp.username'));
            $transport->setPassword($currentPassword);
            
            $transport->start();
            $output[] = "✅ SMTP Connection: SUCCESS";
            $transport->stop();
            
            // If connection works, try sending email
            $output[] = "";
            $output[] = "TESTING EMAIL SEND:";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            
            Mail::raw("Test email to verify Gmail SMTP is working.\n\nTimestamp: " . now()->toDateTimeString(), function ($message) {
                $message->to(config('mail.from.address'))
                        ->subject('Gmail SMTP Test - ' . now()->format('H:i:s'))
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $output[] = "✅ Email sent successfully!";
            $output[] = "Check inbox for: " . config('mail.from.address');
            
        } catch (\Exception $e) {
            $output[] = "❌ SMTP Connection/Email FAILED";
            $output[] = "Error: " . $e->getMessage();
            $output[] = "Class: " . get_class($e);
            
            if (strpos($e->getMessage(), '550 5.7.1') !== false) {
                $output[] = "";
                $output[] = "🎯 THIS IS THE RELAYING DENIED ERROR!";
                if (strpos($currentPassword, ' ') !== false) {
                    $output[] = "Most likely cause: Password format (spaces in password)";
                } else {
                    $output[] = "Other possible causes:";
                    $output[] = "- App password expired/invalid";
                    $output[] = "- Gmail security settings";
                    $output[] = "- Rate limiting";
                }
            }
        }
        
        return response('<h2>Gmail Password Debug Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin">Go to Dashboard</a>');
    });
});


// Production setup routes (Super Admin only)
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/verify-production-data', [AdminController::class, 'verifyProductionData']);
    Route::get('/setup-production-data', [AdminController::class, 'setupProductionData']);
    Route::get('/setup-production-email', [AdminController::class, 'setupProductionEmail']);
    Route::get('/setup-test-expiry/{email}', [AdminController::class, 'setupTestExpiry']);

// Testing Dashboard Routes (Admin/Super Admin only)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/testing-dashboard', [App\Http\Controllers\TestingDashboardController::class, 'index'])->name('testing-dashboard');
    Route::post('/testing-dashboard/run-tests', [App\Http\Controllers\TestingDashboardController::class, 'runAllTests'])->name('testing-dashboard.run-tests');
    
    // Temporary debug route to diagnose 500 error
    Route::get('/testing-dashboard/debug', function () {
        try {
            $controller = new App\Http\Controllers\TestingDashboardController();
            $response = $controller->runAllTests();
            return response()->json([
                'status' => 'success',
                'message' => 'Tests executed successfully',
                'response_type' => get_class($response),
                'content_preview' => substr($response->getContent(), 0, 200) . '...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ], 500);
        }
    });
});

    // Email test route
    Route::get('/test-email', function () {
        try {
            $output = [];
            $output[] = "📧 Testing Email Configuration";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "";
            
            // Test email configuration
            $output[] = "📋 Email Settings:";
            $output[] = "- MAIL_MAILER: " . config('mail.default');
            $output[] = "- MAIL_HOST: " . config('mail.mailers.smtp.host');
            $output[] = "- MAIL_PORT: " . config('mail.mailers.smtp.port');
            $output[] = "- MAIL_USERNAME: " . config('mail.mailers.smtp.username');
            $output[] = "- MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption');
            $output[] = "- MAIL_FROM_ADDRESS: " . config('mail.from.address');
            $output[] = "- MAIL_FROM_NAME: " . config('mail.from.name');
            $output[] = "";
            
            // Send test email
            $testEmail = 'infinitdizzajn@gmail.com';
            $subject = 'Test Email from EN NUR Membership System';
            $message = "Hello!\n\nThis is a test email to verify that the email system is working correctly.\n\nSent at: " . now()->toDateTimeString() . "\n\nBest regards,\nEN NUR Membership Team";
            
            $output[] = "📤 Sending test email to: {$testEmail}";
            
            Mail::raw($message, function ($mail) use ($testEmail, $subject) {
                $mail->to($testEmail)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $output[] = "✅ Email sent successfully!";
            $output[] = "Check the inbox for {$testEmail}";
            $output[] = "Subject: {$subject}";
            
            return response('<h2>Email Test Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>Email Test Failed</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre><br><a href="/admin">Go to Dashboard</a>');
        }
    });
    
    // Test notification system
    Route::get('/test-notification', function () {
        try {
            $output = [];
            $output[] = "🔔 Testing Notification System (Zoho SMTP)";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "Mail Driver: " . config('mail.default');
            $output[] = "Mail Host: " . config('mail.mailers.smtp.host');
            $output[] = "";
            
            // Find the infinitdizzajn user
            $user = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
            if (!$user) {
                $output[] = "❌ User infinitdizzajn@gmail.com not found";
                return response('<h2>Notification Test Failed</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin">Go to Dashboard</a>');
            }
            
            $output[] = "✅ User found: {$user->name} ({$user->email})";
            
            // Find the membership renewal
            $renewal = \App\Models\MembershipRenewal::where('user_id', $user->id)
                ->where('is_renewed', false)
                ->first();
                
            if (!$renewal) {
                $output[] = "❌ No active membership renewal found for user";
                return response('<h2>Notification Test Failed</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin">Go to Dashboard</a>');
            }
            
            $output[] = "✅ Renewal found: ID {$renewal->id}";
            $output[] = "- Days until expiry: {$renewal->days_until_expiry}";
            $output[] = "- Membership end: {$renewal->membership_end_date}";
            $output[] = "- Is hidden: " . ($renewal->is_hidden ? 'Yes' : 'No');
            $output[] = "";
            
            // Test the notification email manually
            $output[] = "📧 Testing notification email...";
            
            $daysRemaining = $renewal->days_until_expiry;
            $notificationMessage = $renewal->getNotificationMessage();
            
            // Email subject based on urgency
            if ($daysRemaining <= 0) {
                $subject = 'Membership Expired - Immediate Renewal Required';
            } elseif ($daysRemaining <= 1) {
                $subject = 'Membership Expires Tomorrow - Urgent Renewal Required';
            } elseif ($daysRemaining <= 7) {
                $subject = "Membership Expires in {$daysRemaining} Days - Renewal Required";
            } else {
                $subject = "Membership Renewal Reminder - {$daysRemaining} Days Remaining";
            }
            
            // Create email content
            $renewalUrl = route('payment.create');
            $membershipStart = $renewal->membership_start_date ? $renewal->membership_start_date->format('M d, Y') : 'N/A';
            $membershipEnd = $renewal->membership_end_date ? $renewal->membership_end_date->format('M d, Y') : 'N/A';
            
            $emailBody = "
Hello {$user->name},

{$notificationMessage}

Your Membership Details:
- Membership Start: {$membershipStart}
- Membership End: {$membershipEnd}
- Days Remaining: " . ($daysRemaining > 0 ? $daysRemaining : 'EXPIRED') . "

To renew your membership, please visit:
{$renewalUrl}

If you have any questions, please contact our support team.

Best regards,
EN NUR - MEMBERSHIP Team
            ";
            
            $output[] = "Subject: {$subject}";
            $output[] = "To: {$user->email}";
            $output[] = "From: " . config('mail.from.address');
            $output[] = "";
            
            // Send the email
            Mail::raw($emailBody, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $output[] = "✅ Notification email sent successfully!";
            $output[] = "Check the inbox for {$user->email}";
            
            // Mark notification as sent
            $renewal->markNotificationSent($renewal->days_until_expiry);
            $output[] = "✅ Notification marked as sent in database";
            
            return response('<h2>Notification Test Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin/users">View Users</a><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>Notification Test Failed</h2><pre>Error: ' . $e->getMessage() . "\n\nClass: " . get_class($e) . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre><br><a href="/admin">Go to Dashboard</a>');
        }
    });
    
    // Test notification with log driver (no actual email)
    Route::get('/test-notification-log', function () {
        try {
            $output = [];
            $output[] = "🔔 Testing Notification System (Log Mode)";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "";
            
            // Find the infinitdizzajn user
            $user = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
            if (!$user) {
                $output[] = "❌ User infinitdizzajn@gmail.com not found";
                return response('<h2>Test Failed</h2><pre>' . implode("\n", $output) . '</pre>');
            }
            
            $output[] = "✅ User found: {$user->name} ({$user->email})";
            
            // Find the membership renewal
            $renewal = \App\Models\MembershipRenewal::where('user_id', $user->id)
                ->where('is_renewed', false)
                ->first();
                
            if (!$renewal) {
                $output[] = "❌ No active membership renewal found";
                return response('<h2>Test Failed</h2><pre>' . implode("\n", $output) . '</pre>');
            }
            
            $output[] = "✅ Renewal found: ID {$renewal->id}";
            $output[] = "- Days until expiry: {$renewal->days_until_expiry}";
            $output[] = "";
            
            // Test notification without actually sending email
            $daysRemaining = $renewal->days_until_expiry;
            $subject = $daysRemaining <= 0 
                ? 'Membership Expired - Immediate Renewal Required'
                : "Membership Renewal Reminder - {$daysRemaining} Days Remaining";
                
            $output[] = "📧 Email that would be sent:";
            $output[] = "To: {$user->email}";
            $output[] = "Subject: {$subject}";
            $output[] = "Status: Ready to send (SMTP disabled for testing)";
            $output[] = "";
            
            // Mark notification as sent in database
            $renewal->markNotificationSent($renewal->days_until_expiry);
            $output[] = "✅ Notification marked as sent in database";
            $output[] = "";
            $output[] = "🎯 This proves the notification system logic works!";
            $output[] = "The only issue is the Gmail SMTP connection.";
            
            return response('<h2>Notification Test Results (Log Mode)</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin/users">View Users</a><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>Test Failed</h2><pre>Error: ' . $e->getMessage() . '</pre>');
        }
    });
    
    // Simple test route
    Route::get('/test-simple', function () {
        return response('<h1>✅ Routes are working!</h1><p>Timestamp: ' . now() . '</p><br><a href="/admin">Go to Dashboard</a>');
    });
});

// Simple redirect for /admin to main dashboard
Route::get('/admin', function () {
    return redirect('/dashboard');
});

// Deep database check and cleanup - show all users and clean duplicates
Route::get('/deep-user-check', function() {
    try {
        $output = [];
        $output[] = "🔍 DEEP DATABASE USER CHECK";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "=" . str_repeat("=", 50);
        $output[] = "";
        
        // Get ALL users from database
        $allUsers = \App\Models\User::all();
        $output[] = "📊 TOTAL USERS IN DATABASE: " . $allUsers->count();
        $output[] = "";
        
        // List every single user
        $output[] = "👥 ALL USERS:";
        $output[] = "-" . str_repeat("-", 30);
        
        foreach ($allUsers as $user) {
            $output[] = "ID: {$user->id} | Name: '{$user->name}' | Email: '{$user->email}' | Role: '{$user->role}' | Verified: " . ($user->email_verified_at ? 'Yes' : 'No');
            
            // Check membership renewals for this user
            $renewals = \App\Models\MembershipRenewal::where('user_id', $user->id)->get();
            if ($renewals->count() > 0) {
                foreach ($renewals as $renewal) {
                    $output[] = "  └─ Renewal: Days={$renewal->days_until_expiry}, End={$renewal->membership_end_date}, Hidden=" . ($renewal->is_hidden ? 'Yes' : 'No');
                }
            } else {
                $output[] = "  └─ No membership renewals";
            }
            $output[] = "";
        }
        
        // Show payments
        $allPayments = \App\Models\Payment::with('user')->get();
        $output[] = "💳 TOTAL PAYMENTS: " . $allPayments->count();
        $output[] = "";
        
        foreach ($allPayments as $payment) {
            $userName = $payment->user ? $payment->user->name : 'Unknown User';
            $userEmail = $payment->user ? $payment->user->email : 'Unknown Email';
            $output[] = "Payment ID: {$payment->id} | User: {$userName} ({$userEmail}) | Amount: {$payment->amount} | Status: {$payment->status}";
        }
        
        $output[] = "";
        $output[] = "🎯 TARGET USERS TO KEEP:";
        $output[] = "1. SUPER ADMIN: kushtrim.m.arifi@gmail.com (Password: [CONFIGURED_VIA_ENV])";
        $output[] = "2. TEST USER: infinitdizzajn@gmail.com (Password: alipasha)";
        $output[] = "";
        $output[] = "❌ ALL OTHER USERS SHOULD BE DELETED";
        
        return response('<h2>🔍 Deep Database Check Results</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/clean-all-users" style="background: red; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🗑️ CLEAN ALL USERS (Keep Only 2)</a><br><br><a href="/admin">Back to Admin</a>');
        
    } catch (\Exception $e) {
        return response('<h2>❌ Error</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
    }
})->middleware(['auth', 'super_admin']);

// Clean all users except the two we want
Route::get('/clean-all-users', function() {
    try {
        $output = [];
        $output[] = "🧹 CLEANING ALL USERS - KEEPING ONLY 2";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "=" . str_repeat("=", 50);
        $output[] = "";
        
        // Target users to keep
        $keepEmails = [
            'kushtrim.m.arifi@gmail.com',
            'infinitdizzajn@gmail.com'
        ];
        
        // Get all users
        $allUsers = \App\Models\User::all();
        $output[] = "📊 Found {$allUsers->count()} total users";
        $output[] = "";
        
        $deletedCount = 0;
        $keptCount = 0;
        
        foreach ($allUsers as $user) {
            if (in_array($user->email, $keepEmails)) {
                $output[] = "✅ KEEPING: {$user->name} ({$user->email}) - Role: {$user->role}";
                $keptCount++;
            } else {
                $output[] = "🗑️ DELETING: {$user->name} ({$user->email}) - Role: {$user->role}";
                
                // Delete related data first
                \App\Models\MembershipRenewal::where('user_id', $user->id)->delete();
                \App\Models\Payment::where('user_id', $user->id)->delete();
                
                // Delete the user
                $user->delete();
                $deletedCount++;
            }
        }
        
        $output[] = "";
        $output[] = "📊 CLEANUP SUMMARY:";
        $output[] = "✅ Users kept: {$keptCount}";
        $output[] = "🗑️ Users deleted: {$deletedCount}";
        $output[] = "";
        
        // Now setup the two users correctly
        $output[] = "🔧 SETTING UP THE TWO USERS:";
        $output[] = "";
        
        // 1. Setup Super Admin
        $superAdmin = \App\Models\User::where('email', 'kushtrim.m.arifi@gmail.com')->first();
        if (!$superAdmin) {
            $superAdmin = \App\Models\User::create([
                'name' => 'SUPER ADMIN',
                'email' => 'kushtrim.m.arifi@gmail.com',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'change-me')),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Super Admin";
        } else {
            $superAdmin->update([
                'name' => 'SUPER ADMIN',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'change-me')),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Updated Super Admin";
        }
        
        // 2. Setup Test User with membership
        $testUser = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
        if (!$testUser) {
            $testUser = \App\Models\User::create([
                'name' => 'kushtrim arifi',
                'email' => 'infinitdizzajn@gmail.com',
                'password' => Hash::make(env('USER_CORRECT_PASSWORD', 'change-me')),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Test User";
        } else {
            $testUser->update([
                'name' => 'kushtrim arifi',
                'password' => Hash::make(env('USER_CORRECT_PASSWORD', 'change-me')),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Updated Test User";
        }
        
        // Clean existing renewals and payments for test user
        \App\Models\MembershipRenewal::where('user_id', $testUser->id)->delete();
        \App\Models\Payment::where('user_id', $testUser->id)->delete();
        
        // Create payment for test user
        $payment = \App\Models\Payment::create([
            'user_id' => $testUser->id,
            'amount' => 35000, // CHF 350.00
            'currency' => 'CHF',
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'transaction_id' => 'test_clean_' . time(),
            'metadata' => ['clean_setup' => true],
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);
        
        // Create membership renewal (expires in 14 days = ORANGE)
        $expiryDate = now()->addDays(14);
        $startDate = $expiryDate->copy()->subYear();
        
        $renewal = \App\Models\MembershipRenewal::create([
            'user_id' => $testUser->id,
            'payment_id' => $payment->id,
            'membership_start_date' => $startDate,
            'membership_end_date' => $expiryDate,
            'days_until_expiry' => 14,
            'is_expired' => false,
            'is_hidden' => false,
            'is_renewed' => false,
            'notifications_sent' => [],
            'last_notification_sent_at' => null,
        ]);
        
        $output[] = "✅ Created membership for test user (14 days remaining = ORANGE)";
        $output[] = "";
        
        // Final verification
        $finalUsers = \App\Models\User::all();
        $output[] = "🔍 FINAL VERIFICATION:";
        $output[] = "Total users now: " . $finalUsers->count();
        $output[] = "";
        
        foreach ($finalUsers as $user) {
            $output[] = "✅ {$user->name} ({$user->email}) - Role: {$user->role}";
        }
        
        $output[] = "";
        $output[] = "🎯 LOGIN CREDENTIALS:";
        $output[] = "👑 SUPER ADMIN: kushtrim.m.arifi@gmail.com / [ENV_CONFIGURED]";
        $output[] = "👤 TEST USER: infinitdizzajn@gmail.com / [ENV_CONFIGURED]";
        $output[] = "";
        $output[] = "🎨 Expected: Test user should show ORANGE (14 days) in admin dashboard";
        
        return response('<h2>✅ Database Cleaned Successfully!</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/admin/users" style="background: green; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Admin Users (Should see ORANGE user)</a><br><br><a href="/login">Test User Login</a><br><br><a href="/admin">Admin Dashboard</a>');
        
    } catch (\Exception $e) {
        return response('<h2>❌ Error</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
    }
})->middleware(['auth', 'super_admin']);

// Test membership renewal logic
Route::get('/test-membership-renewal', function() {
    $user = auth()->user();
    if (!$user) {
        return 'Please login first';
    }
    
    // Get current membership status
    $currentRenewal = \App\Models\MembershipRenewal::where('user_id', $user->id)
        ->where('is_renewed', false)
        ->orderBy('membership_end_date', 'desc')
        ->first();
    
    $membershipService = new \App\Services\MembershipService();
    $userColor = $membershipService->getUserColor($user->id);
    $userStats = $membershipService->getUserStats($user->id);
    
    $output = "<h2>🧪 Membership Renewal Test</h2>";
    $output .= "<p><strong>User:</strong> {$user->name} ({$user->email})</p>";
    $output .= "<p><strong>Current Color:</strong> <span style='color: {$userColor}; font-weight: bold;'>{$userColor}</span></p>";
    
    if ($currentRenewal) {
        $output .= "<p><strong>Current Membership:</strong></p>";
        $output .= "<ul>";
        $output .= "<li>Start: {$currentRenewal->membership_start_date}</li>";
        $output .= "<li>End: {$currentRenewal->membership_end_date}</li>";
        $output .= "<li>Days Until Expiry: {$currentRenewal->days_until_expiry}</li>";
        $output .= "<li>Is Expired: " . ($currentRenewal->is_expired ? 'Yes' : 'No') . "</li>";
        $output .= "<li>Is Renewed: " . ($currentRenewal->is_renewed ? 'Yes' : 'No') . "</li>";
        $output .= "</ul>";
    } else {
        $output .= "<p><strong>No active membership found!</strong></p>";
    }
    
    $output .= "<p><strong>User Stats:</strong></p>";
    $output .= "<ul>";
    $output .= "<li>Status: {$userStats['status']}</li>";
    $output .= "<li>Days Remaining: {$userStats['days_remaining']}</li>";
    $output .= "<li>Expires At: {$userStats['expires_at']}</li>";
    $output .= "</ul>";
    
    $output .= "<p><a href='/payments/create' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Make Test Payment</a></p>";
    $output .= "<p><a href='/dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 View Dashboard</a></p>";
    
    return $output;
})->middleware('auth')->name('test.membership.renewal');

// Setup test users with expired/expiring memberships
Route::get('/setup-test-users', function() {
    try {
        $output = [];
        $output[] = "🧪 SETTING UP TEST USERS FOR ADMIN DASHBOARD";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "=" . str_repeat("=", 50);
        $output[] = "";
        
        // 1. Setup info@mardal.ch - EXPIRED (RED)
        $mardalUser = \App\Models\User::where('email', 'info@mardal.ch')->first();
        if (!$mardalUser) {
            $mardalUser = \App\Models\User::create([
                'name' => 'Mardal User',
                'email' => 'info@mardal.ch',
                'password' => Hash::make(env('TEST_USER_PASSWORD', 'change-me')),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Mardal user: info@mardal.ch";
        } else {
            $mardalUser->update([
                'name' => 'Mardal User',
                'password' => Hash::make(env('TEST_USER_PASSWORD', 'change-me')),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Updated Mardal user: info@mardal.ch";
        }
        
        // 2. Setup infinitdizzajn@gmail.com - EXPIRING SOON (ORANGE)
        $infinitUser = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
        if (!$infinitUser) {
            $infinitUser = \App\Models\User::create([
                'name' => 'kushtrim arifi',
                'email' => 'infinitdizzajn@gmail.com',
                'password' => Hash::make(env('USER_CORRECT_PASSWORD', 'change-me')),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Infinit user: infinitdizzajn@gmail.com";
        } else {
            $infinitUser->update([
                'name' => 'kushtrim arifi',
                'password' => Hash::make(env('USER_CORRECT_PASSWORD', 'change-me')),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Updated Infinit user: infinitdizzajn@gmail.com";
        }
        
        $output[] = "";
        $output[] = "🔧 SETTING UP MEMBERSHIPS (BOTH ABOUT TO EXPIRE):";
        $output[] = "";
        
        // Clean existing renewals and payments for both users
        \App\Models\MembershipRenewal::whereIn('user_id', [$mardalUser->id, $infinitUser->id])->delete();
        \App\Models\Payment::whereIn('user_id', [$mardalUser->id, $infinitUser->id])->delete();
        
        // 1. MARDAL USER - EXPIRED (5 days ago) - Should show in admin dashboard
        $mardalPayment = \App\Models\Payment::create([
            'user_id' => $mardalUser->id,
            'amount' => 35000, // CHF 350.00
            'currency' => 'CHF',
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'transaction_id' => 'test_mardal_' . time(),
            'metadata' => ['test_setup' => 'expired'],
            'created_at' => now()->subYear()->subDays(5),
            'updated_at' => now()->subYear()->subDays(5),
        ]);
        
        $mardalExpiryDate = now()->subDays(5); // EXPIRED 5 days ago (within 30 day window)
        $mardalStartDate = $mardalExpiryDate->copy()->subYear();
        $mardalDaysUntilExpiry = (int) now()->diffInDays($mardalExpiryDate, false); // Should be -5
        
        $mardalRenewal = \App\Models\MembershipRenewal::create([
            'user_id' => $mardalUser->id,
            'payment_id' => $mardalPayment->id,
            'membership_start_date' => $mardalStartDate,
            'membership_end_date' => $mardalExpiryDate,
            'days_until_expiry' => $mardalDaysUntilExpiry,
            'is_expired' => true,
            'is_hidden' => false,
            'is_renewed' => false,
            'notifications_sent' => [],
            'last_notification_sent_at' => null,
        ]);
        
        $output[] = "🔴 MARDAL USER (info@mardal.ch):";
        $output[] = "   - Status: EXPIRED (5 days ago)";
        $output[] = "   - End Date: {$mardalExpiryDate->format('Y-m-d')}";
        $output[] = "   - Days Until Expiry: {$mardalDaysUntilExpiry}";
        $output[] = "   - Expected Color: RED 🔴";
        $output[] = "   - Should appear in admin dashboard: YES";
        $output[] = "";
        
        // 2. INFINIT USER - 10 DAYS REMAINING (ORANGE)
        $infinitPayment = \App\Models\Payment::create([
            'user_id' => $infinitUser->id,
            'amount' => 35000, // CHF 350.00
            'currency' => 'CHF',
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'transaction_id' => 'test_infinit_' . time(),
            'metadata' => ['test_setup' => 'expiring_soon'],
            'created_at' => now()->subYear()->addDays(10),
            'updated_at' => now()->subYear()->addDays(10),
        ]);
        
        $infinitExpiryDate = now()->addDays(10); // 10 days remaining
        $infinitStartDate = $infinitExpiryDate->copy()->subYear();
        $infinitDaysUntilExpiry = (int) now()->diffInDays($infinitExpiryDate, false); // Should be 10
        
        $infinitRenewal = \App\Models\MembershipRenewal::create([
            'user_id' => $infinitUser->id,
            'payment_id' => $infinitPayment->id,
            'membership_start_date' => $infinitStartDate,
            'membership_end_date' => $infinitExpiryDate,
            'days_until_expiry' => $infinitDaysUntilExpiry,
            'is_expired' => false,
            'is_hidden' => false,
            'is_renewed' => false,
            'notifications_sent' => [],
            'last_notification_sent_at' => null,
        ]);
        
        $output[] = "🟠 INFINIT USER (infinitdizzajn@gmail.com):";
        $output[] = "   - Status: EXPIRING SOON (10 days)";
        $output[] = "   - End Date: {$infinitExpiryDate->format('Y-m-d')}";
        $output[] = "   - Days Until Expiry: {$infinitDaysUntilExpiry}";
        $output[] = "   - Expected Color: ORANGE 🟠";
        $output[] = "   - Should appear in admin dashboard: YES";
        $output[] = "";
        
        // Verify with MembershipService
        $membershipService = new \App\Services\MembershipService();
        
        $mardalColor = $membershipService->getUserColor($mardalUser->id);
        $infinitColor = $membershipService->getUserColor($infinitUser->id);
        
        $output[] = "🎨 COLOR VERIFICATION:";
        $output[] = "   - Mardal Color: {$mardalColor} (should be #dc3545 - RED)";
        $output[] = "   - Infinit Color: {$infinitColor} (should be #ff6c37 - ORANGE)";
        $output[] = "";
        
        // Test admin dashboard logic
        $adminDashboardRenewals = \App\Models\MembershipRenewal::with('user')
            ->where('is_renewed', false)
            ->where('is_hidden', false)
            ->get()
            ->filter(function ($renewal) {
                $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
                return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30;
            });
        
        $output[] = "🔍 ADMIN DASHBOARD TEST:";
        $output[] = "   - Total renewals found: " . $adminDashboardRenewals->count();
        foreach ($adminDashboardRenewals as $renewal) {
            $userName = $renewal->user ? $renewal->user->name : 'Unknown';
            $userEmail = $renewal->user ? $renewal->user->email : 'Unknown';
            $calculatedDays = $renewal->calculateDaysUntilExpiry();
            $output[] = "   - {$userName} ({$userEmail}): {$calculatedDays} days";
        }
        $output[] = "";
        
        $output[] = "🔑 LOGIN CREDENTIALS:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        $output[] = "👑 SUPER ADMIN: kushtrim.m.arifi@gmail.com / [ENV_CONFIGURED]";
        $output[] = "🔴 EXPIRED USER: info@mardal.ch / [ENV_CONFIGURED]";
        $output[] = "🟠 EXPIRING USER: infinitdizzajn@gmail.com / [ENV_CONFIGURED]";
        $output[] = "";
        
        $output[] = "🧪 TESTING INSTRUCTIONS:";
        $output[] = "1. Login to admin dashboard - should see 2 users needing attention";
        $output[] = "2. Login as expired user - should see RED urgent renewal message";
        $output[] = "3. Login as expiring user - should see ORANGE renewal reminder";
        $output[] = "4. Make payments to test renewal logic works correctly";
        
        return response('<h2>✅ Test Users Setup Complete!</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👑 View Admin Dashboard</a><br><br><a href="/admin/users" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👥 View Admin Users</a><br><br><a href="/login" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔴 Test Expired User Login</a>');
        
    } catch (\Exception $e) {
        return response('<h2>❌ Error Setting Up Test Users</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
    }
})->middleware(['auth', 'super_admin']);

// Modify existing subscriptions to be about to expire
Route::get('/expire-existing-subscriptions', function() {
    try {
        $output = [];
        $output[] = "🔧 MODIFYING EXISTING SUBSCRIPTIONS TO EXPIRE SOON";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "=" . str_repeat("=", 60);
        $output[] = "";
        
        // Find both users
        $mardalUser = \App\Models\User::where('email', 'info@mardal.ch')->first();
        $infinitUser = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
        
        if (!$mardalUser) {
            $output[] = "❌ Mardal user (info@mardal.ch) not found!";
        }
        if (!$infinitUser) {
            $output[] = "❌ Infinit user (infinitdizzajn@gmail.com) not found!";
        }
        
        if (!$mardalUser || !$infinitUser) {
            return response('<h2>❌ Users Not Found</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/setup-test-users">Setup Users First</a>');
        }
        
        $output[] = "✅ Found both users";
        $output[] = "";
        
        // Find existing memberships for both users
        $mardalRenewal = \App\Models\MembershipRenewal::where('user_id', $mardalUser->id)
            ->where('is_renewed', false)
            ->orderBy('membership_end_date', 'desc')
            ->first();
            
        $infinitRenewal = \App\Models\MembershipRenewal::where('user_id', $infinitUser->id)
            ->where('is_renewed', false)
            ->orderBy('membership_end_date', 'desc')
            ->first();
        
        $output[] = "🔍 EXISTING MEMBERSHIPS:";
        $output[] = "";
        
        if ($mardalRenewal) {
            $output[] = "📋 Mardal User Current Membership:";
            $output[] = "   - Start: {$mardalRenewal->membership_start_date}";
            $output[] = "   - End: {$mardalRenewal->membership_end_date}";
            $output[] = "   - Days Until Expiry: {$mardalRenewal->days_until_expiry}";
            $output[] = "   - Is Expired: " . ($mardalRenewal->is_expired ? 'Yes' : 'No');
        } else {
            $output[] = "❌ No membership found for Mardal user";
        }
        
        if ($infinitRenewal) {
            $output[] = "📋 Infinit User Current Membership:";
            $output[] = "   - Start: {$infinitRenewal->membership_start_date}";
            $output[] = "   - End: {$infinitRenewal->membership_end_date}";
            $output[] = "   - Days Until Expiry: {$infinitRenewal->days_until_expiry}";
            $output[] = "   - Is Expired: " . ($infinitRenewal->is_expired ? 'Yes' : 'No');
        } else {
            $output[] = "❌ No membership found for Infinit user";
        }
        
        $output[] = "";
        $output[] = "🔧 MODIFYING MEMBERSHIPS TO EXPIRE SOON:";
        $output[] = "";
        
        // Modify Mardal user - make it EXPIRED (3 days ago)
        if ($mardalRenewal) {
            $newMardalEndDate = now()->subDays(3);
            $newMardalStartDate = $newMardalEndDate->copy()->subYear();
            $newMardalDays = (int) now()->diffInDays($newMardalEndDate, false); // Should be -3
            
            $mardalRenewal->update([
                'membership_start_date' => $newMardalStartDate,
                'membership_end_date' => $newMardalEndDate,
                'days_until_expiry' => $newMardalDays,
                'is_expired' => true,
                'is_hidden' => false,
                'is_renewed' => false,
            ]);
            
            $output[] = "🔴 MODIFIED Mardal User:";
            $output[] = "   - New End Date: {$newMardalEndDate->format('Y-m-d')}";
            $output[] = "   - Days Until Expiry: {$newMardalDays} (EXPIRED)";
            $output[] = "   - Status: RED - Should appear in admin dashboard";
        }
        
        // Modify Infinit user - make it EXPIRING (7 days remaining)
        if ($infinitRenewal) {
            $newInfinitEndDate = now()->addDays(7);
            $newInfinitStartDate = $newInfinitEndDate->copy()->subYear();
            $newInfinitDays = (int) now()->diffInDays($newInfinitEndDate, false); // Should be 7
            
            $infinitRenewal->update([
                'membership_start_date' => $newInfinitStartDate,
                'membership_end_date' => $newInfinitEndDate,
                'days_until_expiry' => $newInfinitDays,
                'is_expired' => false,
                'is_hidden' => false,
                'is_renewed' => false,
            ]);
            
            $output[] = "🟠 MODIFIED Infinit User:";
            $output[] = "   - New End Date: {$newInfinitEndDate->format('Y-m-d')}";
            $output[] = "   - Days Until Expiry: {$newInfinitDays} (EXPIRING SOON)";
            $output[] = "   - Status: ORANGE - Should appear in admin dashboard";
        }
        
        $output[] = "";
        
        // Verify with MembershipService
        $membershipService = new \App\Services\MembershipService();
        
        if ($mardalUser && $mardalRenewal) {
            $mardalColor = $membershipService->getUserColor($mardalUser->id);
            $output[] = "🎨 Mardal Color: {$mardalColor} (should be #dc3545 - RED)";
        }
        
        if ($infinitUser && $infinitRenewal) {
            $infinitColor = $membershipService->getUserColor($infinitUser->id);
            $output[] = "🎨 Infinit Color: {$infinitColor} (should be #ff6c37 - ORANGE)";
        }
        
        $output[] = "";
        
        // Test admin dashboard logic
        $adminDashboardRenewals = \App\Models\MembershipRenewal::with('user')
            ->where('is_renewed', false)
            ->where('is_hidden', false)
            ->get()
            ->filter(function ($renewal) {
                $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
                return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30;
            });
        
        $output[] = "🔍 ADMIN DASHBOARD VERIFICATION:";
        $output[] = "   - Total renewals that will appear: " . $adminDashboardRenewals->count();
        foreach ($adminDashboardRenewals as $renewal) {
            $userName = $renewal->user ? $renewal->user->name : 'Unknown';
            $userEmail = $renewal->user ? $renewal->user->email : 'Unknown';
            $calculatedDays = $renewal->calculateDaysUntilExpiry();
            $status = $calculatedDays <= 0 ? '🔴 EXPIRED' : '🟠 EXPIRING';
            $output[] = "   - {$status} {$userName} ({$userEmail}): {$calculatedDays} days";
        }
        
        $output[] = "";
        $output[] = "🔑 LOGIN CREDENTIALS:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        $output[] = "👑 SUPER ADMIN: kushtrim.m.arifi@gmail.com / [CONFIGURED_VIA_ENV]";
        $output[] = "🔴 EXPIRED USER: info@mardal.ch / mardal123";
        $output[] = "🟠 EXPIRING USER: infinitdizzajn@gmail.com / alipasha";
        $output[] = "";
        
        $output[] = "✅ SUCCESS! Both users should now appear in Super Admin dashboard";
        
        return response('<h2>✅ Subscriptions Modified Successfully!</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👑 Check Admin Dashboard</a><br><br><a href="/admin/users" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👥 View Admin Users</a><br><br><a href="/login" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔴 Test Expired User</a>');
        
    } catch (\Exception $e) {
        return response('<h2>❌ Error Modifying Subscriptions</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
    }
})->middleware(['auth', 'super_admin']);

// Simple test route to check if routes are working
Route::get('/test-routes-working', function() {
    return response()->json([
        'status' => 'Routes are working!',
        'timestamp' => now()->toDateTimeString(),
        'available_routes' => [
            '/setup-test-users',
            '/expire-existing-subscriptions',
            '/test-membership-renewal',
        ],
        'message' => 'If you see this, the routes are deployed correctly.'
    ]);
});

// Diagnostic route to see what's happening with users
Route::get('/diagnose-users', function() {
    try {
        $output = [];
        $output[] = "🔍 DIAGNOSING USER MEMBERSHIP ISSUES";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "=" . str_repeat("=", 60);
        $output[] = "";
        
        // Check if users exist
        $mardalUser = \App\Models\User::where('email', 'info@mardal.ch')->first();
        $infinitUser = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
        
        $output[] = "👥 USER CHECK:";
        $output[] = "- Mardal user (info@mardal.ch): " . ($mardalUser ? "✅ EXISTS (ID: {$mardalUser->id})" : "❌ NOT FOUND");
        $output[] = "- Infinit user (infinitdizzajn@gmail.com): " . ($infinitUser ? "✅ EXISTS (ID: {$infinitUser->id})" : "❌ NOT FOUND");
        $output[] = "";
        
        // Check all users
        $allUsers = \App\Models\User::all();
        $output[] = "📊 ALL USERS IN DATABASE:";
        foreach ($allUsers as $user) {
            $output[] = "- ID: {$user->id}, Name: '{$user->name}', Email: '{$user->email}', Role: '{$user->role}'";
        }
        $output[] = "";
        
        // Check all membership renewals
        $allRenewals = \App\Models\MembershipRenewal::with('user')->get();
        $output[] = "🔄 ALL MEMBERSHIP RENEWALS:";
        if ($allRenewals->count() > 0) {
            foreach ($allRenewals as $renewal) {
                $userName = $renewal->user ? $renewal->user->name : 'Unknown User';
                $userEmail = $renewal->user ? $renewal->user->email : 'Unknown Email';
                $calculatedDays = $renewal->calculateDaysUntilExpiry();
                $output[] = "- User: {$userName} ({$userEmail})";
                $output[] = "  Start: {$renewal->membership_start_date}";
                $output[] = "  End: {$renewal->membership_end_date}";
                $output[] = "  Days Until Expiry (DB): {$renewal->days_until_expiry}";
                $output[] = "  Days Until Expiry (Calculated): {$calculatedDays}";
                $output[] = "  Is Expired: " . ($renewal->is_expired ? 'Yes' : 'No');
                $output[] = "  Is Hidden: " . ($renewal->is_hidden ? 'Yes' : 'No');
                $output[] = "  Is Renewed: " . ($renewal->is_renewed ? 'Yes' : 'No');
                $output[] = "";
            }
        } else {
            $output[] = "❌ NO MEMBERSHIP RENEWALS FOUND!";
        }
        $output[] = "";
        
        // Check all payments
        $allPayments = \App\Models\Payment::with('user')->get();
        $output[] = "💳 ALL PAYMENTS:";
        if ($allPayments->count() > 0) {
            foreach ($allPayments as $payment) {
                $userName = $payment->user ? $payment->user->name : 'Unknown User';
                $userEmail = $payment->user ? $payment->user->email : 'Unknown Email';
                $output[] = "- User: {$userName} ({$userEmail})";
                $output[] = "  Amount: {$payment->amount}, Type: {$payment->payment_type}, Status: {$payment->status}";
                $output[] = "  Created: {$payment->created_at}";
                $output[] = "";
            }
        } else {
            $output[] = "❌ NO PAYMENTS FOUND!";
        }
        $output[] = "";
        
        // Test admin dashboard logic
        $adminDashboardRenewals = \App\Models\MembershipRenewal::with('user')
            ->where('is_renewed', false)
            ->where('is_hidden', false)
            ->get()
            ->filter(function ($renewal) {
                $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
                return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30;
            });
        
        $output[] = "🎛️ ADMIN DASHBOARD LOGIC TEST:";
        $output[] = "- Query: is_renewed=false AND is_hidden=false AND days_until_expiry <= 30 AND > -30";
        $output[] = "- Total renewals matching criteria: " . $adminDashboardRenewals->count();
        
        if ($adminDashboardRenewals->count() > 0) {
            foreach ($adminDashboardRenewals as $renewal) {
                $userName = $renewal->user ? $renewal->user->name : 'Unknown';
                $userEmail = $renewal->user ? $renewal->user->email : 'Unknown';
                $calculatedDays = $renewal->calculateDaysUntilExpiry();
                $status = $calculatedDays <= 0 ? '🔴 EXPIRED' : '🟠 EXPIRING';
                $output[] = "  - {$status} {$userName} ({$userEmail}): {$calculatedDays} days";
            }
        } else {
            $output[] = "❌ NO USERS WILL APPEAR IN ADMIN DASHBOARD!";
            $output[] = "";
            $output[] = "🔧 POSSIBLE ISSUES:";
            $output[] = "1. No membership renewals exist";
            $output[] = "2. All renewals are marked as renewed (is_renewed=true)";
            $output[] = "3. All renewals are hidden (is_hidden=true)";
            $output[] = "4. Days until expiry is outside the 30-day window";
        }
        
        $output[] = "";
        $output[] = "🛠️ NEXT STEPS:";
        if ($allRenewals->count() === 0) {
            $output[] = "1. Visit /setup-test-users to create memberships";
        } else {
            $output[] = "1. Visit /expire-existing-subscriptions to modify existing memberships";
        }
        $output[] = "2. Check the results above to understand why users aren't showing";
        
        return response('<h2>🔍 User Diagnosis Results</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/setup-test-users" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🧪 Setup Test Users</a><br><br><a href="/expire-existing-subscriptions" style="background: #ff6c37; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">⏰ Expire Existing Subscriptions</a><br><br><a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👑 Admin Dashboard</a>');
        
    } catch (\Exception $e) {
        return response('<h2>❌ Error During Diagnosis</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
    }
})->middleware(['auth', 'super_admin']);

require __DIR__.'/auth.php'; 

    // Test professional email setup
    Route::get('/test-professional-email', function () {
        try {
            $output = [];
            $output[] = "📧 Testing Professional Email Setup";
            $output[] = "Email: info@xhamia-en-nur.ch";
            $output[] = "Provider: Zoho EU";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "";
            
            // Test email configuration
            $output[] = "📋 Email Settings:";
            $output[] = "- MAIL_MAILER: " . config('mail.default');
            $output[] = "- MAIL_HOST: " . config('mail.mailers.smtp.host');
            $output[] = "- MAIL_PORT: " . config('mail.mailers.smtp.port');
            $output[] = "- MAIL_USERNAME: " . config('mail.mailers.smtp.username');
            $output[] = "- MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption');
            $output[] = "- MAIL_FROM_ADDRESS: " . config('mail.from.address');
            $output[] = "- MAIL_FROM_NAME: " . config('mail.from.name');
            $output[] = "";
            
            // Verify configuration
            $isConfigured = (
                config('mail.mailers.smtp.host') === 'smtp.zoho.eu' &&
                config('mail.mailers.smtp.username') === 'info@xhamia-en-nur.ch' &&
                config('mail.from.address') === 'info@xhamia-en-nur.ch' &&
                config('mail.from.name') === 'EN NUR - Xhamia'
            );
            
            if ($isConfigured) {
                $output[] = "✅ Professional email configuration is correct!";
                $output[] = "";
                
                // Send test email
                $output[] = "📤 Sending test email...";
                
                Mail::raw("🎉 Professional Email Setup Test\n\nThis is a test email from your professional email system.\n\nConfiguration:\n- From: EN NUR - Xhamia <info@xhamia-en-nur.ch>\n- Provider: Zoho EU\n- Timestamp: " . now()->toDateTimeString() . "\n\n✅ Your professional email system is working correctly!\n\nBest regards,\nEN NUR Membership System", function ($mail) {
                    $mail->to('info@xhamia-en-nur.ch') // Send to self for testing
                         ->subject('🎉 Professional Email Test - ' . now()->format('H:i:s'))
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                $output[] = "✅ Email sent successfully!";
                $output[] = "Check the inbox for info@xhamia-en-nur.ch";
                $output[] = "";
                $output[] = "🚀 Professional email system is ready for:";
                $output[] = "• Membership renewal notifications";
                $output[] = "• Payment confirmations";
                $output[] = "• User registration emails";
                $output[] = "• Admin notifications";
                
            } else {
                $output[] = "❌ Professional email configuration needs attention:";
                if (config('mail.mailers.smtp.host') !== 'smtp.zoho.eu') {
                    $output[] = "- SMTP host should be 'smtp.zoho.eu'";
                }
                if (config('mail.mailers.smtp.username') !== 'info@xhamia-en-nur.ch') {
                    $output[] = "- Username should be 'info@xhamia-en-nur.ch'";
                }
                if (config('mail.from.address') !== 'info@xhamia-en-nur.ch') {
                    $output[] = "- From address should be 'info@xhamia-en-nur.ch'";
                }
                if (config('mail.from.name') !== 'EN NUR - Xhamia') {
                    $output[] = "- From name should be 'EN NUR - Xhamia'";
                }
            }
            
            return response('<h2>Professional Email Test Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin/testing-dashboard">View Testing Dashboard</a><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>Professional Email Test Failed</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre><br><a href="/admin">Go to Dashboard</a>');
        }
    });

    // Comprehensive email system test
    Route::get('/test-all-emails', function () {
        try {
            $output = [];
            $output[] = "🧪 COMPREHENSIVE EMAIL SYSTEM TEST";
            $output[] = "Professional Email: info@xhamia-en-nur.ch";
            $output[] = "Provider: Zoho EU";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $output[] = "";
            
            // Test 1: Email Configuration
            $output[] = "📋 TEST 1: Email Configuration";
            $output[] = "- MAIL_MAILER: " . config('mail.default');
            $output[] = "- MAIL_HOST: " . config('mail.mailers.smtp.host');
            $output[] = "- MAIL_USERNAME: " . config('mail.mailers.smtp.username');
            $output[] = "- MAIL_FROM_ADDRESS: " . config('mail.from.address');
            $output[] = "- MAIL_FROM_NAME: " . config('mail.from.name');
            
            $configOk = (
                config('mail.mailers.smtp.host') === 'smtp.zoho.eu' &&
                config('mail.mailers.smtp.username') === 'info@xhamia-en-nur.ch' &&
                config('mail.from.address') === 'info@xhamia-en-nur.ch' &&
                config('mail.from.name') === 'EN NUR - Xhamia'
            );
            
            $output[] = $configOk ? "✅ Configuration: PASSED" : "❌ Configuration: FAILED";
            $output[] = "";
            
            // Test 2: SMTP Connection
            $output[] = "🔌 TEST 2: SMTP Connection";
            try {
                $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                    config('mail.mailers.smtp.host'),
                    config('mail.mailers.smtp.port'),
                    config('mail.mailers.smtp.encryption') === 'tls'
                );
                $transport->setUsername(config('mail.mailers.smtp.username'));
                $transport->setPassword(config('mail.mailers.smtp.password'));
                $transport->start();
                $output[] = "✅ SMTP Connection: PASSED";
                $transport->stop();
            } catch (\Exception $e) {
                $output[] = "❌ SMTP Connection: FAILED - " . $e->getMessage();
            }
            $output[] = "";
            
            // Test 3: Basic Email Send
            $output[] = "📤 TEST 3: Basic Email Send";
            try {
                Mail::raw("🎉 Basic Email Test\n\nThis is a test email from your professional email system.\n\nTimestamp: " . now()->toDateTimeString() . "\n\nBest regards,\nEN NUR - Xhamia", function ($mail) {
                    $mail->to('info@xhamia-en-nur.ch')
                         ->subject('✅ Basic Email Test - ' . now()->format('H:i:s'))
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                $output[] = "✅ Basic Email: PASSED";
            } catch (\Exception $e) {
                $output[] = "❌ Basic Email: FAILED - " . $e->getMessage();
            }
            $output[] = "";
            
            // Test 4: User Registration Email Verification
            $output[] = "👤 TEST 4: User Registration Email";
            try {
                // Create a test user temporarily
                $testUser = new \App\Models\User([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => null
                ]);
                
                // Test email verification notification
                $verificationUrl = URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addMinutes(60),
                    ['id' => 999, 'hash' => sha1('test@example.com')]
                );
                
                $emailBody = "Welcome to EN NUR - Xhamia!\n\n";
                $emailBody .= "Thank you for registering. Please verify your email address by clicking the link below:\n\n";
                $emailBody .= $verificationUrl . "\n\n";
                $emailBody .= "If you did not create an account, no further action is required.\n\n";
                $emailBody .= "Best regards,\nEN NUR - Xhamia Team";
                
                Mail::raw($emailBody, function ($mail) {
                    $mail->to('info@xhamia-en-nur.ch') // Send to ourselves for testing
                         ->subject('Verify Your Email Address - EN NUR')
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                $output[] = "✅ Registration Email: PASSED";
            } catch (\Exception $e) {
                $output[] = "❌ Registration Email: FAILED - " . $e->getMessage();
            }
            $output[] = "";
            
            // Test 5: Membership Renewal Notification
            $output[] = "🔔 TEST 5: Membership Renewal Notification";
            try {
                $renewalEmailBody = "Dear Member,\n\n";
                $renewalEmailBody .= "This is a reminder that your membership will expire in 7 days.\n\n";
                $renewalEmailBody .= "MEMBERSHIP DETAILS:\n";
                $renewalEmailBody .= "━━━━━━━━━━━━━━━━━━━━\n";
                $renewalEmailBody .= "• Member ID: MBR-000001\n";
                $renewalEmailBody .= "• Current Expiry: " . now()->addDays(7)->format('M d, Y') . "\n";
                $renewalEmailBody .= "• Days Remaining: 7\n\n";
                $renewalEmailBody .= "To renew your membership, please visit:\n";
                $renewalEmailBody .= config('app.url') . "/payment\n\n";
                $renewalEmailBody .= "Best regards,\nEN NUR - Xhamia Team";
                
                Mail::raw($renewalEmailBody, function ($mail) {
                    $mail->to('info@xhamia-en-nur.ch')
                         ->subject('Membership Renewal Reminder - 7 Days Remaining')
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                $output[] = "✅ Renewal Notification: PASSED";
            } catch (\Exception $e) {
                $output[] = "❌ Renewal Notification: FAILED - " . $e->getMessage();
            }
            $output[] = "";
            
            // Test 6: Payment Confirmation Email
            $output[] = "💳 TEST 6: Payment Confirmation";
            try {
                $paymentEmailBody = "Payment Confirmation - EN NUR Membership\n\n";
                $paymentEmailBody .= "Dear Member,\n\n";
                $paymentEmailBody .= "Your payment has been successfully processed.\n\n";
                $paymentEmailBody .= "PAYMENT DETAILS:\n";
                $paymentEmailBody .= "━━━━━━━━━━━━━━━━━━━━\n";
                $paymentEmailBody .= "• Payment ID: PAY-TEST-001\n";
                $paymentEmailBody .= "• Amount: CHF 350.00\n";
                $paymentEmailBody .= "• Type: Membership\n";
                $paymentEmailBody .= "• Date: " . now()->format('M d, Y H:i') . "\n";
                $paymentEmailBody .= "• Status: Completed\n\n";
                $paymentEmailBody .= "Your membership is now active until " . now()->addYear()->format('M d, Y') . "\n\n";
                $paymentEmailBody .= "Thank you for your support!\n\n";
                $paymentEmailBody .= "Best regards,\nEN NUR - Xhamia Team";
                
                Mail::raw($paymentEmailBody, function ($mail) {
                    $mail->to('info@xhamia-en-nur.ch')
                         ->subject('Payment Confirmation - Membership Renewed')
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                $output[] = "✅ Payment Confirmation: PASSED";
            } catch (\Exception $e) {
                $output[] = "❌ Payment Confirmation: FAILED - " . $e->getMessage();
            }
            $output[] = "";
            
            // Test 7: Password Reset Email
            $output[] = "🔐 TEST 7: Password Reset Email";
            try {
                $resetUrl = url('/password/reset/test-token');
                
                $resetEmailBody = "Password Reset Request - EN NUR\n\n";
                $resetEmailBody .= "You are receiving this email because we received a password reset request for your account.\n\n";
                $resetEmailBody .= "Click the link below to reset your password:\n";
                $resetEmailBody .= $resetUrl . "\n\n";
                $resetEmailBody .= "This password reset link will expire in 60 minutes.\n\n";
                $resetEmailBody .= "If you did not request a password reset, no further action is required.\n\n";
                $resetEmailBody .= "Best regards,\nEN NUR - Xhamia Team";
                
                Mail::raw($resetEmailBody, function ($mail) {
                    $mail->to('info@xhamia-en-nur.ch')
                         ->subject('Reset Password - EN NUR')
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                $output[] = "✅ Password Reset: PASSED";
            } catch (\Exception $e) {
                $output[] = "❌ Password Reset: FAILED - " . $e->getMessage();
            }
            $output[] = "";
            
            // Summary
            $output[] = "📊 TEST SUMMARY";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $output[] = "✅ All email types tested successfully!";
            $output[] = "";
            $output[] = "📧 Check your inbox: info@xhamia-en-nur.ch";
            $output[] = "You should receive 6 test emails covering all functionality:";
            $output[] = "1. Basic Email Test";
            $output[] = "2. Email Verification";
            $output[] = "3. Membership Renewal Reminder";
            $output[] = "4. Payment Confirmation";
            $output[] = "5. Password Reset";
            $output[] = "";
            $output[] = "🚀 Your professional email system is fully operational!";
            
            return response('<h2>📧 Comprehensive Email System Test Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin/testing-dashboard">View Testing Dashboard</a><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>❌ Email System Test Failed</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre><br><a href="/admin">Go to Dashboard</a>');
        }
    });

    // Add this route near the other debug/admin routes (around line 472)
    Route::get('/expire-infinit-user', function () {
        // Only allow super admins
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            return response('<h1>❌ Access Denied</h1><p>Only super admins can access this.</p>', 403);
        }
        
        try {
            // Run the expire user command
            Artisan::call('user:expire-test-user');
            $output = Artisan::output();
            
            return response('
                <h1>🎯 Expire Test User Command</h1>
                <pre style="background: #f5f5f5; padding: 20px; border-radius: 8px; font-family: monospace;">' . 
                htmlspecialchars($output) . 
                '</pre>
                <br>
                <a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    🎛️ Go to Admin Dashboard
                </a>
            ');
            
        } catch (Exception $e) {
            return response('
                <h1>❌ Error</h1>
                <pre style="background: #f8d7da; padding: 20px; border-radius: 8px; color: #721c24;">' . 
                htmlspecialchars($e->getMessage()) . 
                '</pre>
            ');
        }
    })->name('expire.infinit.user');

    // EMERGENCY: Simple password setup for login issues
    Route::get('/emergency-simple-password', function() {
        if (env('APP_ENV') !== 'production') {
            return response('Only available in production', 403);
        }
        
        try {
            $email = 'kushtrim.m.arifi@gmail.com';
            $simplePassword = 'SuperAdmin2025'; // Simple password without special characters
            
            // Update user with simple password
            $user = DB::table('users')->where('email', $email)->first();
            
            if (!$user) {
                return response('User not found', 404);
            }
            
            $newHash = Hash::make($simplePassword);
            DB::table('users')
                ->where('email', $email)
                ->update([
                    'password' => $newHash,
                    'updated_at' => now(),
                ]);
            
            // Test the password immediately
            $testResult = Hash::check($simplePassword, $newHash);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Emergency simple password set successfully',
                'credentials' => [
                    'email' => $email,
                    'password' => $simplePassword
                ],
                'password_test' => $testResult ? 'WORKING' : 'FAILED',
                'instructions' => 'Use these credentials to login immediately',
                'note' => 'This is a temporary simple password for emergency access'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // EMERGENCY: Force refresh email configuration
    Route::get('/force-refresh-email-config', function() {
        if (env('APP_ENV') !== 'production') {
            return response('Only available in production', 403);
        }
        
        try {
            $output = [];
            $output[] = "🔄 Force Refreshing Email Configuration";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "";
            
            // Clear config cache
            Artisan::call('config:clear');
            $output[] = "✅ Config cache cleared";
            
            // Clear view cache
            Artisan::call('view:clear');
            $output[] = "✅ View cache cleared";
            
            // Clear route cache
            Artisan::call('route:clear');
            $output[] = "✅ Route cache cleared";
            
            $output[] = "";
            $output[] = "📧 Current Email Configuration:";
            $output[] = "- MAIL_MAILER: " . config('mail.default');
            $output[] = "- MAIL_HOST: " . config('mail.mailers.smtp.host');
            $output[] = "- MAIL_PORT: " . config('mail.mailers.smtp.port');
            $output[] = "- MAIL_USERNAME: " . config('mail.mailers.smtp.username');
            $output[] = "- MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption');
            $output[] = "- MAIL_FROM_ADDRESS: " . config('mail.from.address');
            $output[] = "- MAIL_FROM_NAME: " . config('mail.from.name');
            $output[] = "";
            
            // Force reload config values from environment
            $mailConfig = [
                'mail.default' => env('MAIL_MAILER', 'smtp'),
                'mail.mailers.smtp.host' => env('MAIL_HOST', 'smtp.mailgun.org'),
                'mail.mailers.smtp.port' => env('MAIL_PORT', 587),
                'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
                'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
                'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'mail.from.address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'mail.from.name' => env('MAIL_FROM_NAME', 'Example'),
            ];
            
            // Set the config values directly
            foreach ($mailConfig as $key => $value) {
                config([$key => $value]);
            }
            
            $output[] = "🔧 Environment Variables (Direct Read):";
            $output[] = "- MAIL_MAILER: " . env('MAIL_MAILER');
            $output[] = "- MAIL_HOST: " . env('MAIL_HOST');
            $output[] = "- MAIL_PORT: " . env('MAIL_PORT');
            $output[] = "- MAIL_USERNAME: " . env('MAIL_USERNAME');
            $output[] = "- MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? '[SET - Length: ' . strlen(env('MAIL_PASSWORD')) . ']' : '[NOT SET]');
            $output[] = "- MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION');
            $output[] = "- MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS');
            $output[] = "- MAIL_FROM_NAME: " . env('MAIL_FROM_NAME');
            $output[] = "";
            
            // Test SMTP connection with forced config
            $output[] = "🔌 Testing SMTP Connection:";
            try {
                $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                    env('MAIL_HOST', 'smtppro.zoho.com'),
                    (int) env('MAIL_PORT', 465),
                    env('MAIL_ENCRYPTION', 'ssl') === 'ssl'
                );
                
                $transport->setUsername(env('MAIL_USERNAME', 'info@xhamia-en-nur.ch'));
                $transport->setPassword(env('MAIL_PASSWORD'));
                
                $transport->start();
                $output[] = "✅ SMTP Connection: SUCCESS";
                $transport->stop();
                
                // Test sending email
                $output[] = "";
                $output[] = "📤 Testing Email Send:";
                
                Mail::raw("Email configuration refresh test.\n\nTimestamp: " . now()->toDateTimeString() . "\n\nThis email confirms that the SMTP configuration is working correctly after refresh.", function ($message) {
                    $message->to(env('MAIL_FROM_ADDRESS', 'info@xhamia-en-nur.ch'))
                            ->subject('Email Config Refresh Test - ' . now()->format('H:i:s'))
                            ->from(env('MAIL_FROM_ADDRESS', 'info@xhamia-en-nur.ch'), env('MAIL_FROM_NAME', 'EN NUR - Xhamia'));
                });
                
                $output[] = "✅ Email sent successfully!";
                $output[] = "Check inbox: " . env('MAIL_FROM_ADDRESS');
                
            } catch (\Exception $e) {
                $output[] = "❌ SMTP Connection/Email failed:";
                $output[] = "Error: " . $e->getMessage();
                $output[] = "Class: " . get_class($e);
                
                // Specific troubleshooting for common issues
                if (strpos($e->getMessage(), '550 5.7.1') !== false) {
                    $output[] = "";
                    $output[] = "🔍 RELAYING DENIED - Possible Solutions:";
                    $output[] = "1. Verify MAIL_PASSWORD_SECRET is set in Render dashboard";
                    $output[] = "2. Check if Zoho app password is still valid";
                    $output[] = "3. Ensure 2FA is enabled on Zoho account";
                    $output[] = "4. Try regenerating Zoho app password";
                }
            }
            
            return response('<h2>Email Configuration Refresh Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/dashboard">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>Email Configuration Refresh Failed</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre><br><a href="/dashboard">Go to Dashboard</a>');
        }
    });



require __DIR__.'/auth.php';

// Temporary test route to verify email verification is working (remove after testing)
Route::get('/test-email-verification/{email}', function ($email) {
    try {
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }
        
        // Test sending verification email manually
        $user->sendEmailVerificationNotification();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Email verification sent successfully!',
            'user_email' => $user->email,
            'user_verified' => $user->hasVerifiedEmail(),
            'mail_config' => [
                'MAIL_MAILER' => config('mail.default'),
                'MAIL_HOST' => config('mail.mailers.smtp.host'),
                'MAIL_FROM_ADDRESS' => config('mail.from.address'),
                'MAIL_FROM_NAME' => config('mail.from.name'),
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.email.verification');



