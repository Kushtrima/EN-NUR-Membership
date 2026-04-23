<?php

namespace App\Policies;

use App\Models\MembershipRenewal;
use App\Models\User;

/**
 * Authorization policy for actions on MembershipRenewal records.
 *
 * Renewals are administrative records. Current behavior (mirrored
 * exactly here) restricts all actions to super_admin — the existing
 * controller had `if (!auth()->user()->isSuperAdmin()) abort 403`
 * at the top of every method. Keeping the same gate so this commit
 * is a no-op behavior-wise; can be loosened to isAdmin() later if
 * regular admins need this surface.
 *
 * Owners don't interact with their renewal row directly; they see a
 * card in their dashboard that reads from the renewal but doesn't
 * mutate it.
 */
class MembershipRenewalPolicy
{
    /**
     * View a renewal's details. Super admin only.
     */
    public function view(User $actor, MembershipRenewal $renewal): bool
    {
        return $actor->isSuperAdmin();
    }

    /**
     * Administrative write operations:
     *   - sendNotification
     *   - hide
     *   - show (un-hide)
     *
     * Super admin only (matches existing in-controller checks).
     */
    public function adminWrite(User $actor, MembershipRenewal $renewal): bool
    {
        return $actor->isSuperAdmin();
    }
}
