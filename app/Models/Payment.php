<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'payment_type',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'payment_method',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the membership renewal record for this payment.
     */
    public function membershipRenewal()
    {
        return $this->hasOne(MembershipRenewal::class);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount / 100, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Payment type constants.
     */
    const TYPE_MEMBERSHIP = 'membership';
    const TYPE_DONATION = 'donation';

    /**
     * Payment status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
} 