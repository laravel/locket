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
        $notes = [
            'This is a great resource for learning React hooks!',
            'Bookmarked for reference when working on the new project.',
            'Great explanation of the concept. Will implement this next week.',
            'Need to share this with the team - very relevant to our current work.',
            'Solid tutorial, though some parts could be more detailed.',
            'Perfect timing! Just what I needed for my current task.',
            'Excellent deep dive. The code examples are really helpful.',
            'This approach solved the exact problem I was facing.',
            'Adding to my weekend reading list. Looks comprehensive.',
            'Good overview but will need to research more advanced topics.',
            'The performance tips here are game-changing.',
            'Must read before starting the new feature implementation.',
            'Clear explanation with practical examples. Very useful.',
            'This will be helpful for the code review tomorrow.',
            'Interesting perspective on this architectural pattern.',
            'Need to experiment with this approach in a side project.',
            'Great resource for onboarding new team members.',
            'The security considerations mentioned here are important.',
            'This complements the book I\'m reading perfectly.',
            'Will definitely reference this during the refactoring.',
        ];

        return [
            'user_id' => User::factory(),
            'link_id' => Link::factory(),
            'note' => fake()->randomElement($notes),
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
