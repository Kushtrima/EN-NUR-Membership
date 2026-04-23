<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'first_name'        => fake()->firstName(),
            'last_name'         => fake()->lastName(),
            'date_of_birth'     => fake()->date('Y-m-d', '-20 years'),
            'address'           => fake()->streetAddress(),
            'postal_code'       => fake()->postcode(),
            'city'              => fake()->city(),
            'marital_status'    => fake()->randomElement(['single', 'married']),
            'phone_number'      => '+41' . fake()->numerify('#########'),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Produce a user with a specific role. role is NOT in $fillable,
     * so we set it explicitly after create — same pattern used by
     * application code (see AdminController::setupExpiredMemberships
     * and others).
     */
    public function withRole(string $role): self
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->role = $role;
            $user->save();
        });
    }

    public function unverified(): self
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
