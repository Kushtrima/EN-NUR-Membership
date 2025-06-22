<?php

namespace App\Services;

use App\Models\User;
use App\Models\MembershipRenewal;

class MembershipService
{
    /**
     * Get user's membership status with all calculated fields.
     */
    public function getUserMembershipStatus(User $user): ?array
    {
        $renewal = MembershipRenewal::where('user_id', $user->id)
            ->where('is_renewed', false)
            ->first();

        if (!$renewal) {
            return null;
        }

        $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
        
        return [
            'days_until_expiry' => $daysUntilExpiry,
            'is_hidden' => $renewal->is_hidden,
            'is_expired' => $daysUntilExpiry <= 0,
            'membership_end_date' => $renewal->membership_end_date,
            'priority_level' => $this->getPriorityLevel($daysUntilExpiry, $renewal->is_hidden),
            'display_class' => $this->getDisplayClass($daysUntilExpiry, $renewal->is_hidden),
            'border_color' => $this->getBorderColor($daysUntilExpiry, $renewal->is_hidden),
            'status_badge' => $this->getStatusBadge($daysUntilExpiry, $renewal->is_hidden),
        ];
    }

    /**
     * Get priority level for sorting/display.
     */
    private function getPriorityLevel(int $daysUntilExpiry, bool $isHidden): int
    {
        if ($isHidden) return 4; // Highest priority - hidden/removed users
        if ($daysUntilExpiry <= 0) return 3; // Expired
        if ($daysUntilExpiry <= 7) return 2; // Critical (1 week)
        if ($daysUntilExpiry <= 30) return 1; // Warning (1 month)
        return 0; // Normal
    }

    /**
     * Get CSS class for display styling.
     */
    private function getDisplayClass(int $daysUntilExpiry, bool $isHidden): string
    {
        if ($isHidden) return 'membership-hidden';
        if ($daysUntilExpiry <= 0) return 'membership-expired';
        if ($daysUntilExpiry <= 7) return 'membership-critical';
        if ($daysUntilExpiry <= 30) return 'membership-warning';
        return 'membership-active';
    }

    /**
     * Get border color for user rows.
     */
    private function getBorderColor(int $daysUntilExpiry, bool $isHidden): string
    {
        if ($isHidden) return '#dc3545'; // Red - Hidden/Deleted users
        if ($daysUntilExpiry <= 0) return '#dc3545'; // Red - Expired
        if ($daysUntilExpiry <= 30) return '#ff6c37'; // Orange - Expiring within 30 days
        return '#28a745'; // Green - Active membership (>30 days)
    }

    /**
     * Get status badge for display.
     */
    private function getStatusBadge(int $daysUntilExpiry, bool $isHidden): array
    {
        if ($isHidden) {
            return [
                'text' => 'HIDDEN',
                'color' => '#dc3545',
                'background' => '#dc3545'
            ];
        }

        if ($daysUntilExpiry <= 0) {
            return [
                'text' => 'EXPIRED',
                'color' => 'white',
                'background' => '#dc3545'
            ];
        }

        if ($daysUntilExpiry <= 7) {
            return [
                'text' => $daysUntilExpiry . 'D',
                'color' => 'white',
                'background' => '#dc3545'
            ];
        }

        if ($daysUntilExpiry <= 30) {
            return [
                'text' => $daysUntilExpiry . 'D',
                'color' => 'white',
                'background' => '#ff6c37'
            ];
        }

        return [
            'text' => 'ACTIVE',
            'color' => 'white',
            'background' => '#28a745'
        ];
    }

    /**
     * Get user dashboard statistics.
     */
    public function getUserDashboardStats(User $user): array
    {
        $completedPayments = $user->payments()->where('status', 'completed');
        $membershipPayments = $completedPayments->where('payment_type', 'membership');
        $latestMembership = $membershipPayments->latest()->first();

        // Check for active membership renewal (has valid dates and not expired)
        $activeMembershipRenewal = MembershipRenewal::where('user_id', $user->id)
            ->whereNotNull('membership_start_date')
            ->whereNotNull('membership_end_date')
            ->where('membership_end_date', '>=', now())
            ->where('is_renewed', false)
            ->first();

        return [
            'total_paid' => $completedPayments->sum('amount') / 100,
            'total_donations' => $completedPayments->where('payment_type', 'donation')->sum('amount') / 100,
            'completed_payments' => $completedPayments->count(),
            'pending_payments' => $user->payments()->where('status', 'pending')->count(),
            'has_membership' => $activeMembershipRenewal !== null,
            'latest_membership' => $latestMembership,
            'active_membership_renewal' => $activeMembershipRenewal,
        ];
    }
} 