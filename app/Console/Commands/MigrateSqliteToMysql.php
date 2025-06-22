<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class MigrateSqliteToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:sqlite-to-mysql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from SQLite to MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting data migration from SQLite to MySQL...');
        
        try {
            // Create SQLite connection
            $sqlitePath = database_path('database.sqlite');
            if (!file_exists($sqlitePath)) {
                $this->error('❌ SQLite database file not found: ' . $sqlitePath);
                return 1;
            }
            
            $sqlitePdo = new PDO('sqlite:' . $sqlitePath);
            $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Migrate Users
            $this->info('👥 Migrating users...');
            $stmt = $sqlitePdo->query('SELECT * FROM users');
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                DB::table('users')->insert([
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'email_verified_at' => $user['email_verified_at'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'remember_token' => $user['remember_token'],
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at'],
                ]);
            }
            $this->info('✅ Migrated ' . count($users) . ' users');
            
            // Migrate Payments
            $this->info('💳 Migrating payments...');
            $stmt = $sqlitePdo->query('SELECT * FROM payments');
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($payments as $payment) {
                DB::table('payments')->insert([
                    'id' => $payment['id'],
                    'user_id' => $payment['user_id'],
                    'payment_type' => $payment['payment_type'],
                    'amount' => $payment['amount'],
                    'currency' => $payment['currency'],
                    'status' => $payment['status'],
                    'transaction_id' => $payment['transaction_id'],
                    'payment_method' => $payment['payment_method'],
                    'metadata' => $payment['metadata'],
                    'created_at' => $payment['created_at'],
                    'updated_at' => $payment['updated_at'],
                ]);
            }
            $this->info('✅ Migrated ' . count($payments) . ' payments');
            
            // Verify migration
            $this->info('🔍 Verifying migration...');
            $mysqlUserCount = DB::table('users')->count();
            $mysqlPaymentCount = DB::table('payments')->count();
            
            $this->info("MySQL Users: {$mysqlUserCount}");
            $this->info("MySQL Payments: {$mysqlPaymentCount}");
            
            if ($mysqlUserCount == count($users) && $mysqlPaymentCount == count($payments)) {
                $this->info('🎉 Data migration completed successfully!');
                $this->info('✅ All data has been transferred from SQLite to MySQL');
                
                // Update auto-increment values
                $maxUserId = DB::table('users')->max('id');
                $maxPaymentId = DB::table('payments')->max('id');
                
                DB::statement("ALTER TABLE users AUTO_INCREMENT = " . ($maxUserId + 1));
                DB::statement("ALTER TABLE payments AUTO_INCREMENT = " . ($maxPaymentId + 1));
                
                $this->info('✅ Updated auto-increment values');
                
                return 0;
            } else {
                $this->error('❌ Migration verification failed!');
                $this->error("Expected Users: " . count($users) . ", Got: {$mysqlUserCount}");
                $this->error("Expected Payments: " . count($payments) . ", Got: {$mysqlPaymentCount}");
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
