<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: a full valid registration payload (all fields the
     * RegisteredUserController validates).
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Test User',
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'date_of_birth'         => '1990-01-01',
            'address'               => 'Teststrasse 1',
            'postal_code'           => '8000',
            'city'                  => 'Zurich',
            'marital_status'        => 'single',
            'phone_number'          => '+41791234567',
            'email'                 => 'test-' . uniqid() . '@example.test',
            'password'              => 'secret-pass-12',
            'password_confirmation' => 'secret-pass-12',
        ], $overrides);
    }

    public function test_guest_can_access_registration_page(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_user_can_register_and_gets_role_user(): void
    {
        $payload = $this->validPayload();

        $response = $this->post('/register', $payload);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', $payload['email'])->first();
        $this->assertNotNull($user, 'user was not created');
        $this->assertSame(User::ROLE_USER, $user->role);
    }

    /**
     * Regression test for SECURITY_AUDIT_REPORT_2026-04-22 finding 3.3.
     *
     * 'role' is not in $fillable. A user who POSTs role=super_admin
     * to /register must still end up with role=user, never elevated.
     */
    public function test_registration_ignores_role_mass_assignment(): void
    {
        $payload = $this->validPayload([
            'email' => 'elevate-attempt@example.test',
            'role'  => User::ROLE_SUPER_ADMIN,
        ]);

        $this->post('/register', $payload);

        $user = User::where('email', 'elevate-attempt@example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame(
            User::ROLE_USER,
            $user->role,
            'Mass-assigned role on /register leaked into user record — $fillable guard failed.'
        );
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        // Seed an existing user WITHOUT logging in the test client —
        // if we use POST /register the client becomes authenticated
        // and the guest middleware on /register would redirect the
        // second attempt to /dashboard, masking the validation.
        User::factory()->create(['email' => 'dup@example.test']);

        $payload  = $this->validPayload(['email' => 'dup@example.test']);
        $response = $this->from('/register')->post('/register', $payload);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'dup@example.test')->count());
    }

    public function test_registration_requires_password_confirmation_match(): void
    {
        $payload = $this->validPayload([
            'password'              => 'secret-pass-12',
            'password_confirmation' => 'different-pass-99',
        ]);

        $response = $this->from('/register')->post('/register', $payload);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertSame(0, User::where('email', $payload['email'])->count());
    }
}
