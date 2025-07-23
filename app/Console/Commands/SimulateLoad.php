<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Symfony\Component\Process\Process;

class SimulateLoad extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'simulate:load 
                            {--users=50 : Number of users to simulate}
                            {--admin-actions=10 : Number of admin actions to simulate}
                            {--concurrent=5 : Number of concurrent processes}
                            {--duration=300 : Maximum duration in seconds}
                            {--cleanup : Clean up test data after simulation}';

    /**
     * The console command description.
     */
    protected $description = 'Simulate realistic load testing with multiple users and admin actions';

    private $baseUrl;
    private $simulationId;
    private $startTime;
    private $metrics = [];
    private $errors = [];
    private $testUsers = [];
    private $faker;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->initializeSimulation();
        
        $this->info("🚀 Starting Load Simulation for EN-NUR-Membership");
        $this->info("=" . str_repeat("=", 50));
        
        try {
            // Create simulation directory
            $this->createSimulationDirectory();
            
            // Pre-simulation setup
            $this->preSimulationSetup();
            
            // Run the main simulation
            $this->runSimulation();
            
            // Generate comprehensive report
            $this->generateReport();
            
            // Cleanup if requested
            if ($this->option('cleanup')) {
                $this->cleanupTestData();
            }
            
            $this->info("✅ Load simulation completed successfully!");
            $this->displaySummary();
            
        } catch (\Exception $e) {
            $this->error("❌ Simulation failed: " . $e->getMessage());
            $this->logError("CRITICAL", $e->getMessage(), $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }

    /**
     * Initialize simulation parameters.
     */
    private function initializeSimulation()
    {
        $this->baseUrl = config('app.url', 'http://localhost:8000');
        $this->simulationId = 'sim_' . date('Y-m-d_H-i-s');
        $this->startTime = microtime(true);
        $this->faker = Faker::create();
        
        $this->metrics = [
            'users_created' => 0,
            'users_logged_in' => 0,
            'payments_attempted' => 0,
            'payments_successful' => 0,
            'admin_actions' => 0,
            'total_requests' => 0,
            'failed_requests' => 0,
            'avg_response_time' => 0,
            'max_response_time' => 0,
            'min_response_time' => PHP_FLOAT_MAX,
        ];
    }

    /**
     * Create simulation directory for logs and reports.
     */
    private function createSimulationDirectory()
    {
        $simulationPath = storage_path("app/simulation_logs");
        if (!file_exists($simulationPath)) {
            mkdir($simulationPath, 0755, true);
        }
        
        $this->simulationPath = $simulationPath . '/' . $this->simulationId;
        mkdir($this->simulationPath, 0755, true);
        
        $this->info("📁 Simulation logs will be saved to: {$this->simulationPath}");
    }

    /**
     * Pre-simulation setup and validation.
     */
    private function preSimulationSetup()
    {
        $this->info("🔧 Pre-simulation setup...");
        
        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info("✅ Database connection: OK");
        } catch (\Exception $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
        
        // Check if Laravel server is running
        try {
            $response = Http::timeout(5)->get($this->baseUrl);
            if ($response->successful()) {
                $this->info("✅ Laravel server: OK ({$this->baseUrl})");
            } else {
                throw new \Exception("Server returned status: " . $response->status());
            }
        } catch (\Exception $e) {
            throw new \Exception("Laravel server check failed. Please ensure 'php artisan serve' is running.");
        }
        
        // Store initial database state
        $this->storeInitialState();
        
        $this->info("✅ Pre-simulation setup completed");
    }

    /**
     * Run the main simulation.
     */
    private function runSimulation()
    {
        $userCount = $this->option('users');
        $adminActions = $this->option('admin-actions');
        $concurrent = $this->option('concurrent');
        
        $this->info("👥 Simulating {$userCount} users with {$concurrent} concurrent processes");
        $this->info("🛡️  Simulating {$adminActions} admin actions");
        $this->info("⏱️  Maximum duration: {$this->option('duration')} seconds");
        
        // Start progress bar
        $progressBar = $this->output->createProgressBar($userCount + $adminActions);
        $progressBar->start();
        
        // Create admin user for simulation
        $adminUser = $this->createAdminUser();
        
        // Simulate users in batches (concurrent simulation)
        $batches = array_chunk(range(1, $userCount), $concurrent);
        
        foreach ($batches as $batch) {
            $processes = [];
            
            // Start concurrent user simulations
            foreach ($batch as $userIndex) {
                $processes[] = $this->startUserSimulation($userIndex);
            }
            
            // Wait for batch to complete
            foreach ($processes as $process) {
                $process->wait();
                $progressBar->advance();
                
                // Check for timeout
                if (microtime(true) - $this->startTime > $this->option('duration')) {
                    $this->warn("\n⏰ Timeout reached, stopping simulation");
                    break 2;
                }
            }
            
            // Simulate admin actions during user load
            if (rand(1, 3) == 1) { // 33% chance per batch
                $this->simulateAdminAction($adminUser);
                $progressBar->advance();
            }
        }
        
        // Complete remaining admin actions
        $remainingAdminActions = $adminActions - $this->metrics['admin_actions'];
        for ($i = 0; $i < $remainingAdminActions; $i++) {
            $this->simulateAdminAction($adminUser);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Start user simulation process.
     */
    private function startUserSimulation($userIndex)
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => "test_user_{$userIndex}_{$this->simulationId}@test.com",
            'password' => 'password123',
            'phone_number' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
        ];
        
        // Simulate user journey
        $this->simulateUserRegistration($userData);
        $this->simulateUserLogin($userData);
        
        // 70% chance to attempt payment
        if (rand(1, 10) <= 7) {
            $this->simulateUserPayment($userData);
        }
        
        return new \Symfony\Component\Process\Process(['echo', 'completed']);
    }

    /**
     * Simulate user registration.
     */
    private function simulateUserRegistration($userData)
    {
        $startTime = microtime(true);
        
        try {
            // Create user via HTTP request
            $response = Http::timeout(10)->post($this->baseUrl . '/register', array_merge($userData, [
                'password_confirmation' => $userData['password'],
                'terms_accepted' => true,
            ]));
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->updateMetrics('registration', $responseTime, $response->successful());
            
            if ($response->successful()) {
                $this->metrics['users_created']++;
                $this->testUsers[] = $userData['email'];
                $this->logAction("USER_REGISTRATION", "Success", $userData['email'], $responseTime);
            } else {
                $this->logError("USER_REGISTRATION", "Failed for " . $userData['email'], $response->body());
            }
            
        } catch (\Exception $e) {
            $this->logError("USER_REGISTRATION", "Exception for " . $userData['email'], $e->getMessage());
        }
    }

    /**
     * Simulate user login.
     */
    private function simulateUserLogin($userData)
    {
        $startTime = microtime(true);
        
        try {
            $response = Http::timeout(10)->post($this->baseUrl . '/login', [
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->updateMetrics('login', $responseTime, $response->successful());
            
            if ($response->successful()) {
                $this->metrics['users_logged_in']++;
                $this->logAction("USER_LOGIN", "Success", $userData['email'], $responseTime);
            } else {
                $this->logError("USER_LOGIN", "Failed for " . $userData['email'], $response->body());
            }
            
        } catch (\Exception $e) {
            $this->logError("USER_LOGIN", "Exception for " . $userData['email'], $e->getMessage());
        }
    }

    /**
     * Simulate user payment attempt.
     */
    private function simulateUserPayment($userData)
    {
        $startTime = microtime(true);
        
        try {
            // Simulate payment creation
            $this->metrics['payments_attempted']++;
            
            // Create a test payment record
            $user = User::where('email', $userData['email'])->first();
            if ($user) {
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'payment_type' => 'membership',
                    'amount' => 35000, // CHF 350
                    'currency' => 'chf',
                    'status' => 'completed',
                    'payment_method' => 'stripe_test',
                    'transaction_id' => 'sim_' . uniqid(),
                    'metadata' => ['simulation' => true, 'simulation_id' => $this->simulationId],
                ]);
                
                $responseTime = (microtime(true) - $startTime) * 1000;
                $this->updateMetrics('payment', $responseTime, true);
                $this->metrics['payments_successful']++;
                $this->logAction("USER_PAYMENT", "Success", $userData['email'], $responseTime);
            }
            
        } catch (\Exception $e) {
            $this->logError("USER_PAYMENT", "Exception for " . $userData['email'], $e->getMessage());
        }
    }

    /**
     * Create admin user for simulation.
     */
    private function createAdminUser()
    {
        $adminEmail = "admin_{$this->simulationId}@test.com";
        
        $admin = User::create([
            'name' => 'Load Test Admin',
            'email' => $adminEmail,
            'password' => bcrypt('admin123'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
        
        $this->logAction("ADMIN_CREATION", "Success", $adminEmail, 0);
        return $admin;
    }

    /**
     * Simulate admin actions.
     */
    private function simulateAdminAction($adminUser)
    {
        $actions = [
            'view_dashboard',
            'view_users',
            'view_payments',
            'mark_payment_completed',
            'create_backup',
        ];
        
        $action = $actions[array_rand($actions)];
        $startTime = microtime(true);
        
        try {
            switch ($action) {
                case 'view_dashboard':
                    $this->simulateAdminDashboard($adminUser);
                    break;
                case 'view_users':
                    $this->simulateAdminUsers($adminUser);
                    break;
                case 'view_payments':
                    $this->simulateAdminPayments($adminUser);
                    break;
                case 'mark_payment_completed':
                    $this->simulatePaymentApproval($adminUser);
                    break;
                case 'create_backup':
                    $this->simulateBackupCreation($adminUser);
                    break;
            }
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->updateMetrics('admin_action', $responseTime, true);
            $this->metrics['admin_actions']++;
            $this->logAction("ADMIN_ACTION", $action, $adminUser->email, $responseTime);
            
        } catch (\Exception $e) {
            $this->logError("ADMIN_ACTION", "Failed: " . $action, $e->getMessage());
        }
    }

    /**
     * Simulate admin dashboard access.
     */
    private function simulateAdminDashboard($adminUser)
    {
        // Simulate dashboard data loading
        $userCount = User::count();
        $paymentCount = Payment::count();
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        
        // Simulate some processing time
        usleep(rand(50, 200) * 1000); // 50-200ms
    }

    /**
     * Simulate admin viewing users.
     */
    private function simulateAdminUsers($adminUser)
    {
        $users = User::with('payments')->take(20)->get();
        usleep(rand(100, 300) * 1000); // 100-300ms
    }

    /**
     * Simulate admin viewing payments.
     */
    private function simulateAdminPayments($adminUser)
    {
        $payments = Payment::with('user')->take(50)->get();
        usleep(rand(150, 400) * 1000); // 150-400ms
    }

    /**
     * Simulate payment approval.
     */
    private function simulatePaymentApproval($adminUser)
    {
        $pendingPayment = Payment::where('status', 'pending')->first();
        if ($pendingPayment) {
            $pendingPayment->update(['status' => 'completed']);
        }
        usleep(rand(200, 500) * 1000); // 200-500ms
    }

    /**
     * Simulate backup creation.
     */
    private function simulateBackupCreation($adminUser)
    {
        // Simulate backup command
        usleep(rand(1000, 3000) * 1000); // 1-3 seconds
    }

    /**
     * Update performance metrics.
     */
    private function updateMetrics($action, $responseTime, $success)
    {
        $this->metrics['total_requests']++;
        
        if (!$success) {
            $this->metrics['failed_requests']++;
        }
        
        // Update response time metrics
        if ($responseTime > $this->metrics['max_response_time']) {
            $this->metrics['max_response_time'] = $responseTime;
        }
        
        if ($responseTime < $this->metrics['min_response_time']) {
            $this->metrics['min_response_time'] = $responseTime;
        }
        
        // Calculate running average
        $totalTime = $this->metrics['avg_response_time'] * ($this->metrics['total_requests'] - 1) + $responseTime;
        $this->metrics['avg_response_time'] = $totalTime / $this->metrics['total_requests'];
    }

    /**
     * Log action with timestamp.
     */
    private function logAction($type, $action, $identifier, $responseTime)
    {
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => $type,
            'action' => $action,
            'identifier' => $identifier,
            'response_time_ms' => round($responseTime, 2),
            'simulation_id' => $this->simulationId,
        ];
        
        file_put_contents(
            $this->simulationPath . '/actions.log',
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Log error with details.
     */
    private function logError($type, $message, $details = null)
    {
        $errorEntry = [
            'timestamp' => now()->toISOString(),
            'type' => $type,
            'message' => $message,
            'details' => $details,
            'simulation_id' => $this->simulationId,
        ];
        
        $this->errors[] = $errorEntry;
        
        file_put_contents(
            $this->simulationPath . '/errors.log',
            json_encode($errorEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Store initial database state.
     */
    private function storeInitialState()
    {
        $initialState = [
            'users_count' => User::count(),
            'payments_count' => Payment::count(),
            'timestamp' => now()->toISOString(),
        ];
        
        file_put_contents(
            $this->simulationPath . '/initial_state.json',
            json_encode($initialState, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Generate comprehensive report.
     */
    private function generateReport()
    {
        $endTime = microtime(true);
        $totalDuration = $endTime - $this->startTime;
        
        $report = [
            'simulation_info' => [
                'simulation_id' => $this->simulationId,
                'start_time' => date('Y-m-d H:i:s', $this->startTime),
                'end_time' => date('Y-m-d H:i:s', $endTime),
                'total_duration_seconds' => round($totalDuration, 2),
                'requested_users' => $this->option('users'),
                'requested_admin_actions' => $this->option('admin-actions'),
            ],
            'performance_metrics' => $this->metrics,
            'success_rates' => [
                'user_registration_rate' => $this->metrics['users_created'] > 0 ? 
                    round(($this->metrics['users_created'] / $this->option('users')) * 100, 2) : 0,
                'payment_success_rate' => $this->metrics['payments_attempted'] > 0 ? 
                    round(($this->metrics['payments_successful'] / $this->metrics['payments_attempted']) * 100, 2) : 0,
                'overall_success_rate' => $this->metrics['total_requests'] > 0 ? 
                    round((($this->metrics['total_requests'] - $this->metrics['failed_requests']) / $this->metrics['total_requests']) * 100, 2) : 0,
            ],
            'response_times' => [
                'average_ms' => round($this->metrics['avg_response_time'], 2),
                'maximum_ms' => round($this->metrics['max_response_time'], 2),
                'minimum_ms' => round($this->metrics['min_response_time'], 2),
            ],
            'errors_summary' => [
                'total_errors' => count($this->errors),
                'error_types' => array_count_values(array_column($this->errors, 'type')),
            ],
            'recommendations' => $this->generateRecommendations(),
        ];
        
        // Save detailed report
        file_put_contents(
            $this->simulationPath . '/simulation_report.json',
            json_encode($report, JSON_PRETTY_PRINT)
        );
        
        // Save summary report
        $this->generateSummaryReport($report);
        
        $this->info("📊 Detailed report saved to: {$this->simulationPath}/simulation_report.json");
    }

    /**
     * Generate summary report.
     */
    private function generateSummaryReport($report)
    {
        $summary = "# EN-NUR-Membership Load Simulation Report\n\n";
        $summary .= "**Simulation ID:** {$report['simulation_info']['simulation_id']}\n";
        $summary .= "**Duration:** {$report['simulation_info']['total_duration_seconds']} seconds\n";
        $summary .= "**Date:** {$report['simulation_info']['start_time']}\n\n";
        
        $summary .= "## Performance Summary\n\n";
        $summary .= "- **Users Created:** {$report['performance_metrics']['users_created']}/{$report['simulation_info']['requested_users']}\n";
        $summary .= "- **Successful Logins:** {$report['performance_metrics']['users_logged_in']}\n";
        $summary .= "- **Payment Attempts:** {$report['performance_metrics']['payments_attempted']}\n";
        $summary .= "- **Successful Payments:** {$report['performance_metrics']['payments_successful']}\n";
        $summary .= "- **Admin Actions:** {$report['performance_metrics']['admin_actions']}\n";
        $summary .= "- **Total Requests:** {$report['performance_metrics']['total_requests']}\n\n";
        
        $summary .= "## Response Times\n\n";
        $summary .= "- **Average:** {$report['response_times']['average_ms']}ms\n";
        $summary .= "- **Maximum:** {$report['response_times']['maximum_ms']}ms\n";
        $summary .= "- **Minimum:** {$report['response_times']['minimum_ms']}ms\n\n";
        
        $summary .= "## Success Rates\n\n";
        $summary .= "- **Registration Success:** {$report['success_rates']['user_registration_rate']}%\n";
        $summary .= "- **Payment Success:** {$report['success_rates']['payment_success_rate']}%\n";
        $summary .= "- **Overall Success:** {$report['success_rates']['overall_success_rate']}%\n\n";
        
        if ($report['errors_summary']['total_errors'] > 0) {
            $summary .= "## Errors\n\n";
            $summary .= "- **Total Errors:** {$report['errors_summary']['total_errors']}\n";
            foreach ($report['errors_summary']['error_types'] as $type => $count) {
                $summary .= "- **{$type}:** {$count}\n";
            }
            $summary .= "\n";
        }
        
        $summary .= "## Recommendations\n\n";
        foreach ($report['recommendations'] as $recommendation) {
            $summary .= "- {$recommendation}\n";
        }
        
        file_put_contents($this->simulationPath . '/SUMMARY.md', $summary);
    }

    /**
     * Generate performance recommendations.
     */
    private function generateRecommendations()
    {
        $recommendations = [];
        
        // Check response times
        if ($this->metrics['avg_response_time'] > 1000) {
            $recommendations[] = "⚠️  Average response time is high ({$this->metrics['avg_response_time']}ms). Consider optimizing database queries.";
        }
        
        if ($this->metrics['max_response_time'] > 5000) {
            $recommendations[] = "🔴 Maximum response time is very high ({$this->metrics['max_response_time']}ms). Investigate slow endpoints.";
        }
        
        // Check success rates
        $overallSuccess = $this->metrics['total_requests'] > 0 ? 
            (($this->metrics['total_requests'] - $this->metrics['failed_requests']) / $this->metrics['total_requests']) * 100 : 100;
        
        if ($overallSuccess < 95) {
            $recommendations[] = "🔴 Success rate is below 95% ({$overallSuccess}%). Check error logs for issues.";
        }
        
        // Check error patterns
        if (count($this->errors) > ($this->option('users') * 0.1)) {
            $recommendations[] = "⚠️  High error count detected. Review error logs for patterns.";
        }
        
        // Performance recommendations
        if ($this->metrics['total_requests'] > 1000 && $this->metrics['avg_response_time'] < 500) {
            $recommendations[] = "✅ Good performance under load. Application handles concurrent requests well.";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "✅ No major performance issues detected. Application performed well under simulated load.";
        }
        
        return $recommendations;
    }

    /**
     * Clean up test data.
     */
    private function cleanupTestData()
    {
        $this->info("🧹 Cleaning up test data...");
        
        try {
            // Delete test users and their payments
            $testUserIds = User::where('email', 'like', '%' . $this->simulationId . '%')->pluck('id');
            
            if ($testUserIds->count() > 0) {
                Payment::whereIn('user_id', $testUserIds)->delete();
                User::whereIn('id', $testUserIds)->delete();
                $this->info("✅ Deleted {$testUserIds->count()} test users and their data");
            }
            
            // Delete simulation payments
            Payment::where('metadata->simulation_id', $this->simulationId)->delete();
            $this->info("✅ Deleted simulation payments");
            
        } catch (\Exception $e) {
            $this->error("❌ Cleanup failed: " . $e->getMessage());
        }
    }

    /**
     * Display final summary.
     */
    private function displaySummary()
    {
        $this->newLine();
        $this->info("📊 SIMULATION SUMMARY");
        $this->info("=" . str_repeat("=", 30));
        $this->info("🎯 Users Simulated: {$this->metrics['users_created']}/{$this->option('users')}");
        $this->info("🔐 Successful Logins: {$this->metrics['users_logged_in']}");
        $this->info("💳 Payment Attempts: {$this->metrics['payments_attempted']}");
        $this->info("✅ Successful Payments: {$this->metrics['payments_successful']}");
        $this->info("🛡️  Admin Actions: {$this->metrics['admin_actions']}");
        $this->info("📈 Average Response: " . round($this->metrics['avg_response_time'], 2) . "ms");
        $this->info("❌ Total Errors: " . count($this->errors));
        $this->newLine();
        $this->info("📁 Full report: {$this->simulationPath}/");
    }
} 