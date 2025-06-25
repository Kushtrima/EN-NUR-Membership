<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MembershipRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_id',
        'membership_start_date',
        'membership_end_date',
        'days_until_expiry',
        'notifications_sent',
        'last_notification_sent_at',
        'is_hidden',
        'is_expired',
        'is_renewed',
        'renewal_payment_id',
        'admin_notes',
    ];

    protected $casts = [
        'membership_start_date' => 'date',
        'membership_end_date' => 'date',
        'notifications_sent' => 'array',
        'last_notification_sent_at' => 'datetime',
        'is_hidden' => 'boolean',
        'is_expired' => 'boolean',
        'is_renewed' => 'boolean',
    ];

    /**
     * Get the user that owns the membership renewal.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the original membership payment.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the renewal payment if exists.
     */
    public function renewalPayment()
    {
        return $this->belongsTo(Payment::class, 'renewal_payment_id');
    }

    /**
     * Calculate days until expiry.
     */
    public function calculateDaysUntilExpiry(): int
    {
        // Use diffInDays with false parameter to get negative values for expired memberships
        $diffInDays = Carbon::now()->diffInDays($this->membership_end_date, false);
        
        // Return the actual difference (can be negative for expired memberships)
        return (int) $diffInDays;
    }

    /**
     * Get days until expiry (non-negative for display purposes).
     */
    public function getDaysUntilExpiryForDisplay(): int
    {
        return max(0, $this->calculateDaysUntilExpiry());
    }

    /**
     * Update days until expiry.
     */
    public function updateDaysUntilExpiry(): void
    {
        $this->days_until_expiry = $this->calculateDaysUntilExpiry();
        $this->is_expired = $this->days_until_expiry <= 0;
        $this->save();
    }

    /**
     * Check if notification should be sent for given days.
     */
    public function shouldSendNotification(int $days): bool
    {
        $notificationsSent = $this->notifications_sent ?? [];
        return !in_array($days, $notificationsSent) && $this->days_until_expiry <= $days;
    }

    /**
     * Mark notification as sent.
     */
    public function markNotificationSent(int $days): void
    {
        $notificationsSent = $this->notifications_sent ?? [];
        if (!in_array($days, $notificationsSent)) {
            $notificationsSent[] = $days;
            $this->notifications_sent = $notificationsSent;
            $this->last_notification_sent_at = now();
            $this->save();
        }
    }

    /**
     * Get the appropriate notification message based on days remaining.
     */
    public function getNotificationMessage(): string
    {
        $days = $this->days_until_expiry;
        
        if ($days <= 0) {
            return 'Your membership has expired. Please renew to continue enjoying our services.';
        } elseif ($days == 1) {
            return 'Your membership expires tomorrow! Please renew to avoid interruption.';
        } elseif ($days <= 7) {
            return "Your membership expires in {$days} days. Please renew soon to continue your access.";
        } elseif ($days <= 30) {
            return "Your membership expires in {$days} days. Consider renewing to ensure uninterrupted service.";
        }
        
        return "Your membership expires in {$days} days.";
    }

    /**
     * Get priority level for display (higher number = higher priority).
     */
    public function getPriorityLevel(): int
    {
        if ($this->is_expired) return 4; // Highest priority
        if ($this->days_until_expiry <= 1) return 3;
        if ($this->days_until_expiry <= 7) return 2;
        if ($this->days_until_expiry <= 30) return 1;
        return 0;
    }

    /**
     * Get CSS class for display styling.
     */
    public function getDisplayClass(): string
    {
        if ($this->is_expired) return 'renewal-expired';
        if ($this->days_until_expiry <= 1) return 'renewal-critical';
        if ($this->days_until_expiry <= 7) return 'renewal-urgent';
        if ($this->days_until_expiry <= 30) return 'renewal-warning';
        return 'renewal-normal';
    }

    /**
     * Scope for active renewals (not hidden, not renewed).
     */
    public function scopeActive($query)
    {
        return $query->where('is_hidden', false)
                    ->where('is_renewed', false);
    }

    /**
     * Scope for renewals needing attention (within 30 days).
     */
    public function scopeNeedingAttention($query)
    {
        return $query->where('days_until_expiry', '<=', 30)
                    ->where('is_hidden', false)
                    ->where('is_renewed', false);
    }

    /**
     * Scope for expired renewals.
     */
    public function scopeExpired($query)
    {
        return $query->where('is_expired', true)
                    ->where('is_hidden', false)
                    ->where('is_renewed', false);
    }
}
