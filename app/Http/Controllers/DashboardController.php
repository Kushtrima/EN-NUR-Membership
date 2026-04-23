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

        // Get pending cash payments for all admins
        $pendingCashPayments = \App\Models\Payment::with('user')
            ->where('payment_method', 'cash')
            ->where('status', \App\Models\Payment::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        // Super Admin Analytics (only for super admins)
        $analytics = null;
        $renewals = null;
        if (auth()->user()->isSuperAdmin()) {
            $analytics = $this->getSuperAdminAnalytics();
            $renewals = $this->getMembershipRenewals();
        }

        return view('dashboard.admin', compact('stats', 'analytics', 'renewals', 'pendingCashPayments'));
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
        //
        // PRIOR BUG: the previous implementation chained where() calls on a
        // shared Eloquent Builder ($completedPayments). Builder->where() is
        // NOT a clone — each call mutated the same instance, so after
        // filtering by payment_method='stripe' once, every subsequent
        // payment_method filter accumulated, producing
        //   WHERE payment_method='stripe' AND payment_method='paypal' AND ...
        // which matched zero rows. Result: only the stripe row was correct;
        // paypal, bank_transfer, twint silently showed 0/0/0%.
        //
        // Fixed with one grouped query (also closes N+1 finding 3.8).
        $methodTotals = \App\Models\Payment::query()
            ->where('status', \App\Models\Payment::STATUS_COMPLETED)
            ->selectRaw('payment_method, COUNT(*) as method_count, COALESCE(SUM(amount), 0) as method_total')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $totalRevenue = $methodTotals->sum('method_total') / 100;

        $paymentMethods = [];
        foreach (['stripe', 'paypal', 'bank_transfer', 'twint'] as $method) {
            $row    = $methodTotals->get($method);
            $count  = $row ? (int) $row->method_count : 0;
            $amount = $row ? ((int) $row->method_total) / 100 : 0;
            $paymentMethods[$method] = [
                'count'      => $count,
                'amount'     => $amount,
                'percentage' => $totalRevenue > 0 ? round($amount / $totalRevenue * 100, 1) : 0,
            ];
        }

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

        // Calculate statistics using calculated values (only visible, non-hidden renewals)
        $visibleRenewals = $allRenewals->filter(function ($renewal) {
            return !$renewal->is_hidden && !$renewal->is_renewed;
        });

        $expired = $visibleRenewals->filter(function ($renewal) {
            return $renewal->calculateDaysUntilExpiry() <= 0;
        })->count();

        $expiring7Days = $visibleRenewals->filter(function ($renewal) {
            $days = $renewal->calculateDaysUntilExpiry();
            return $days > 0 && $days <= 7;
        })->count();

        $expiring30Days = $visibleRenewals->filter(function ($renewal) {
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