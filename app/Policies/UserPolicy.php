<?php

namespace App\Policies;

use App\Models\User;

/**
 * Authorization policy for actions on User records.
 *
 * These abilities duplicate the role gates already enforced by the
 * `admin` / `super_admin` middleware on route groups and the in-body
 * checks inside AdminController. Defense-in-depth: any future route
 * that accidentally bypasses the middleware (or any CLI tool that
 * acts on behalf of a user) still hits a policy-level check.
 *
 * Conventions:
 *   - $actor is the currently-authenticated user performing the action.
 *   - $target is the User record being acted upon.
 */
class UserPolicy
{
    /**
     * View / list a user record.
     * Admins and super admins can view any user.
     */
    public function view(User $actor, User $target): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Export the user's data bundle (GDPR).
     * Admins and super admins only.
     */
    public function export(User $actor, User $target): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Change another user's role.
     *
     * Mirrors AdminController::updateUserRole:
     *   - only super admins may change roles at all,
     *   - cannot change own super-admin role,
     *   - promoting to super_admin requires super admin (already covered
     *     by the "super admins only" rule above, but kept explicit).
     */
    public function updateRole(User $actor, User $target): bool
    {
        if (!$actor->isSuperAdmin()) {
            return false;
        }

        // Super admin cannot change their own super-admin role via this path.
        if ($target->isSuperAdmin() && $actor->id === $target->id) {
            return false;
        }

        return true;
    }

    /**
     * Delete a user.
     *
     * Mirrors AdminController::deleteUser:
     *   - super admin only,
     *   - cannot delete self,
     *   - cannot delete other super admins,
     *   - cannot delete the final super admin account (the controller
     *     also enforces this defensively).
     */
    public function delete(User $actor, User $target): bool
    {
        if (!$actor->isSuperAdmin()) {
            return false;
        }

        if ($actor->id === $target->id) {
            return false;
        }

        if ($target->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
