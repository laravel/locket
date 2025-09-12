<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LinkNote>
 */
class LinkNoteFactory extends Factory
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
            'note' => fake()->paragraph(),
        ];
    }

    /**
     * Create a short note.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => fake()->sentence(),
        ]);
    }

    /**
     * Create a detailed note.
     */
    public function detailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => fake()->paragraphs(3, true),
        ]);
    }
}
