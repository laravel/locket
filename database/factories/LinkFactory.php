<?php

namespace Database\Factories;

use App\Enums\LinkCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $url = fake()->url();

        return [
            'url' => $url,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(LinkCategory::cases()),
            'submitted_by_user_id' => User::factory(),
            'metadata' => [
                'og_title' => fake()->sentence(),
                'og_description' => fake()->paragraph(),
                'og_image' => fake()->imageUrl(),
                'scraped_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Create a read-category link.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => LinkCategory::READ,
        ]);
    }

    /**
     * Create a reference-category link.
     */
    public function reference(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => LinkCategory::REFERENCE,
        ]);
    }

    /**
     * Create a watch-category link.
     */
    public function watch(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => LinkCategory::WATCH,
        ]);
    }

    /**
     * Create a tools-category link.
     */
    public function tools(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => LinkCategory::TOOLS,
        ]);
    }
}
