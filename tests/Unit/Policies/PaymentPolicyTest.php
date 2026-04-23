<?php

namespace Tests\Unit\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\PaymentPolicy;
use PHPUnit\Framework\TestCase;

class PaymentPolicyTest extends TestCase
{
    private PaymentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PaymentPolicy();
    }

    private function userWithRole(string $role, int $id = 1): User
    {
        $user = new User();
        $user->id = $id;
        $user->role = $role;
        return $user;
    }

    private function paymentFor(int $userId, string $status = Payment::STATUS_PENDING): Payment
    {
        $payment = new Payment();
        $payment->user_id = $userId;
        $payment->status  = $status;
        return $payment;
    }

    // view

    public function test_owner_can_view_own_payment(): void
    {
        $this->assertTrue($this->policy->view(
            $this->userWithRole(User::ROLE_USER, 42),
            $this->paymentFor(42),
        ));
    }

    public function test_non_owner_regular_user_cannot_view_payment(): void
    {
        $this->assertFalse($this->policy->view(
            $this->userWithRole(User::ROLE_USER, 99),
            $this->paymentFor(42),
        ));
    }

    public function test_admin_can_view_any_payment(): void
    {
        $this->assertTrue($this->policy->view(
            $this->userWithRole(User::ROLE_ADMIN, 1),
            $this->paymentFor(42),
        ));
    }

    public function test_super_admin_can_view_any_payment(): void
    {
        $this->assertTrue($this->policy->view(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $this->paymentFor(42),
        ));
    }

    // confirm

    public function test_owner_can_confirm_own_pending_payment(): void
    {
        $this->assertTrue($this->policy->confirm(
            $this->userWithRole(User::ROLE_USER, 42),
            $this->paymentFor(42, Payment::STATUS_PENDING),
        ));
    }

    public function test_owner_cannot_confirm_already_completed_payment(): void
    {
        $this->assertFalse($this->policy->confirm(
            $this->userWithRole(User::ROLE_USER, 42),
            $this->paymentFor(42, Payment::STATUS_COMPLETED),
        ));
    }

    public function test_non_owner_cannot_confirm_someone_elses_payment(): void
    {
        $this->assertFalse($this->policy->confirm(
            $this->userWithRole(User::ROLE_USER, 99),
            $this->paymentFor(42, Payment::STATUS_PENDING),
        ));
    }

    // adminWrite

    public function test_admin_has_admin_write_on_any_payment(): void
    {
        $this->assertTrue($this->policy->adminWrite(
            $this->userWithRole(User::ROLE_ADMIN, 1),
            $this->paymentFor(42),
        ));
    }

    public function test_super_admin_has_admin_write_on_any_payment(): void
    {
        $this->assertTrue($this->policy->adminWrite(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $this->paymentFor(42),
        ));
    }

    public function test_owner_cannot_admin_write_own_payment(): void
    {
        $this->assertFalse($this->policy->adminWrite(
            $this->userWithRole(User::ROLE_USER, 42),
            $this->paymentFor(42),
        ));
    }

    // delete

    public function test_only_super_admin_can_delete(): void
    {
        $payment = $this->paymentFor(42);

        $this->assertTrue($this->policy->delete(
            $this->userWithRole(User::ROLE_SUPER_ADMIN, 1),
            $payment,
        ));

        $this->assertFalse($this->policy->delete(
            $this->userWithRole(User::ROLE_ADMIN, 1),
            $payment,
        ));

        $this->assertFalse($this->policy->delete(
            $this->userWithRole(User::ROLE_USER, 42),
            $payment,
        ));
    }
}
