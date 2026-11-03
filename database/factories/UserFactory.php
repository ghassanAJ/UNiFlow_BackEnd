<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'university_id' => fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => fake()->unique()->password(12),
            'role' => Role::Student,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Admin,
        ]);
    }

    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Teacher,
        ]);
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Student,
        ]);
    }
}
