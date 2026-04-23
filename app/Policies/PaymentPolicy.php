<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

/**
 * Authorization policy for actions on Payment records.
 *
 * Payments have two classes of actor:
 *   - The user who created the payment (owns it).
 *   - Admins / super admins operating on any payment.
 *
 * User-owned read/confirm flows (payment success callbacks, TWINT /
 * bank / cash instruction pages) already perform an inline
 * `$payment->user_id === auth()->id()` check in PaymentController.
 * Those abilities are duplicated here so they can be migrated to
 * `$this->authorize(...)` in a follow-up without changing behavior.
 *
 * Admin actions (status update, bulk status, payment details, send
 * notification, generate receipt, delete) use `adminWrite()`.
 */
class PaymentPolicy
{
    /**
     * View a single payment.
     * Owner or any admin.
     */
    public function view(User $actor, Payment $payment): bool
    {
        return $actor->isAdmin() || $payment->user_id === $actor->id;
    }

    /**
     * Confirm a pending payment as the paying user.
     * (Used for TWINT / bank / cash self-confirmation flows.)
     * Owner only; must be pending.
     */
    public function confirm(User $actor, Payment $payment): bool
    {
        return $payment->user_id === $actor->id
            && $payment->status === Payment::STATUS_PENDING;
    }

    /**
     * Admin write operations on a payment:
     *   - updatePaymentStatus
     *   - bulkUpdatePaymentStatus (each element)
     *   - getPaymentDetails
     *   - sendPaymentNotification
     *   - generatePaymentReceipt
     *   - cashConfirm (admin-side confirmation)
     *
     * Requires admin or super admin.
     */
    public function adminWrite(User $actor, Payment $payment): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Delete a payment.
     *
     * Deletion has stricter rules: super admin only. Deleting a
     * completed payment removes financial record; keep this
     * privileged.
     */
    public function delete(User $actor, Payment $payment): bool
    {
        return $actor->isSuperAdmin();
    }
}
