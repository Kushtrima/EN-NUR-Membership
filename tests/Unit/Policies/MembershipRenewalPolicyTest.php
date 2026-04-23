<?php

namespace Tests\Unit\Policies;

use App\Models\MembershipRenewal;
use App\Models\User;
use App\Policies\MembershipRenewalPolicy;
use PHPUnit\Framework\TestCase;

class MembershipRenewalPolicyTest extends TestCase
{
    private MembershipRenewalPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new MembershipRenewalPolicy();
    }

    private function userWithRole(string $role): User
    {
        $user = new User();
        $user->id = 1;
        $user->role = $role;
        return $user;
    }

    // view

    public function test_super_admin_can_view_renewal(): void
    {
        $this->assertTrue($this->policy->view(
            $this->userWithRole(User::ROLE_SUPER_ADMIN),
            new MembershipRenewal(),
        ));
    }

    public function test_regular_admin_cannot_view_renewal(): void
    {
        $this->assertFalse($this->policy->view(
            $this->userWithRole(User::ROLE_ADMIN),
            new MembershipRenewal(),
        ));
    }

    public function test_regular_user_cannot_view_renewal(): void
    {
        $this->assertFalse($this->policy->view(
            $this->userWithRole(User::ROLE_USER),
            new MembershipRenewal(),
        ));
    }

    // adminWrite

    public function test_super_admin_can_perform_admin_write(): void
    {
        $this->assertTrue($this->policy->adminWrite(
            $this->userWithRole(User::ROLE_SUPER_ADMIN),
            new MembershipRenewal(),
        ));
    }

    public function test_regular_admin_cannot_perform_admin_write(): void
    {
        $this->assertFalse($this->policy->adminWrite(
            $this->userWithRole(User::ROLE_ADMIN),
            new MembershipRenewal(),
        ));
    }

    public function test_regular_user_cannot_perform_admin_write(): void
    {
        $this->assertFalse($this->policy->adminWrite(
            $this->userWithRole(User::ROLE_USER),
            new MembershipRenewal(),
        ));
    }
}
