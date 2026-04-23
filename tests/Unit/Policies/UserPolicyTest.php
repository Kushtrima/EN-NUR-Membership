<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class UserPolicyTest extends TestCase
{
    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    private function userWithRole(string $role, int $id = 1): User
    {
        $user = new User();
        $user->id = $id;
        $user->role = $role;
        return $user;
    }

    // view / export

    public function test_super_admin_can_view_any_user(): void
    {
        $this->assertTrue($this->policy->view(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_admin_can_view_any_user(): void
    {
        $this->assertTrue($this->policy->view(
            $this->userWithRole(User::ROLE_ADMIN, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_regular_user_cannot_view_another_user(): void
    {
        $this->assertFalse($this->policy->view(
            $this->userWithRole(User::ROLE_USER, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_super_admin_can_export_any_user(): void
    {
        $this->assertTrue($this->policy->export(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_regular_user_cannot_export(): void
    {
        $this->assertFalse($this->policy->export(
            $this->userWithRole(User::ROLE_USER, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    // updateRole

    public function test_super_admin_can_update_another_users_role(): void
    {
        $this->assertTrue($this->policy->updateRole(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_super_admin_cannot_update_own_super_admin_role(): void
    {
        $self = $this->userWithRole(User::ROLE_SUPER_ADMIN, 1);
        $this->assertFalse($this->policy->updateRole($self, $self));
    }

    public function test_regular_admin_cannot_update_roles(): void
    {
        $this->assertFalse($this->policy->updateRole(
            $this->userWithRole(User::ROLE_ADMIN, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_regular_user_cannot_update_roles(): void
    {
        $this->assertFalse($this->policy->updateRole(
            $this->userWithRole(User::ROLE_USER, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    // delete

    public function test_super_admin_can_delete_regular_user(): void
    {
        $this->assertTrue($this->policy->delete(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $self = $this->userWithRole(User::ROLE_SUPER_ADMIN, 1);
        $this->assertFalse($this->policy->delete($self, $self));
    }

    public function test_super_admin_cannot_delete_another_super_admin(): void
    {
        $this->assertFalse($this->policy->delete(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 2),
        ));
    }

    public function test_regular_admin_cannot_delete_users(): void
    {
        $this->assertFalse($this->policy->delete(
            $this->userWithRole(User::ROLE_ADMIN, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }

    public function test_regular_user_cannot_delete_anyone(): void
    {
        $this->assertFalse($this->policy->delete(
            $this->userWithRole(User::ROLE_USER, 1),
            $this->userWithRole(User::ROLE_USER, 2),
        ));
    }
}
