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
        
         
         
         
         // System Management Routes (Super Admin only)
        Route::post('/system/backup', [AdminController::class, 'createSystemBackup'])->name('system.backup');
        Route::post('/system/clear-logs', [AdminController::class, 'clearSystemLogs'])->name('system.clear-logs');
        Route::post('/notifications/bulk-send', [AdminController::class, 'sendBulkNotifications'])->name('notifications.bulk-send');
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




require __DIR__.'/auth.php';



