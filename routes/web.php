<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentExportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembershipRenewalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return '<h1>Laravel is Working!</h1><p>Your EN NUR Membership System is successfully deployed!</p><p>PHP Version: ' . phpversion() . '</p><p>Laravel Version: ' . app()->version() . '</p>';
});

Route::get('/test-route', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel is working!',
        'timestamp' => now(),
        'php_version' => phpversion()
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
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
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
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
        
        // System Management Routes (Super Admin only)
        Route::post('/system/backup', [AdminController::class, 'createSystemBackup'])->name('system.backup');
        Route::post('/system/clear-logs', [AdminController::class, 'clearSystemLogs'])->name('system.clear-logs');
        Route::post('/notifications/bulk-send', [AdminController::class, 'sendBulkNotifications'])->name('notifications.bulk-send');
    });
});

require __DIR__.'/auth.php'; 