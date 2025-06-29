<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MembershipService;

class DashboardController extends Controller
{
    /**
     * Show user dashboard based on role.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Admin and Super Admin see community statistics
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }
        
        // Regular users see only their personal dashboard
        return $this->userDashboard();
    }
    
    /**
     * Admin dashboard with community statistics.
     */
    private function adminDashboard()
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'membership_payments' => \App\Models\Payment::where('payment_type', 'membership')->where('status', \App\Models\Payment::STATUS_COMPLETED)->count(),
            'total_revenue' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED)->sum('amount') / 100,
            'total_donations' => \App\Models\Payment::where('payment_type', 'donation')->where('status', \App\Models\Payment::STATUS_COMPLETED)->sum('amount') / 100,
            'recent_registrations' => \App\Models\User::where('created_at', '>=', now()->subDays(30))->count(),
            'pending_payments' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_PENDING)->count(),
        ];

        // Super Admin Analytics (only for super admins)
        $analytics = null;
        $renewals = null;
        if (auth()->user()->isSuperAdmin()) {
            $analytics = $this->getSuperAdminAnalytics();
            $renewals = $this->getMembershipRenewals();
        }

        return view('dashboard.admin', compact('stats', 'analytics', 'renewals'));
    }
    
    /**
     * User dashboard with personal statistics only.
     */
    private function userDashboard()
    {
        $user = auth()->user();
        $membershipService = new MembershipService();
        
        // Use the service to get user statistics
        $userStats = $membershipService->getUserDashboardStats($user);

        return view('dashboard.user', compact('userStats'));
    }

    /**
     * Get advanced analytics for super admins only.
     */
    private function getSuperAdminAnalytics()
    {
        // 1. Payment Method Breakdown
        $completedPayments = \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED);
        $totalRevenue = $completedPayments->sum('amount') / 100;
        
        $paymentMethods = [
            'stripe' => [
                'count' => $completedPayments->where('payment_method', 'stripe')->count(),
                'amount' => $completedPayments->where('payment_method', 'stripe')->sum('amount') / 100,
                'percentage' => $totalRevenue > 0 ? round(($completedPayments->where('payment_method', 'stripe')->sum('amount') / 100) / $totalRevenue * 100, 1) : 0
            ],
            'paypal' => [
                'count' => $completedPayments->where('payment_method', 'paypal')->count(),
                'amount' => $completedPayments->where('payment_method', 'paypal')->sum('amount') / 100,
                'percentage' => $totalRevenue > 0 ? round(($completedPayments->where('payment_method', 'paypal')->sum('amount') / 100) / $totalRevenue * 100, 1) : 0
            ],
            'bank_transfer' => [
                'count' => $completedPayments->where('payment_method', 'bank_transfer')->count(),
                'amount' => $completedPayments->where('payment_method', 'bank_transfer')->sum('amount') / 100,
                'percentage' => $totalRevenue > 0 ? round(($completedPayments->where('payment_method', 'bank_transfer')->sum('amount') / 100) / $totalRevenue * 100, 1) : 0
            ],
            'twint' => [
                'count' => $completedPayments->where('payment_method', 'twint')->count(),
                'amount' => $completedPayments->where('payment_method', 'twint')->sum('amount') / 100,
                'percentage' => $totalRevenue > 0 ? round(($completedPayments->where('payment_method', 'twint')->sum('amount') / 100) / $totalRevenue * 100, 1) : 0
            ]
        ];

        // 2. Monthly Revenue Comparison
        $thisMonth = \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount') / 100;
            
        $lastMonth = \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount') / 100;
            
        $monthlyGrowth = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        // 3. Recent Activity Summary
        $recentActivity = [
            'payments_last_7_days' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED)
                ->where('created_at', '>=', now()->subDays(7))->count(),
            'users_last_7_days' => \App\Models\User::where('created_at', '>=', now()->subDays(7))->count(),
            'revenue_today' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED)
                ->whereDate('created_at', today())->sum('amount') / 100,
            'revenue_this_week' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_COMPLETED)
                ->where('created_at', '>=', now()->startOfWeek())->sum('amount') / 100,
        ];

        return [
            'payment_methods' => $paymentMethods,
            'monthly_comparison' => [
                'this_month' => $thisMonth,
                'last_month' => $lastMonth,
                'growth' => $monthlyGrowth,
                'growth_amount' => $thisMonth - $lastMonth
            ],
            'recent_activity' => $recentActivity
        ];
    }

    /**
     * Get membership renewals for super admins only.
     */
    private function getMembershipRenewals()
    {
        // Get all renewals and filter using calculated values
        $allRenewals = \App\Models\MembershipRenewal::with(['user', 'payment'])
            ->where('is_hidden', false)
            ->where('is_renewed', false)
            ->get();

        // Filter renewals needing attention (within 30 days) using calculated values
        $renewalsNeedingAttention = $allRenewals->filter(function ($renewal) {
            $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
            return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30;
        })->sortBy(function ($renewal) {
            $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
            // Priority sorting: expired first, then critical, then warning
            if ($daysUntilExpiry <= 0) return 1; // Expired - highest priority
            if ($daysUntilExpiry <= 7) return 2; // Critical - 7 days or less
            if ($daysUntilExpiry <= 30) return 3; // Warning - 30 days or less
            return 4; // Should not happen in this filter
        })->values();

        // Calculate statistics using calculated values
        $expired = $allRenewals->filter(function ($renewal) {
            return $renewal->calculateDaysUntilExpiry() <= 0;
        })->count();

        $expiring7Days = $allRenewals->filter(function ($renewal) {
            $days = $renewal->calculateDaysUntilExpiry();
            return $days > 0 && $days <= 7;
        })->count();

        $expiring30Days = $allRenewals->filter(function ($renewal) {
            $days = $renewal->calculateDaysUntilExpiry();
            return $days > 0 && $days <= 30;
        })->count();

        $renewalStats = [
            'total_active_memberships' => \App\Models\MembershipRenewal::where('is_renewed', false)->count(),
            'expiring_within_30_days' => $expiring30Days,
            'expiring_within_7_days' => $expiring7Days,
            'expired' => $expired,
            'hidden' => \App\Models\MembershipRenewal::where('is_hidden', true)->count(),
        ];

        return [
            'renewals' => $renewalsNeedingAttention,
            'stats' => $renewalStats
        ];
    }
} 