<?php

namespace Database\Factories;

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserLink>
 */
class UserLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'link_id' => Link::factory(),
            'category' => fake()->randomElement(LinkCategory::cases()),
            'status' => fake()->randomElement(LinkStatus::cases()),
        ];
    }

    /**
     * Create an unread user link.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LinkStatus::UNREAD,
        ]);
    }

    /**
     * Create a reading user link.
     */
    public function reading(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LinkStatus::READING,
        ]);
    }

    /**
     * Create a read user link.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LinkStatus::READ,
        ]);
    }

    /**
     * Create an archived user link.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LinkStatus::ARCHIVED,
        ]);
    }

    /**
     * Create a reference user link.
     */
    public function reference(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => LinkCategory::REFERENCE,
            'status' => LinkStatus::REFERENCE,
        ]);
    }
}
