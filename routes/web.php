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

require __DIR__.'/auth.php'; 