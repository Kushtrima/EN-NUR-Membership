<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Payment;

class MonitorDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:monitor 
                            {--alert : Send alerts if issues detected}
                            {--email= : Email address for alerts}
                            {--threshold=1000 : Response time threshold in milliseconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor database health, performance, and send alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Starting database monitoring...');
        
        $report = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'connection_status' => $this->checkConnection(),
            'performance_metrics' => $this->checkPerformance(),
            'data_integrity' => $this->checkDataIntegrity(),
            'disk_usage' => $this->checkDiskUsage(),
            'backup_status' => $this->checkBackupStatus(),
            'recent_activity' => $this->checkRecentActivity(),
        ];
        
        // Display report
        $this->displayReport($report);
        
        // Check for issues and send alerts if needed
        $issues = $this->analyzeIssues($report);
        if (!empty($issues) && $this->option('alert')) {
            $this->sendAlerts($issues, $report);
        }
        
        // Log monitoring results
        Log::info('Database monitoring completed', $report);
        
        return 0;
    }
    
    /**
     * Check database connection.
     */
    private function checkConnection()
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $responseTime = (microtime(true) - $start) * 1000;
            
            return [
                'status' => 'connected',
                'response_time_ms' => round($responseTime, 2),
                'connection' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check database performance metrics.
     */
    private function checkPerformance()
    {
        try {
            $metrics = [];
            
            // Test query performance
            $start = microtime(true);
            $userCount = User::count();
            $userQueryTime = (microtime(true) - $start) * 1000;
            
            $start = microtime(true);
            $paymentCount = Payment::count();
            $paymentQueryTime = (microtime(true) - $start) * 1000;
            
            // Complex query test
            $start = microtime(true);
            $recentPayments = Payment::with('user')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count();
            $complexQueryTime = (microtime(true) - $start) * 1000;
            
            return [
                'user_count_query_ms' => round($userQueryTime, 2),
                'payment_count_query_ms' => round($paymentQueryTime, 2),
                'complex_query_ms' => round($complexQueryTime, 2),
                'average_response_ms' => round(($userQueryTime + $paymentQueryTime + $complexQueryTime) / 3, 2),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check data integrity.
     */
    private function checkDataIntegrity()
    {
        try {
            $integrity = [];
            
            // Check for orphaned payments
            $orphanedPayments = Payment::whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('users')
                      ->whereRaw('users.id = payments.user_id');
            })->count();
            
            // Check for users without payments
            $usersWithoutPayments = User::whereDoesntHave('payments')->count();
            
            // Check for duplicate emails
            $duplicateEmails = User::select('email')
                ->groupBy('email')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            
            // Check payment status consistency
            $invalidPayments = Payment::whereNotIn('status', ['pending', 'completed', 'failed', 'cancelled'])->count();
            
            return [
                'orphaned_payments' => $orphanedPayments,
                'users_without_payments' => $usersWithoutPayments,
                'duplicate_emails' => $duplicateEmails,
                'invalid_payment_status' => $invalidPayments,
                'integrity_score' => $this->calculateIntegrityScore([
                    $orphanedPayments, $duplicateEmails, $invalidPayments
                ]),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check disk usage.
     */
    private function checkDiskUsage()
    {
        try {
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");
            
            if ($config['driver'] === 'sqlite') {
                $dbFile = $config['database'];
                if (file_exists($dbFile)) {
                    $size = filesize($dbFile);
                    $diskFree = disk_free_space(dirname($dbFile));
                    $diskTotal = disk_total_space(dirname($dbFile));
                    
                    return [
                        'database_size_bytes' => $size,
                        'database_size_mb' => round($size / 1024 / 1024, 2),
                        'disk_free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
                        'disk_usage_percent' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 2),
                    ];
                }
            }
            
            return [
                'message' => 'Disk usage monitoring not available for this database type',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check backup status.
     */
    private function checkBackupStatus()
    {
        try {
            $backupPath = storage_path('app/backups');
            $backups = glob($backupPath . '/backup_*');
            
            if (empty($backups)) {
                return [
                    'status' => 'no_backups',
                    'last_backup' => null,
                    'backup_count' => 0,
                ];
            }
            
            // Get latest backup
            usort($backups, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $latestBackup = $backups[0];
            $lastBackupTime = Carbon::createFromTimestamp(filemtime($latestBackup));
            $hoursSinceBackup = $lastBackupTime->diffInHours(Carbon::now());
            
            return [
                'status' => $hoursSinceBackup > 24 ? 'outdated' : 'recent',
                'last_backup' => $lastBackupTime->toDateTimeString(),
                'hours_since_backup' => $hoursSinceBackup,
                'backup_count' => count($backups),
                'latest_backup_size' => $this->formatBytes(filesize($latestBackup)),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check recent activity.
     */
    private function checkRecentActivity()
    {
        try {
            $now = Carbon::now();
            
            return [
                'new_users_today' => User::whereDate('created_at', $now->toDateString())->count(),
                'new_users_week' => User::where('created_at', '>=', $now->subDays(7))->count(),
                'payments_today' => Payment::whereDate('created_at', $now->toDateString())->count(),
                'payments_week' => Payment::where('created_at', '>=', $now->subDays(7))->count(),
                'failed_payments_today' => Payment::whereDate('created_at', $now->toDateString())
                    ->where('status', 'failed')->count(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Display monitoring report.
     */
    private function displayReport($report)
    {
        $this->info("\n📋 Database Monitoring Report");
        $this->info("Generated: " . $report['timestamp']);
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        // Connection Status
        $connection = $report['connection_status'];
        $status = $connection['status'] === 'connected' ? '✅' : '❌';
        $this->info("\n🔗 Connection Status: {$status} " . ucfirst($connection['status']));
        if (isset($connection['response_time_ms'])) {
            $this->info("⚡ Response Time: {$connection['response_time_ms']}ms");
        }
        
        // Performance Metrics
        if (isset($report['performance_metrics']['average_response_ms'])) {
            $perf = $report['performance_metrics'];
            $this->info("\n📈 Performance Metrics:");
            $this->info("   Average Query Time: {$perf['average_response_ms']}ms");
            $this->info("   Complex Query Time: {$perf['complex_query_ms']}ms");
        }
        
        // Data Integrity
        if (isset($report['data_integrity']['integrity_score'])) {
            $integrity = $report['data_integrity'];
            $score = $integrity['integrity_score'];
            $scoreIcon = $score >= 95 ? '✅' : ($score >= 80 ? '⚠️' : '❌');
            $this->info("\n🔍 Data Integrity: {$scoreIcon} {$score}%");
            if ($integrity['orphaned_payments'] > 0) {
                $this->warn("   ⚠️  {$integrity['orphaned_payments']} orphaned payments");
            }
            if ($integrity['duplicate_emails'] > 0) {
                $this->warn("   ⚠️  {$integrity['duplicate_emails']} duplicate emails");
            }
        }
        
        // Disk Usage
        if (isset($report['disk_usage']['database_size_mb'])) {
            $disk = $report['disk_usage'];
            $this->info("\n💾 Disk Usage:");
            $this->info("   Database Size: {$disk['database_size_mb']} MB");
            $this->info("   Free Space: {$disk['disk_free_gb']} GB");
            $this->info("   Disk Usage: {$disk['disk_usage_percent']}%");
        }
        
        // Backup Status
        $backup = $report['backup_status'];
        $backupIcon = $backup['status'] === 'recent' ? '✅' : '⚠️';
        $this->info("\n💾 Backup Status: {$backupIcon} " . ucfirst($backup['status']));
        if (isset($backup['hours_since_backup'])) {
            $this->info("   Last Backup: {$backup['hours_since_backup']} hours ago");
            $this->info("   Backup Count: {$backup['backup_count']}");
        }
        
        // Recent Activity
        $activity = $report['recent_activity'];
        $this->info("\n📊 Recent Activity:");
        $this->info("   New Users Today: {$activity['new_users_today']}");
        $this->info("   Payments Today: {$activity['payments_today']}");
        if ($activity['failed_payments_today'] > 0) {
            $this->warn("   ⚠️  Failed Payments Today: {$activity['failed_payments_today']}");
        }
        
        $this->line("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
    
    /**
     * Analyze issues from the report.
     */
    private function analyzeIssues($report)
    {
        $issues = [];
        
        // Connection issues
        if ($report['connection_status']['status'] !== 'connected') {
            $issues[] = 'Database connection failed';
        }
        
        // Performance issues
        if (isset($report['performance_metrics']['average_response_ms'])) {
            $threshold = $this->option('threshold');
            if ($report['performance_metrics']['average_response_ms'] > $threshold) {
                $issues[] = "Slow query performance: {$report['performance_metrics']['average_response_ms']}ms (threshold: {$threshold}ms)";
            }
        }
        
        // Data integrity issues
        if (isset($report['data_integrity']['integrity_score'])) {
            if ($report['data_integrity']['integrity_score'] < 90) {
                $issues[] = "Data integrity issues detected";
            }
        }
        
        // Backup issues
        if ($report['backup_status']['status'] === 'no_backups') {
            $issues[] = "No database backups found";
        } elseif ($report['backup_status']['status'] === 'outdated') {
            $issues[] = "Database backup is outdated (>24 hours)";
        }
        
        // Disk space issues
        if (isset($report['disk_usage']['disk_usage_percent'])) {
            if ($report['disk_usage']['disk_usage_percent'] > 90) {
                $issues[] = "Disk usage is high: {$report['disk_usage']['disk_usage_percent']}%";
            }
        }
        
        return $issues;
    }
    
    /**
     * Send alerts for detected issues.
     */
    private function sendAlerts($issues, $report)
    {
        $email = $this->option('email') ?: config('mail.from.address');
        
        if (!$email) {
            $this->warn("⚠️  No email address provided for alerts");
            return;
        }
        
        $this->warn("🚨 " . count($issues) . " issue(s) detected:");
        foreach ($issues as $issue) {
            $this->warn("   • {$issue}");
        }
        
        // Here you would send an email alert
        // For now, we'll just log it
        Log::warning('Database monitoring alerts', [
            'issues' => $issues,
            'report' => $report,
            'email' => $email,
        ]);
        
        $this->info("📧 Alert logged (email functionality can be added)");
    }
    
    /**
     * Calculate integrity score.
     */
    private function calculateIntegrityScore($issues)
    {
        $totalIssues = array_sum($issues);
        if ($totalIssues === 0) {
            return 100;
        }
        
        // Simple scoring: reduce score based on issues
        $score = 100 - min($totalIssues * 10, 50);
        return max($score, 0);
    }
    
    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
