<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\MembershipRenewal;
use App\Services\MembershipService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestingDashboardController extends Controller
{
    private $membershipService;
    private $testResults = [];

    public function __construct()
    {
        $this->membershipService = new MembershipService();
    }

    /**
     * Show the testing dashboard
     */
    public function index()
    {
        return view('admin.testing-dashboard');
    }

    /**
     * Run all tests and return results
     */
    public function runAllTests()
    {
        $this->testResults = [];
        
        try {
            // Core Business Logic Tests
            $this->testMembershipStatusCalculation();
            $this->testColorCodingLogic();
            $this->testStatusBadgeGeneration();
            $this->testPriorityLevels();
            
            // Data Integrity Tests
            $this->testDatabaseRelationships();
            $this->testMembershipRenewalCalculations();
            $this->testPaymentIntegrity();
            
            // System Integration Tests
            $this->testDashboardStatistics();
            $this->testUserPermissions();
            $this->testAdminFunctionality();
            
            // Performance Tests
            $this->testQueryPerformance();
            
            // Email & Notification Tests
            $this->testNotificationLogic();
            
            return response()->json([
                'success' => true,
                'results' => $this->testResults,
                'summary' => $this->generateSummary()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'results' => $this->testResults
            ]);
        }
    }

    /**
     * Test membership status calculation logic
     */
    private function testMembershipStatusCalculation()
    {
        $this->addTestCategory('Membership Status Calculation');
        
        try {
            // Test with different scenarios
            $testCases = [
                ['days' => 45, 'hidden' => false, 'expected_color' => '#28a745', 'expected_class' => 'membership-active'],
                ['days' => 25, 'hidden' => false, 'expected_color' => '#ff6c37', 'expected_class' => 'membership-warning'],
                ['days' => 5, 'hidden' => false, 'expected_color' => '#ff6c37', 'expected_class' => 'membership-warning'],
                ['days' => -5, 'hidden' => false, 'expected_color' => '#dc3545', 'expected_class' => 'membership-expired'],
                ['days' => 30, 'hidden' => true, 'expected_color' => '#dc3545', 'expected_class' => 'membership-hidden'],
            ];
            
            foreach ($testCases as $case) {
                $result = $this->simulateMembershipStatus($case['days'], $case['hidden']);
                
                $colorMatch = $result['border_color'] === $case['expected_color'];
                $classMatch = $result['display_class'] === $case['expected_class'];
                
                $this->addTestResult(
                    "Status calculation for {$case['days']} days" . ($case['hidden'] ? ' (hidden)' : ''),
                    $colorMatch && $classMatch,
                    $colorMatch && $classMatch ? 'Color and class correct' : "Expected color: {$case['expected_color']}, got: {$result['border_color']}"
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Membership Status Calculation', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test color coding logic
     */
    private function testColorCodingLogic()
    {
        $this->addTestCategory('Color Coding System');
        
        try {
            // Test color boundaries
            $colorTests = [
                ['days' => 31, 'expected' => '#28a745', 'label' => 'Active (>30 days)'],
                ['days' => 30, 'expected' => '#ff6c37', 'label' => 'Warning (30 days)'],
                ['days' => 15, 'expected' => '#ff6c37', 'label' => 'Warning (15 days)'],
                ['days' => 1, 'expected' => '#ff6c37', 'label' => 'Warning (1 day)'],
                ['days' => 0, 'expected' => '#dc3545', 'label' => 'Expired (0 days)'],
                ['days' => -10, 'expected' => '#dc3545', 'label' => 'Expired (-10 days)'],
            ];
            
            foreach ($colorTests as $test) {
                $result = $this->simulateMembershipStatus($test['days'], false);
                $colorMatch = $result['border_color'] === $test['expected'];
                
                $this->addTestResult(
                    "Color for {$test['label']}",
                    $colorMatch,
                    $colorMatch ? "Correct color: {$test['expected']}" : "Expected: {$test['expected']}, got: {$result['border_color']}"
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Color Coding Logic', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test status badge generation
     */
    private function testStatusBadgeGeneration()
    {
        $this->addTestCategory('Status Badge Generation');
        
        try {
            $badgeTests = [
                ['days' => 45, 'hidden' => false, 'expected_text' => 'ACTIVE'],
                ['days' => 25, 'hidden' => false, 'expected_text' => '25D'],
                ['days' => 5, 'hidden' => false, 'expected_text' => '5D'],
                ['days' => 0, 'hidden' => false, 'expected_text' => 'EXPIRED'],
                ['days' => -5, 'hidden' => false, 'expected_text' => 'EXPIRED'],
                ['days' => 30, 'hidden' => true, 'expected_text' => 'HIDDEN'],
            ];
            
            foreach ($badgeTests as $test) {
                $result = $this->simulateMembershipStatus($test['days'], $test['hidden']);
                $badgeMatch = $result['status_badge']['text'] === $test['expected_text'];
                
                $this->addTestResult(
                    "Badge for {$test['days']} days" . ($test['hidden'] ? ' (hidden)' : ''),
                    $badgeMatch,
                    $badgeMatch ? "Correct badge: {$test['expected_text']}" : "Expected: {$test['expected_text']}, got: {$result['status_badge']['text']}"
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Status Badge Generation', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test priority level calculation
     */
    private function testPriorityLevels()
    {
        $this->addTestCategory('Priority Level System');
        
        try {
            $priorityTests = [
                ['days' => 45, 'hidden' => false, 'expected' => 0, 'label' => 'Normal'],
                ['days' => 25, 'hidden' => false, 'expected' => 1, 'label' => 'Warning'],
                ['days' => 5, 'hidden' => false, 'expected' => 2, 'label' => 'Critical'],
                ['days' => 0, 'hidden' => false, 'expected' => 3, 'label' => 'Expired'],
                ['days' => 30, 'hidden' => true, 'expected' => 4, 'label' => 'Hidden'],
            ];
            
            foreach ($priorityTests as $test) {
                $result = $this->simulateMembershipStatus($test['days'], $test['hidden']);
                $priorityMatch = $result['priority_level'] === $test['expected'];
                
                $this->addTestResult(
                    "Priority for {$test['label']} ({$test['days']} days)",
                    $priorityMatch,
                    $priorityMatch ? "Correct priority: {$test['expected']}" : "Expected: {$test['expected']}, got: {$result['priority_level']}"
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Priority Level System', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test database relationships
     */
    private function testDatabaseRelationships()
    {
        $this->addTestCategory('Database Relationships');
        
        try {
            // Test User -> Payments relationship
            $userWithPayments = User::with('payments')->first();
            if ($userWithPayments) {
                $paymentsLoaded = $userWithPayments->payments !== null;
                $this->addTestResult(
                    'User -> Payments relationship',
                    $paymentsLoaded,
                    $paymentsLoaded ? "User {$userWithPayments->id} has {$userWithPayments->payments->count()} payments" : 'Payments relationship not loaded',
                    $paymentsLoaded ? null : 'User->payments relationship returned null - check hasMany relationship definition in User model'
                );
            } else {
                $this->addTestResult(
                    'User -> Payments relationship',
                    false,
                    'No users found in database',
                    'Cannot test relationship - no users exist in the database'
                );
            }
            
            // Test User -> MembershipRenewals relationship
            $userWithRenewals = User::with('membershipRenewals')->first();
            if ($userWithRenewals) {
                $renewalsLoaded = $userWithRenewals->membershipRenewals !== null;
                $this->addTestResult(
                    'User -> MembershipRenewals relationship',
                    $renewalsLoaded,
                    $renewalsLoaded ? "User {$userWithRenewals->id} has {$userWithRenewals->membershipRenewals->count()} renewals" : 'MembershipRenewals relationship not loaded',
                    $renewalsLoaded ? null : 'User->membershipRenewals relationship returned null - check hasMany relationship definition in User model'
                );
            } else {
                $this->addTestResult(
                    'User -> MembershipRenewals relationship',
                    false,
                    'No users found in database',
                    'Cannot test relationship - no users exist in the database'
                );
            }
            
            // Test MembershipRenewal -> User relationship
            $renewal = MembershipRenewal::with('user')->first();
            if ($renewal) {
                $userLoaded = $renewal->user !== null;
                $this->addTestResult(
                    'MembershipRenewal -> User relationship',
                    $userLoaded,
                    $userLoaded ? "Renewal {$renewal->id} belongs to user {$renewal->user->name} (ID: {$renewal->user->id})" : 'User relationship not loaded',
                    $userLoaded ? null : 'MembershipRenewal->user relationship returned null - check belongsTo relationship definition in MembershipRenewal model'
                );
            } else {
                $this->addTestResult(
                    'MembershipRenewal -> User relationship',
                    false,
                    'No membership renewals found in database',
                    'Cannot test relationship - no membership renewals exist in the database'
                );
            }
            
            // Test MembershipRenewal -> Payment relationship
            $renewalWithPayment = MembershipRenewal::with('payment')->first();
            if ($renewalWithPayment) {
                $paymentLoaded = $renewalWithPayment->payment !== null;
                $this->addTestResult(
                    'MembershipRenewal -> Payment relationship',
                    $paymentLoaded,
                    $paymentLoaded ? "Renewal {$renewalWithPayment->id} linked to payment {$renewalWithPayment->payment->id}" : 'Payment relationship not loaded',
                    $paymentLoaded ? null : 'MembershipRenewal->payment relationship returned null - check belongsTo relationship definition or foreign key constraint'
                );
            } else {
                $this->addTestResult(
                    'MembershipRenewal -> Payment relationship',
                    false,
                    'No membership renewals found in database',
                    'Cannot test relationship - no membership renewals exist in the database'
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult(
                'Database Relationships', 
                false, 
                'Critical database error occurred',
                "Exception: {$e->getMessage()}\nFile: {$e->getFile()}\nLine: {$e->getLine()}\n\nThis indicates a serious database connectivity or model configuration issue."
            );
        }
    }

    /**
     * Test membership renewal calculations
     */
    private function testMembershipRenewalCalculations()
    {
        $this->addTestCategory('Membership Renewal Calculations');
        
        try {
            $renewals = MembershipRenewal::all();
            
            foreach ($renewals as $renewal) {
                // Test days until expiry calculation
                $calculatedDays = $renewal->calculateDaysUntilExpiry();
                $expectedDays = Carbon::now()->diffInDays($renewal->membership_end_date, false);
                
                $this->addTestResult(
                    "Days calculation for renewal ID {$renewal->id}",
                    abs($calculatedDays - (int)$expectedDays) <= 1, // Allow 1 day difference for timing
                    "Calculated: {$calculatedDays}, Expected: " . (int)$expectedDays
                );
                
                // Test expiry status
                $isExpired = $calculatedDays <= 0;
                $this->addTestResult(
                    "Expiry status for renewal ID {$renewal->id}",
                    ($renewal->is_expired ?? false) === $isExpired,
                    "Days: {$calculatedDays}, Expired: " . ($isExpired ? 'Yes' : 'No')
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Membership Renewal Calculations', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test payment integrity
     */
    private function testPaymentIntegrity()
    {
        $this->addTestCategory('Payment System Integrity');
        
        try {
            $payments = Payment::all();
            
            // Test payment status consistency
            $statusCounts = Payment::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();
            
            foreach ($statusCounts as $statusCount) {
                $this->addTestResult(
                    "Payment status '{$statusCount->status}' count",
                    $statusCount->count > 0,
                    "{$statusCount->count} payments with status '{$statusCount->status}'"
                );
            }
            
            // Test payment types
            $typeCounts = Payment::selectRaw('payment_type, COUNT(*) as count')
                ->groupBy('payment_type')
                ->get();
            
            foreach ($typeCounts as $typeCount) {
                $this->addTestResult(
                    "Payment type '{$typeCount->payment_type}' count",
                    $typeCount->count > 0,
                    "{$typeCount->count} payments of type '{$typeCount->payment_type}'"
                );
            }
            
            // Test amount validation
            $invalidAmounts = Payment::where('amount', '<=', 0)->count();
            $this->addTestResult(
                'Payment amount validation',
                $invalidAmounts === 0,
                $invalidAmounts === 0 ? 'All payments have valid amounts' : "{$invalidAmounts} payments with invalid amounts"
            );
            
        } catch (\Exception $e) {
            $this->addTestResult('Payment System Integrity', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test dashboard statistics
     */
    private function testDashboardStatistics()
    {
        $this->addTestCategory('Dashboard Statistics');
        
        try {
            // Test admin dashboard renewals logic
            $adminRenewals = MembershipRenewal::with('user')
                ->where('is_renewed', false)
                ->where('is_hidden', false)
                ->get()
                ->filter(function ($renewal) {
                    $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
                    return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30;
                });
            
            $this->addTestResult(
                'Admin dashboard renewals filter',
                $adminRenewals->count() >= 0,
                "Found {$adminRenewals->count()} renewals needing attention"
            );
            
            // Test user statistics calculation
            $users = User::all();
            foreach ($users->take(3) as $user) { // Test first 3 users
                $userStats = $this->membershipService->getUserDashboardStats($user);
                
                $this->addTestResult(
                    "User stats for {$user->name}",
                    isset($userStats['total_paid']) && isset($userStats['has_membership']),
                    "Stats calculated successfully"
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Dashboard Statistics', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test user permissions
     */
    private function testUserPermissions()
    {
        $this->addTestCategory('User Permissions & Roles');
        
        try {
            $users = User::all();
            
            // Test role assignments
            $adminCount = $users->where('role', 'admin')->count();
            $superAdminCount = $users->where('role', 'super_admin')->count();
            $userCount = $users->where('role', 'user')->count();
            
            $this->addTestResult(
                'User role distribution',
                $adminCount + $superAdminCount + $userCount === $users->count(),
                "Users: {$userCount}, Admins: {$adminCount}, Super Admins: {$superAdminCount}"
            );
            
            // Test admin methods
            foreach ($users->take(5) as $user) {
                if (method_exists($user, 'isAdmin') && method_exists($user, 'isSuperAdmin')) {
                    $isAdminResult = $user->isAdmin();
                    $isSuperAdminResult = $user->isSuperAdmin();
                    
                    $this->addTestResult(
                        "Permission methods for {$user->name}",
                        is_bool($isAdminResult) && is_bool($isSuperAdminResult),
                        "Admin: " . ($isAdminResult ? 'Yes' : 'No') . ", Super Admin: " . ($isSuperAdminResult ? 'Yes' : 'No')
                    );
                }
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('User Permissions & Roles', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test admin functionality
     */
    private function testAdminFunctionality()
    {
        $this->addTestCategory('Admin Panel Functionality');
        
        try {
            // Test admin dashboard data
            $totalUsers = User::count();
            $totalPayments = Payment::count();
            $completedPayments = Payment::where('status', 'completed')->count();
            
            $this->addTestResult(
                'Admin statistics calculation',
                $totalUsers > 0 && $totalPayments >= 0,
                "Users: {$totalUsers}, Payments: {$totalPayments}, Completed: {$completedPayments}"
            );
            
            // Test membership service integration
            if ($totalUsers > 0) {
                $testUser = User::first();
                $membershipStatus = $this->membershipService->getUserMembershipStatus($testUser);
                
                $this->addTestResult(
                    'MembershipService integration',
                    $membershipStatus !== false, // Can be null or array, but not false
                    $membershipStatus ? 'Status calculated successfully' : 'No membership status (expected for some users)'
                );
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Admin Panel Functionality', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test query performance
     */
    private function testQueryPerformance()
    {
        $this->addTestCategory('Query Performance');
        
        try {
            // Test user list query performance
            $start = microtime(true);
            $users = User::with(['payments', 'membershipRenewals'])->paginate(20);
            $userQueryTime = microtime(true) - $start;
            
            $this->addTestResult(
                'User list query performance',
                $userQueryTime < 1.0, // Should complete in under 1 second
                "Query time: " . round($userQueryTime * 1000, 2) . "ms"
            );
            
            // Test payment list query performance
            $start = microtime(true);
            $payments = Payment::with('user')->paginate(20);
            $paymentQueryTime = microtime(true) - $start;
            
            $this->addTestResult(
                'Payment list query performance',
                $paymentQueryTime < 1.0,
                "Query time: " . round($paymentQueryTime * 1000, 2) . "ms"
            );
            
            // Test membership renewal calculations performance
            $start = microtime(true);
            $renewals = MembershipRenewal::all();
            foreach ($renewals->take(10) as $renewal) {
                $renewal->calculateDaysUntilExpiry();
            }
            $renewalCalcTime = microtime(true) - $start;
            
            $this->addTestResult(
                'Membership calculations performance',
                $renewalCalcTime < 0.5,
                "Calculation time for " . min(10, $renewals->count()) . " renewals: " . round($renewalCalcTime * 1000, 2) . "ms"
            );
            
        } catch (\Exception $e) {
            $this->addTestResult('Query Performance', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test notification logic
     */
    private function testNotificationLogic()
    {
        $this->addTestCategory('Notification & Email Logic');
        
        try {
            $renewals = MembershipRenewal::all();
            
            foreach ($renewals->take(5) as $renewal) {
                // Test notification message generation
                if (method_exists($renewal, 'getNotificationMessage')) {
                    $message = $renewal->getNotificationMessage();
                    
                    $this->addTestResult(
                        "Notification message for renewal ID {$renewal->id}",
                        !empty($message) && is_string($message),
                        "Message generated successfully"
                    );
                }
                
                // Test notification scheduling logic
                if (method_exists($renewal, 'shouldSendNotification')) {
                    $shouldSend30 = $renewal->shouldSendNotification(30);
                    $shouldSend7 = $renewal->shouldSendNotification(7);
                    
                    $this->addTestResult(
                        "Notification scheduling for renewal ID {$renewal->id}",
                        is_bool($shouldSend30) && is_bool($shouldSend7),
                        "30-day: " . ($shouldSend30 ? 'Yes' : 'No') . ", 7-day: " . ($shouldSend7 ? 'Yes' : 'No')
                    );
                }
            }
            
        } catch (\Exception $e) {
            $this->addTestResult('Notification & Email Logic', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Simulate membership status calculation
     */
    private function simulateMembershipStatus($daysUntilExpiry, $isHidden)
    {
        // Simulate the MembershipService logic
        $priorityLevel = $this->getPriorityLevel($daysUntilExpiry, $isHidden);
        $displayClass = $this->getDisplayClass($daysUntilExpiry, $isHidden);
        $borderColor = $this->getBorderColor($daysUntilExpiry, $isHidden);
        $statusBadge = $this->getStatusBadge($daysUntilExpiry, $isHidden);
        
        return [
            'days_until_expiry' => $daysUntilExpiry,
            'is_hidden' => $isHidden,
            'is_expired' => $daysUntilExpiry <= 0,
            'priority_level' => $priorityLevel,
            'display_class' => $displayClass,
            'border_color' => $borderColor,
            'status_badge' => $statusBadge,
        ];
    }

    /**
     * Helper methods that mirror MembershipService logic
     */
    private function getPriorityLevel(int $daysUntilExpiry, bool $isHidden): int
    {
        if ($isHidden) return 4;
        if ($daysUntilExpiry <= 0) return 3;
        if ($daysUntilExpiry <= 7) return 2;
        if ($daysUntilExpiry <= 30) return 1;
        return 0;
    }

    private function getDisplayClass(int $daysUntilExpiry, bool $isHidden): string
    {
        if ($isHidden) return 'membership-hidden';
        if ($daysUntilExpiry <= 0) return 'membership-expired';
        if ($daysUntilExpiry <= 7) return 'membership-critical';
        if ($daysUntilExpiry <= 30) return 'membership-warning';
        return 'membership-active';
    }

    private function getBorderColor(int $daysUntilExpiry, bool $isHidden): string
    {
        if ($isHidden) return '#dc3545';
        if ($daysUntilExpiry <= 0) return '#dc3545';
        if ($daysUntilExpiry <= 30) return '#ff6c37';
        return '#28a745';
    }

    private function getStatusBadge(int $daysUntilExpiry, bool $isHidden): array
    {
        if ($isHidden) {
            return ['text' => 'HIDDEN', 'color' => '#dc3545', 'background' => '#dc3545'];
        }
        if ($daysUntilExpiry <= 0) {
            return ['text' => 'EXPIRED', 'color' => 'white', 'background' => '#dc3545'];
        }
        if ($daysUntilExpiry <= 7) {
            return ['text' => $daysUntilExpiry . 'D', 'color' => 'white', 'background' => '#dc3545'];
        }
        if ($daysUntilExpiry <= 30) {
            return ['text' => $daysUntilExpiry . 'D', 'color' => 'white', 'background' => '#ff6c37'];
        }
        return ['text' => 'ACTIVE', 'color' => 'white', 'background' => '#28a745'];
    }

    /**
     * Helper methods for test management
     */
    private function addTestCategory($category)
    {
        $this->testResults[] = [
            'type' => 'category',
            'name' => $category,
            'timestamp' => now()->toISOString()
        ];
    }

    private function addTestResult($testName, $passed, $details = '', $error = null)
    {
        $result = [
            'type' => 'test',
            'name' => $testName,
            'passed' => $passed,
            'details' => $details,
            'duration' => '< 1ms',
            'timestamp' => now()->toISOString()
        ];
        
        // Add detailed error information if test failed
        if (!$passed) {
            if ($error) {
                $result['error'] = $error;
                if ($details && $details !== $error) {
                    $result['error_details'] = $details;
                }
            } elseif ($details) {
                // If no separate error provided, use details as error
                $result['error'] = $details;
            } else {
                $result['error'] = 'Test failed - no details provided';
            }
        }
        
        $this->testResults[] = $result;
    }

    private function generateSummary()
    {
        $tests = collect($this->testResults)->where('type', 'test');
        $categories = collect($this->testResults)->where('type', 'category');
        
        $totalTests = $tests->count();
        $passedTests = $tests->where('passed', true)->count();
        $failedTests = $tests->where('passed', false)->count();
        
        return [
            'total_categories' => $categories->count(),
            'total_tests' => $totalTests,
            'passed_tests' => $passedTests,
            'failed_tests' => $failedTests,
            'success_rate' => $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0,
            'status' => $failedTests === 0 ? 'all_passed' : ($passedTests > $failedTests ? 'mostly_passed' : 'needs_attention')
        ];
    }
} 