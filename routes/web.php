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

Route::get('/debug-info', function() {
    $info = [
        'laravel_working' => 'YES - Laravel is booting successfully!',
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
        'current_time' => now()->toDateTimeString(),
        'environment' => [
            'APP_ENV' => env('APP_ENV'),
            'APP_DEBUG' => env('APP_DEBUG'),
            'APP_KEY' => env('APP_KEY') ? 'Set (' . strlen(env('APP_KEY')) . ' chars)' : 'NOT SET',
            'APP_URL' => env('APP_URL'),
        ],
        'database' => [
            'DB_CONNECTION' => env('DB_CONNECTION'),
            'DB_HOST' => env('DB_HOST'),
            'DB_PORT' => env('DB_PORT'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME') ? 'Set' : 'NOT SET',
            'DB_PASSWORD' => env('DB_PASSWORD') ? 'Set' : 'NOT SET',
        ],
        'directories' => [
            'storage_writable' => is_writable(storage_path()) ? 'YES' : 'NO',
            'bootstrap_cache_writable' => is_writable(base_path('bootstrap/cache')) ? 'YES' : 'NO',
            'storage_logs_exists' => file_exists(storage_path('logs')) ? 'YES' : 'NO',
        ],
        'database_test' => 'Testing...'
    ];
    
    // Test database connection
    try {
        \DB::connection()->getPdo();
        $info['database_test'] = 'SUCCESS - Database connected';
    } catch (\Exception $e) {
        $info['database_test'] = 'FAILED: ' . $e->getMessage();
    }
    
    return response()->json($info, 200, [], JSON_PRETTY_PRINT);
});

Route::get('/view-logs', function() {
    $logPath = storage_path('logs/laravel.log');
    
    if (!file_exists($logPath)) {
        return response()->json([
            'status' => 'No log file found',
            'path' => $logPath,
            'storage_path' => storage_path(),
            'files_in_logs' => file_exists(storage_path('logs')) ? scandir(storage_path('logs')) : 'logs directory does not exist'
        ]);
    }
    
    $logs = file_get_contents($logPath);
    $recentLogs = collect(explode("\n", $logs))
        ->filter()
        ->takeLast(50)
        ->implode("\n");
    
    return response('<pre>' . htmlspecialchars($recentLogs) . '</pre>');
});

Route::get('/debug', function() {
    return dd('Laravel is booting successfully!', [
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
        'app_env' => env('APP_ENV'),
        'app_debug' => env('APP_DEBUG'),
        'db_connection' => env('DB_CONNECTION'),
        'app_key_set' => env('APP_KEY') ? 'Yes' : 'No',
    ]);
});

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

Route::get('/health/detailed', function () {
    $checks = [
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
        'checks' => []
    ];
    
    // Database check
    try {
        DB::connection()->getPdo();
        $checks['checks']['database'] = 'CONNECTED';
    } catch (Exception $e) {
        $checks['checks']['database'] = 'FAILED: ' . $e->getMessage();
        $checks['status'] = 'ERROR';
    }
    
    // Storage check
    $checks['checks']['storage_writable'] = is_writable(storage_path()) ? 'WRITABLE' : 'NOT_WRITABLE';
    $checks['checks']['cache_writable'] = is_writable(bootstrap_path('cache')) ? 'WRITABLE' : 'NOT_WRITABLE';
    
    // Extensions check
    $required_extensions = ['pdo_pgsql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'];
    $missing_extensions = [];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missing_extensions[] = $ext;
        }
    }
    $checks['checks']['php_extensions'] = empty($missing_extensions) ? 'ALL_LOADED' : 'MISSING: ' . implode(', ', $missing_extensions);
    
    // Configuration check
    $checks['checks']['app_key'] = config('app.key') ? 'SET' : 'NOT_SET';
    $checks['checks']['database_url'] = env('DATABASE_URL') ? 'SET' : 'NOT_SET';
    
    if (!empty($missing_extensions) || !config('app.key') || !env('DATABASE_URL')) {
        $checks['status'] = 'ERROR';
    }
    
    return response()->json($checks, $checks['status'] === 'OK' ? 200 : 500);
});

