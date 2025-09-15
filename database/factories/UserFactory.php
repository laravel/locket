<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'sarah_chen', 'alex_martinez', 'jamie_taylor', 'dev_mike',
            'techie_sam', 'code_warrior', 'design_guru', 'startup_founder',
            'data_scientist', 'fullstack_dev', 'mobile_expert', 'ai_researcher',
            'product_manager', 'ux_designer', 'backend_ninja', 'frontend_wizard',
            'devops_engineer', 'security_expert', 'growth_hacker', 'tech_lead',
        ];

        return [
            'name' => fake()->randomElement($names),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