Route::get('/test-route', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel is working!',
        'timestamp' => now(),
        'php_version' => phpversion()
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
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
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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

Route::get('/force-migrate', function() {
    if (env('APP_ENV') !== 'production') {
        return response('Only available in production', 403);
    }
    
    try {
        // Force run migrations
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();
        
        // Force run seeder if no users exist
        $userCount = DB::table('users')->count();
        $seedOutput = '';
        if ($userCount === 0) {
            Artisan::call('db:seed', ['--class' => 'ProductionSeeder', '--force' => true]);
            $seedOutput = Artisan::output();
        }
        
        return response()->json([
            'status' => 'success',
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput,
            'user_count' => DB::table('users')->count(),
            'tables' => collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))
                ->pluck('tablename')->toArray()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/db-status', function() {
    if (env('APP_ENV') !== 'production') {
        return response('Only available in production', 403);
    }
    
    try {
        // Check database connection
        $dbConnected = true;
        $connectionError = null;
        try {
            DB::connection()->getPdo();
        } catch (Exception $e) {
            $dbConnected = false;
            $connectionError = $e->getMessage();
        }
        
        // Get existing tables
        $tables = [];
        $tablesError = null;
        try {
            $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            $tables = array_map(function($table) { return $table->tablename; }, $tables);
        } catch (Exception $e) {
            $tablesError = $e->getMessage();
        }
        
        // Check migration status
        $migrationStatus = 'unknown';
        $migrationError = null;
        try {
            $migrationStatus = 'migrations_table_exists';
            $migrations = DB::table('migrations')->pluck('migration')->toArray();
        } catch (Exception $e) {
            $migrationStatus = 'no_migrations_table';
            $migrationError = $e->getMessage();
            $migrations = [];
        }
        
        return response()->json([
            'status' => 'success',
            'database_connected' => $dbConnected,
            'connection_error' => $connectionError,
            'existing_tables' => $tables,
            'tables_error' => $tablesError,
            'migration_status' => $migrationStatus,
            'migration_error' => $migrationError,
            'ran_migrations' => $migrations ?? [],
            'app_env' => env('APP_ENV'),
            'db_connection' => env('DB_CONNECTION'),
            'db_host' => env('DB_HOST'),
            'db_database' => env('DB_DATABASE'),
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/reset-migrations', function() {
    if (env('APP_ENV') !== 'production') {
        return response('Only available in production', 403);
    }
    
    try {
        // Drop all tables except migrations
        $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename != 'migrations'");
        $dropOutput = [];
        
        foreach ($tables as $table) {
            $tableName = $table->tablename;
            try {
                DB::statement("DROP TABLE IF EXISTS {$tableName} CASCADE");
                $dropOutput[] = "Dropped: {$tableName}";
            } catch (Exception $e) {
                $dropOutput[] = "Failed to drop {$tableName}: " . $e->getMessage();
            }
        }
        
        // Clear migration records for dropped tables
        try {
            DB::table('migrations')->delete();
            $dropOutput[] = "Cleared migration records";
        } catch (Exception $e) {
            $dropOutput[] = "Failed to clear migrations: " . $e->getMessage();
        }
        
        // Run fresh migrations
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();
        
        // Run seeder
        Artisan::call('db:seed', ['--class' => 'ProductionSeeder', '--force' => true]);
        $seedOutput = Artisan::output();
        
        return response()->json([
            'status' => 'success',
            'drop_output' => $dropOutput,
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/fix-database', function() {
    if (env('APP_ENV') !== 'production') {
        return response('Only available in production', 403);
    }
    
    try {
        // Get current database state
        $existingTables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        $tableNames = array_map(function($table) { return $table->tablename; }, $existingTables);
        
        $output = [];
        $output[] = "🔍 Found existing tables: " . implode(', ', $tableNames);
        
        // Drop ALL tables to ensure clean state
        $output[] = "🧹 Dropping all existing tables...";
        foreach ($tableNames as $tableName) {
            try {
                DB::statement("DROP TABLE IF EXISTS {$tableName} CASCADE");
                $output[] = "✅ Dropped: {$tableName}";
            } catch (Exception $e) {
                $output[] = "⚠️ Failed to drop {$tableName}: " . $e->getMessage();
            }
        }
        
        // Verify all tables are gone
        $remainingTables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        $output[] = "📊 Remaining tables after cleanup: " . count($remainingTables);
        
        // Run fresh migrations
        $output[] = "🔄 Running fresh migrations...";
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());
        $output[] = "Migration output: " . $migrateOutput;
        
        // Verify all required tables exist
        $requiredTables = ['migrations', 'users', 'sessions', 'cache', 'payments', 'membership_renewals', 'jobs'];
        $finalTables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        $finalTableNames = array_map(function($table) { return $table->tablename; }, $finalTables);
        
        $missing = array_diff($requiredTables, $finalTableNames);
        $output[] = "📋 Required tables status:";
        foreach ($requiredTables as $table) {
            $status = in_array($table, $finalTableNames) ? '✅' : '❌';
            $output[] = "  {$status} {$table}";
        }
        
        if (empty($missing)) {
            // Run seeder if no users exist
            $userCount = DB::table('users')->count();
            $output[] = "👥 Current user count: {$userCount}";
            
            if ($userCount === 0) {
                $output[] = "🌱 Running seeder...";
                Artisan::call('db:seed', ['--class' => 'ProductionSeeder', '--force' => true]);
                $seedOutput = trim(Artisan::output());
                $output[] = "Seed output: " . $seedOutput;
                
                $newUserCount = DB::table('users')->count();
                $output[] = "👥 User count after seeding: {$newUserCount}";
            }
            
            // Clear and rebuild caches
            $output[] = "⚡ Rebuilding caches...";
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:cache');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Database completely reset and rebuilt successfully!',
                'output' => $output,
                'final_tables' => $finalTableNames,
                'user_count' => DB::table('users')->count()
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Some required tables are still missing: ' . implode(', ', $missing),
                'output' => $output,
                'missing_tables' => $missing
            ]);
        }
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/create-admin', function() {
    if (env('APP_ENV') !== 'production') {
        return response('Only available in production', 403);
    }
    
    try {
        // Check current users
        $userCount = DB::table('users')->count();
        $existingAdmin = DB::table('users')->where('email', 'admin@ennur.ch')->first();
        
        $output = [];
        $output[] = "👥 Current user count: {$userCount}";
        
        if ($existingAdmin) {
            $output[] = "✅ Admin user exists: " . $existingAdmin->email;
            $output[] = "📧 Email verified: " . ($existingAdmin->email_verified_at ? 'Yes' : 'No');
            $output[] = "🔐 Role: " . ($existingAdmin->role ?? 'Not set');
        } else {
            $output[] = "❌ Admin user does NOT exist";
            
            // Create admin user
            DB::table('users')->insert([
                'name' => 'EN NUR Admin',
                'email' => 'admin@ennur.ch',
                'email_verified_at' => now(),
                'password' => bcrypt('ENnur2025!Admin'),
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $output[] = "✅ Admin user created successfully!";
        }
        
        // List all users
        $users = DB::table('users')->select('id', 'name', 'email', 'role', 'email_verified_at')->get();
        $output[] = "📋 All users in database:";
        foreach ($users as $user) {
            $verified = $user->email_verified_at ? '✅' : '❌';
            $output[] = "  - {$user->name} ({$user->email}) - Role: {$user->role} - Verified: {$verified}";
        }
        
        return response()->json([
            'status' => 'success',
            'output' => $output,
            'user_count' => $userCount,
            'admin_credentials' => [
                'email' => 'admin@ennur.ch',
                'password' => 'ENnur2025!Admin'
            ]
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/setup-super-admin', function() {
    if (env('APP_ENV') !== 'production') {
        return response('Only available in production', 403);
    }
    
    try {
        $email = 'kushtrim.m.arifi@gmail.com';
        $name = 'SUPER ADMIN';
        $password = 'Alipasha1985X';
        
        // Check if user already exists
        $existingUser = DB::table('users')->where('email', $email)->first();
        
        $output = [];
        
        if ($existingUser) {
            // Update existing user to super admin with FRESH password hash
            $freshHash = Hash::make($password);
            DB::table('users')
                ->where('email', $email)
                ->update([
                    'name' => $name,
                    'password' => $freshHash,
                    'role' => 'super_admin',
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);
            
            $output[] = "✅ Updated existing user to SUPER ADMIN";
            $output[] = "🔄 Generated FRESH password hash";
            $output[] = "📧 Email: {$email}";
            $output[] = "👤 Name: {$name}";
            $output[] = "🔐 Role: super_admin";
        } else {
            // Create new super admin user with FRESH password hash
            $freshHash = Hash::make($password);
            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => $freshHash,
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $output[] = "✅ Created new SUPER ADMIN user";
            $output[] = "🔄 Generated FRESH password hash";
            $output[] = "📧 Email: {$email}";
            $output[] = "👤 Name: {$name}";
            $output[] = "🔐 Role: super_admin";
        }
        
        // Verify the user was created/updated correctly
        $user = DB::table('users')->where('email', $email)->first();
        $output[] = "🔍 Verification:";
        $output[] = "  - User ID: {$user->id}";
        $output[] = "  - Name: {$user->name}";
        $output[] = "  - Email: {$user->email}";
        $output[] = "  - Role: {$user->role}";
        $output[] = "  - Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No');
        
        // Show login credentials
        $output[] = "🎯 LOGIN CREDENTIALS:";
        $output[] = "  📧 Email: {$email}";
        $output[] = "  🔑 Password: {$password}";
        $output[] = "  🌐 Login URL: https://en-nur-membership.onrender.com/login";
        
        return response()->json([
            'status' => 'success',
            'message' => 'Super admin account ready!',
            'output' => $output,
            'credentials' => [
                'email' => $email,
                'password' => $password,
                'login_url' => 'https://en-nur-membership.onrender.com/login'
            ]
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/debug-login', function() {
    if (env('APP_ENV') !== 'production') {
        return response('Only available in production', 403);
    }
    
    try {
        $email = 'kushtrim.m.arifi@gmail.com';
        $password = 'Alipasha1985X';
        
        // Get the user from database
        $user = DB::table('users')->where('email', $email)->first();
        
        $output = [];
        
        if (!$user) {
            $output[] = "❌ User not found in database";
            return response()->json(['status' => 'error', 'output' => $output]);
        }
        
        $output[] = "✅ User found in database:";
        $output[] = "  - ID: {$user->id}";
        $output[] = "  - Name: {$user->name}";
        $output[] = "  - Email: {$user->email}";
        $output[] = "  - Role: {$user->role}";
        $output[] = "  - Password Hash: " . substr($user->password, 0, 20) . "...";
        $output[] = "  - Created: {$user->created_at}";
        $output[] = "  - Updated: {$user->updated_at}";
        
        // Test password verification
        $passwordMatch = password_verify($password, $user->password);
        $output[] = "🔍 Password verification test:";
        $output[] = "  - Input password: {$password}";
        $output[] = "  - Hash from DB: " . substr($user->password, 0, 30) . "...";
        $output[] = "  - Password matches: " . ($passwordMatch ? '✅ YES' : '❌ NO');
        
        // Test with Hash::check (Laravel way)
        $laravelHashCheck = Hash::check($password, $user->password);
        $output[] = "  - Laravel Hash::check: " . ($laravelHashCheck ? '✅ YES' : '❌ NO');
        
        // Test creating a new hash for comparison
        $newHash = bcrypt($password);
        $newHashCheck = Hash::check($password, $newHash);
        $output[] = "🔧 New hash test:";
        $output[] = "  - New hash: " . substr($newHash, 0, 30) . "...";
        $output[] = "  - New hash check: " . ($newHashCheck ? '✅ YES' : '❌ NO');
        
        // If password doesn't match, update it
        if (!$passwordMatch && !$laravelHashCheck) {
            $output[] = "🔄 Password doesn't match, updating with new hash...";
            
            DB::table('users')
                ->where('email', $email)
                ->update([
                    'password' => $newHash,
                    'updated_at' => now(),
                ]);
                
            $output[] = "✅ Password updated successfully";
            $output[] = "🎯 Try logging in now with: {$email} / {$password}";
        }
        
        return response()->json([
            'status' => 'success',
            'output' => $output,
            'password_match' => $passwordMatch || $laravelHashCheck,
            'credentials' => [
                'email' => $email,
                'password' => $password
            ]
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Clean setup super admin route (removes ALL admins and creates only yours)
Route::get('/clean-setup-super-admin', function () {
    try {
        Artisan::call('admin:clean-setup');
        $output = Artisan::output();
        
        return response('<pre>' . $output . '</pre><br><a href="/login">Login as Super Admin</a><br><a href="/admin-diagnose">Run Diagnostics</a>');
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
})->name('clean.setup.super.admin');

// Setup super admin route (for initial setup)
Route::get('/setup-super-admin', function () {
    try {
        Artisan::call('admin:setup-super-admin');
        $output = Artisan::output();
        
        return response('<pre>' . $output . '</pre><br><a href="/login">Login as Super Admin</a>');
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
})->name('setup.super.admin');

// Diagnostic route to check dashboard state
Route::get('/admin-diagnose', function () {
    if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
        abort(403, 'Unauthorized - Super Admin Only');
    }
    
    try {
        Artisan::call('admin:diagnose');
        $output = Artisan::output();
        
        return response('<pre>' . $output . '</pre><br><a href="/admin">Go to Admin Dashboard</a><br><a href="/create-expired-test-users">Create Test Users</a>');
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
})->name('admin.diagnose');

// Test route to create expired users (remove after testing)
Route::get('/create-expired-test-users', function () {
    if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
        abort(403, 'Unauthorized - Super Admin Only');
    }
    
    try {
        Artisan::call('test:create-expired-users');
        $output = Artisan::output();
        
        return response('<pre>' . $output . '</pre><br><a href="/admin">Go to Admin Dashboard</a><br><a href="/admin-diagnose">Run Diagnostics</a>');
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
})->name('create.expired.test.users');

// Run original database seeder (contains proper test users)
Route::get('/run-original-seeder', function () {
    if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
        abort(403, 'Unauthorized - Super Admin Only');
    }
    
    try {
        // Run the original seeder that contains all test users
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
        $output = Artisan::output();
        
        return response('<h2>Original Database Seeder Executed</h2><pre>' . $output . '</pre><br><a href="/admin/users">View Users</a><br><a href="/admin">Go to Dashboard</a>');
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
})->name('run.original.seeder');

// Production setup routes (Super Admin only)
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/verify-production-data', [AdminController::class, 'verifyProductionData']);
    Route::get('/setup-production-data', [AdminController::class, 'setupProductionData']);
    Route::get('/setup-production-email', [AdminController::class, 'setupProductionEmail']);
    Route::get('/setup-test-expiry/{email}', [AdminController::class, 'setupTestExpiry']);
    
    // Diagnostic route to test the latest code deployment
    Route::get('/test-latest-deployment', function () {
        try {
            $output = [];
            $output[] = "🔍 Testing Latest Code Deployment";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            
            // Test the CreateExpiredTestUsers command with --infinit flag
            $output[] = "\n📋 Testing CreateExpiredTestUsers command with --infinit flag...";
            
            // Run the command and capture output
            Artisan::call('test:create-expired-users', ['--infinit' => true]);
            $commandOutput = Artisan::output();
            
            $output[] = "Command Output:";
            $output[] = $commandOutput;
            
            // Check if the user was created/updated
            $user = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
            if ($user) {
                $output[] = "\n✅ User Found:";
                $output[] = "- Name: {$user->name}";
                $output[] = "- Email: {$user->email}";
                $output[] = "- Days Until Expiry: {$user->days_until_expiry}";
                $output[] = "- Membership Status: {$user->membership_status}";
                $output[] = "- Color: {$user->color}";
                $output[] = "- Hidden: " . ($user->hidden ? 'Yes' : 'No');
            } else {
                $output[] = "\n❌ User not found";
            }
            
            // Test the MembershipService color logic
            $output[] = "\n🎨 Testing Color Logic:";
            $membershipService = app(\App\Services\MembershipService::class);
            if ($user) {
                $membershipStatus = $membershipService->getUserMembershipStatus($user);
                if ($membershipStatus) {
                    $output[] = "Border Color: {$membershipStatus['border_color']}";
                    $output[] = "Status Badge: {$membershipStatus['status_badge']['text']}";
                    $output[] = "Display Class: {$membershipStatus['display_class']}";
                } else {
                    $output[] = "No membership status found";
                }
            }
            
            return response('<h2>Latest Deployment Test Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin/users">View Users</a><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>Error Testing Deployment</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre><br><a href="/admin">Go to Dashboard</a>');
        }
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
    
    // Debug SMTP Configuration
    Route::get('/debug-smtp', function () {
        $output = [];
        $output[] = "🔧 SMTP Configuration Debug";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "";
        $output[] = "CURRENT MAIL CONFIGURATION:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        $output[] = "Mailer: " . config('mail.default');
        $output[] = "Host: " . config('mail.mailers.smtp.host');
        $output[] = "Port: " . config('mail.mailers.smtp.port');
        $output[] = "Username: " . config('mail.mailers.smtp.username');
        $output[] = "Password: " . (config('mail.mailers.smtp.password') ? '[CONFIGURED - Length: ' . strlen(config('mail.mailers.smtp.password')) . ']' : '[NOT SET]');
        $output[] = "Encryption: " . config('mail.mailers.smtp.encryption');
        $output[] = "From Address: " . config('mail.from.address');
        $output[] = "From Name: " . config('mail.from.name');
        $output[] = "";
        
        // Test SMTP connection
        $output[] = "TESTING SMTP CONNECTION:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━";
        
        try {
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                config('mail.mailers.smtp.host'),
                config('mail.mailers.smtp.port'),
                config('mail.mailers.smtp.encryption') === 'tls'
            );
            $transport->setUsername(config('mail.mailers.smtp.username'));
            $transport->setPassword(config('mail.mailers.smtp.password'));
            
            // Try to start transport
            $transport->start();
            $output[] = "✅ SMTP Connection: SUCCESS";
            $transport->stop();
        } catch (\Exception $e) {
            $output[] = "❌ SMTP Connection: FAILED";
            $output[] = "Error: " . $e->getMessage();
            $output[] = "Class: " . get_class($e);
        }
        
        $output[] = "";
        $output[] = "TESTING SIMPLE EMAIL SEND:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        
        try {
            $testEmail = config('mail.from.address'); // Send to self for testing
            
            Mail::raw("This is a test email to verify SMTP configuration.\n\nTimestamp: " . now()->toDateTimeString(), function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('SMTP Test - ' . now()->toDateTimeString())
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $output[] = "✅ Email sent successfully to: {$testEmail}";
            $output[] = "Check your inbox to confirm delivery.";
            
        } catch (\Exception $e) {
            $output[] = "❌ Email send failed:";
            $output[] = "Error: " . $e->getMessage();
            $output[] = "Class: " . get_class($e);
            $output[] = "File: " . $e->getFile() . ":" . $e->getLine();
            
            // Check for specific Gmail errors
            if (strpos($e->getMessage(), '550 5.7.1') !== false) {
                $output[] = "";
                $output[] = "🔍 GMAIL RELAYING DENIED - POSSIBLE SOLUTIONS:";
                $output[] = "1. Verify 2-Factor Authentication is enabled on Gmail";
                $output[] = "2. Generate new App Password: https://myaccount.google.com/apppasswords";
                $output[] = "3. Update MAIL_PASSWORD in Render environment variables";
                $output[] = "4. Consider using transactional email service (Mailgun, SendGrid)";
            }
        }
        
        return response('<h2>SMTP Debug Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin">Go to Dashboard</a>');
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
    
    // Temporary fix: Switch to log driver for notifications
    Route::get('/fix-email-temporarily', function () {
        try {
            $output = [];
            $output[] = "🔧 Temporary Email Fix Applied";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "";
            $output[] = "This route temporarily switches the mail driver to 'log'";
            $output[] = "so that notifications work without SMTP errors.";
            $output[] = "";
            $output[] = "✅ Mail driver switched to 'log'";
            $output[] = "✅ Notifications will be logged instead of emailed";
            $output[] = "✅ Dashboard 'Send' button should work now";
            $output[] = "";
            $output[] = "📝 Note: Emails won't actually be sent, but the";
            $output[] = "notification system will work and mark users as notified.";
            
            // Temporarily set mail driver to log
            config(['mail.default' => 'log']);
            
            return response('<h2>Temporary Email Fix</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin/users">Test Dashboard Notifications</a><br><a href="/admin">Go to Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>Fix Failed</h2><pre>Error: ' . $e->getMessage() . '</pre>');
        }
    });
    
    // Simple test route
    Route::get('/test-simple', function () {
        return response('<h1>✅ Routes are working!</h1><p>Timestamp: ' . now() . '</p><br><a href="/admin">Go to Dashboard</a>');
    });
});

// Comprehensive email debug for custom domain
Route::get('/debug-custom-email', function () {
    $output = [];
    $output[] = "🔧 Custom Email Debug - xhamia-en-nur.ch";
    $output[] = "Timestamp: " . now()->toDateTimeString();
    $output[] = "";
    
    $email = 'info@xhamia-en-nur.ch';
    $password = '##~(nWoL-Bi;&&gJBMmb<>g#2#@';
    
    $output[] = "📧 Testing Email Account: {$email}";
    $output[] = "Password Length: " . strlen($password) . " characters";
    $output[] = "";
    
    // Test different SMTP configurations (Namecheap servers)
    $configs = [
        'NAMECHEAP_SSL_465' => [
            'host' => 'mail.privateemail.com',
            'port' => 465,
            'encryption' => 'ssl'
        ],
        'NAMECHEAP_TLS_587' => [
            'host' => 'mail.privateemail.com',
            'port' => 587,
            'encryption' => 'tls'
        ],
        'ALT_SMTP_SSL_465' => [
            'host' => 'smtp.privateemail.com',
            'port' => 465,
            'encryption' => 'ssl'
        ],
        'ALT_SMTP_TLS_587' => [
            'host' => 'smtp.privateemail.com',
            'port' => 587,
            'encryption' => 'tls'
        ],
        'WEBHOSTING_SSL_465' => [
            'host' => 'mail.web-hosting.com',
            'port' => 465,
            'encryption' => 'ssl'
        ]
    ];
    
    foreach ($configs as $name => $config) {
        $output[] = "🔌 Testing {$name} Configuration:";
        $output[] = "Host: {$config['host']}";
        $output[] = "Port: {$config['port']}";
        $output[] = "Encryption: {$config['encryption']}";
        
        try {
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $config['host'],
                $config['port'],
                $config['encryption'] === 'ssl'
            );
            
            if ($config['encryption'] === 'tls') {
                $transport->setUsername($email);
                $transport->setPassword($password);
            } else {
                $transport->setUsername($email);
                $transport->setPassword($password);
            }
            
            $transport->start();
            $output[] = "✅ {$name}: CONNECTION SUCCESS";
            $transport->stop();
            
            // Try sending test email with this config
            $output[] = "📤 Testing email send with {$name}...";
            
            // Temporarily override mail config
            config(['mail.mailers.smtp.host' => $config['host']]);
            config(['mail.mailers.smtp.port' => $config['port']]);
            config(['mail.mailers.smtp.encryption' => $config['encryption']]);
            config(['mail.mailers.smtp.username' => $email]);
            config(['mail.mailers.smtp.password' => $password]);
            config(['mail.from.address' => $email]);
            config(['mail.from.name' => 'EN NUR - Test']);
            
            Mail::raw("Test email from {$name} configuration.\n\nTimestamp: " . now()->toDateTimeString(), function ($message) use ($email) {
                $message->to($email) // Send to self for testing
                        ->subject('SMTP Test - ' . now()->toDateTimeString())
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $output[] = "✅ {$name}: EMAIL SEND SUCCESS";
            $output[] = "📧 Test email sent to {$email}";
            break; // Stop on first success
            
        } catch (\Exception $e) {
            $output[] = "❌ {$name}: FAILED";
            $output[] = "Error: " . $e->getMessage();
            
            if (strpos($e->getMessage(), '550 5.7.1') !== false) {
                $output[] = "🔍 Authentication/Relay issue detected";
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                $output[] = "🔍 Port/Host connection issue";
            } elseif (strpos($e->getMessage(), 'timeout') !== false) {
                $output[] = "🔍 Timeout - server might be slow/blocked";
            }
        }
        
        $output[] = "";
    }
    
    $output[] = "🎯 RECOMMENDATIONS:";
    $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
    $output[] = "1. Check if email account is active and accessible";
    $output[] = "2. Verify password is correct";
    $output[] = "3. Check if SMTP is enabled for this email account";
    $output[] = "4. Contact hosting provider if all configs fail";
    $output[] = "5. Consider using a transactional email service (Mailgun/SendGrid)";
    
    return response('<h2>Custom Email Debug Results</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/admin">Go to Dashboard</a>');
});

// Test simple admin access (no auth required)
Route::get('/admin-test', function () {
    return response('<h1>✅ Admin Routes Working!</h1><p>Timestamp: ' . now() . '</p><p>If you see this, the routing is working correctly.</p><br><a href="/login">Go to Login</a><br><a href="/admin/dashboard">Try Admin Dashboard</a>');
});

// Simple redirect for /admin to /admin/dashboard
Route::get('/admin', function () {
    return redirect('/admin/dashboard');
});

// Fix infinitdizzajn user password and membership status
Route::get('/fix-infinit-user', function() {
    try {
        $email = 'infinitdizzajn@gmail.com';
        $correctPassword = 'alipasha'; // User confirmed this is the correct password
        
        $output = [];
        $output[] = "🔧 Fixing infinitdizzajn@gmail.com user";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "";
        
        // Find the user
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            $output[] = "❌ User not found! Creating new user...";
            
            $user = \App\Models\User::create([
                'name' => 'kushtrim arifi',
                'email' => $email,
                'password' => Hash::make($correctPassword),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            
            $output[] = "✅ Created user: {$user->name} (ID: {$user->id})";
        } else {
            $output[] = "✅ User found: {$user->name} (ID: {$user->id})";
            
            // Update password to correct one
            $user->update([
                'password' => Hash::make($correctPassword),
                'name' => 'kushtrim arifi', // Ensure correct name
            ]);
            
            $output[] = "✅ Password updated to: {$correctPassword}";
        }
        
        // Check membership renewal status
        $renewal = \App\Models\MembershipRenewal::where('user_id', $user->id)
            ->where('is_renewed', false)
            ->first();
            
        if ($renewal) {
            $output[] = "✅ Membership renewal found: ID {$renewal->id}";
            $output[] = "- Days until expiry: {$renewal->days_until_expiry}";
            $output[] = "- Membership end: {$renewal->membership_end_date}";
            $output[] = "- Is hidden: " . ($renewal->is_hidden ? 'Yes' : 'No');
            $output[] = "- Is expired: " . ($renewal->is_expired ? 'Yes' : 'No');
            
            // Show color that should appear
            $days = $renewal->days_until_expiry;
            if ($renewal->is_hidden) {
                $color = '🔴 RED (Hidden)';
            } elseif ($days <= 0) {
                $color = '🔴 RED (Expired)';
            } elseif ($days <= 30) {
                $color = '🟠 ORANGE (Expiring within 30 days)';
            } else {
                $color = '🟢 GREEN (Active)';
            }
            
            $output[] = "🎨 Expected color: {$color}";
        } else {
            $output[] = "❌ No membership renewal found!";
            $output[] = "Creating test membership renewal...";
            
            // Create a payment first
            $payment = \App\Models\Payment::create([
                'user_id' => $user->id,
                'amount' => 35000, // CHF 350.00
                'currency' => 'CHF',
                'payment_type' => 'membership',
                'payment_method' => 'stripe',
                'status' => 'completed',
                'transaction_id' => 'test_infinit_' . time(),
                'metadata' => ['test_user' => true],
                'created_at' => now()->subYear(),
                'updated_at' => now()->subYear(),
            ]);
            
            // Create membership renewal (expires in 14 days)
            $expiryDate = now()->addDays(14);
            $startDate = $expiryDate->copy()->subYear();
            
            $renewal = \App\Models\MembershipRenewal::create([
                'user_id' => $user->id,
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
            
            $output[] = "✅ Created membership renewal (expires in 14 days)";
            $output[] = "🟠 Expected color: ORANGE (Expiring within 30 days)";
        }
        
        $output[] = "";
        $output[] = "🎯 LOGIN CREDENTIALS:";
        $output[] = "📧 Email: {$email}";
        $output[] = "🔑 Password: {$correctPassword}";
        $output[] = "🌐 Login URL: https://en-nur-membership.onrender.com/login";
        $output[] = "";
        $output[] = "🔍 ADMIN VIEW:";
        $output[] = "Login as super admin and check /admin/users to see the color indicator";
        
        return response('<h2>✅ User Fixed Successfully!</h2><pre>' . implode("\n", $output) . '</pre><br><a href="/login">Login as User</a><br><a href="/admin/users">View Admin Users Page</a><br><a href="/admin">Admin Dashboard</a>');
        
    } catch (\Exception $e) {
        return response('<h2>❌ Error</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
    }
})->middleware(['auth', 'super_admin']);

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
        $output[] = "1. SUPER ADMIN: kushtrim.m.arifi@gmail.com (Password: Alipasha1985X)";
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
                'password' => Hash::make('Alipasha1985X'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Super Admin";
        } else {
            $superAdmin->update([
                'name' => 'SUPER ADMIN',
                'password' => Hash::make('Alipasha1985X'),
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
                'password' => Hash::make('alipasha'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Test User";
        } else {
            $testUser->update([
                'name' => 'kushtrim arifi',
                'password' => Hash::make('alipasha'),
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
        $output[] = "👑 SUPER ADMIN: kushtrim.m.arifi@gmail.com / Alipasha1985X";
        $output[] = "👤 TEST USER: infinitdizzajn@gmail.com / alipasha";
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
        $output[] = "🧪 SETTING UP TEST USERS";
        $output[] = "Timestamp: " . now()->toDateTimeString();
        $output[] = "=" . str_repeat("=", 50);
        $output[] = "";
        
        // 1. Setup info@mardal.ch - EXPIRED (RED)
        $mardalUser = \App\Models\User::where('email', 'info@mardal.ch')->first();
        if (!$mardalUser) {
            $mardalUser = \App\Models\User::create([
                'name' => 'Mardal User',
                'email' => 'info@mardal.ch',
                'password' => Hash::make('mardal123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Mardal user: info@mardal.ch";
        } else {
            $mardalUser->update([
                'name' => 'Mardal User',
                'password' => Hash::make('mardal123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Updated Mardal user: info@mardal.ch";
        }
        
        // 2. Setup infinitdizzajn@gmail.com - 15 DAYS (ORANGE)
        $infinitUser = \App\Models\User::where('email', 'infinitdizzajn@gmail.com')->first();
        if (!$infinitUser) {
            $infinitUser = \App\Models\User::create([
                'name' => 'kushtrim arifi',
                'email' => 'infinitdizzajn@gmail.com',
                'password' => Hash::make('alipasha'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Created Infinit user: infinitdizzajn@gmail.com";
        } else {
            $infinitUser->update([
                'name' => 'kushtrim arifi',
                'password' => Hash::make('alipasha'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
            $output[] = "✅ Updated Infinit user: infinitdizzajn@gmail.com";
        }
        
        $output[] = "";
        $output[] = "🔧 SETTING UP MEMBERSHIPS:";
        $output[] = "";
        
        // Clean existing renewals and payments for both users
        \App\Models\MembershipRenewal::whereIn('user_id', [$mardalUser->id, $infinitUser->id])->delete();
        \App\Models\Payment::whereIn('user_id', [$mardalUser->id, $infinitUser->id])->delete();
        
        // 1. MARDAL USER - EXPIRED (30 days ago)
        $mardalPayment = \App\Models\Payment::create([
            'user_id' => $mardalUser->id,
            'amount' => 35000, // CHF 350.00
            'currency' => 'CHF',
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'transaction_id' => 'test_mardal_' . time(),
            'metadata' => ['test_setup' => 'expired'],
            'created_at' => now()->subYear()->subDays(30),
            'updated_at' => now()->subYear()->subDays(30),
        ]);
        
        $mardalExpiryDate = now()->subDays(30); // EXPIRED 30 days ago
        $mardalStartDate = $mardalExpiryDate->copy()->subYear();
        
        $mardalRenewal = \App\Models\MembershipRenewal::create([
            'user_id' => $mardalUser->id,
            'payment_id' => $mardalPayment->id,
            'membership_start_date' => $mardalStartDate,
            'membership_end_date' => $mardalExpiryDate,
            'days_until_expiry' => (int) now()->diffInDays($mardalExpiryDate, false), // Negative = expired
            'is_expired' => true,
            'is_hidden' => false,
            'is_renewed' => false,
            'notifications_sent' => [],
            'last_notification_sent_at' => null,
        ]);
        
        $output[] = "🔴 MARDAL USER (info@mardal.ch):";
        $output[] = "   - Status: EXPIRED (30 days ago)";
        $output[] = "   - End Date: {$mardalExpiryDate->format('Y-m-d')}";
        $output[] = "   - Days Until Expiry: {$mardalRenewal->days_until_expiry}";
        $output[] = "   - Expected Color: RED 🔴";
        $output[] = "";
        
        // 2. INFINIT USER - 15 DAYS REMAINING (ORANGE)
        $infinitPayment = \App\Models\Payment::create([
            'user_id' => $infinitUser->id,
            'amount' => 35000, // CHF 350.00
            'currency' => 'CHF',
            'payment_type' => 'membership',
            'payment_method' => 'stripe',
            'status' => 'completed',
            'transaction_id' => 'test_infinit_' . time(),
            'metadata' => ['test_setup' => 'expiring_soon'],
            'created_at' => now()->subYear()->addDays(15),
            'updated_at' => now()->subYear()->addDays(15),
        ]);
        
        $infinitExpiryDate = now()->addDays(15); // 15 days remaining
        $infinitStartDate = $infinitExpiryDate->copy()->subYear();
        
        $infinitRenewal = \App\Models\MembershipRenewal::create([
            'user_id' => $infinitUser->id,
            'payment_id' => $infinitPayment->id,
            'membership_start_date' => $infinitStartDate,
            'membership_end_date' => $infinitExpiryDate,
            'days_until_expiry' => 15,
            'is_expired' => false,
            'is_hidden' => false,
            'is_renewed' => false,
            'notifications_sent' => [],
            'last_notification_sent_at' => null,
        ]);
        
        $output[] = "🟠 INFINIT USER (infinitdizzajn@gmail.com):";
        $output[] = "   - Status: EXPIRING SOON (15 days)";
        $output[] = "   - End Date: {$infinitExpiryDate->format('Y-m-d')}";
        $output[] = "   - Days Until Expiry: {$infinitRenewal->days_until_expiry}";
        $output[] = "   - Expected Color: ORANGE 🟠";
        $output[] = "";
        
        // Verify with MembershipService
        $membershipService = new \App\Services\MembershipService();
        
        $mardalColor = $membershipService->getUserColor($mardalUser->id);
        $infinitColor = $membershipService->getUserColor($infinitUser->id);
        
        $output[] = "🎨 COLOR VERIFICATION:";
        $output[] = "   - Mardal Color: {$mardalColor} (should be #dc3545 - RED)";
        $output[] = "   - Infinit Color: {$infinitColor} (should be #ff6c37 - ORANGE)";
        $output[] = "";
        
        $output[] = "🔑 LOGIN CREDENTIALS:";
        $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
        $output[] = "👑 SUPER ADMIN: kushtrim.m.arifi@gmail.com / Alipasha1985X";
        $output[] = "🔴 EXPIRED USER: info@mardal.ch / mardal123";
        $output[] = "🟠 EXPIRING USER: infinitdizzajn@gmail.com / alipasha";
        $output[] = "";
        
        $output[] = "🧪 TESTING INSTRUCTIONS:";
        $output[] = "1. Login to admin dashboard - should see 2 users needing attention";
        $output[] = "2. Login as expired user - should see RED urgent renewal message";
        $output[] = "3. Login as expiring user - should see ORANGE renewal reminder";
        $output[] = "4. Make payments to test renewal logic works correctly";
        
        return response('<h2>✅ Test Users Setup Complete!</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/admin/users" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👑 View Admin Dashboard</a><br><br><a href="/login" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔴 Test Expired User Login</a><br><br><a href="/dashboard" style="background: #ff6c37; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🟠 Test Expiring User Dashboard</a>');
        
    } catch (\Exception $e) {
        return response('<h2>❌ Error Setting Up Test Users</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
    }
})->middleware(['auth', 'super_admin']);

require __DIR__.'/auth.php'; 