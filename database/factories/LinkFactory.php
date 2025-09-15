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
        // Create realistic tech/programming URLs and content
        $domains = [
            'dev.to', 'medium.com', 'github.com', 'stackoverflow.com',
            'css-tricks.com', 'smashingmagazine.com', 'a11yproject.com',
            'web.dev', 'developer.mozilla.org', 'reactjs.org', 'vuejs.org',
            'angular.io', 'nodejs.org', 'laravel.com', 'tailwindcss.com',
            'stripe.com', 'vercel.com', 'netlify.com', 'firebase.google.com',
        ];

        $titles = [
            'Building Modern React Applications with TypeScript',
            'Laravel 11 New Features and Performance Improvements',
            'CSS Grid Layout: A Complete Guide',
            'Introduction to Machine Learning with Python',
            'Deploying Applications with Docker and Kubernetes',
            'Advanced JavaScript Design Patterns',
            'Building Accessible Web Components',
            'Vue 3 Composition API Best Practices',
            'Node.js Performance Optimization Techniques',
            'Database Design for Scalable Applications',
            'API Security Best Practices',
            'Tailwind CSS Advanced Techniques',
            'GraphQL vs REST: When to Choose What',
            'Building Progressive Web Apps',
            'Microservices Architecture Patterns',
            'Frontend Testing Strategies',
            'DevOps Pipeline Automation',
            'Serverless Functions at Scale',
            'Web Performance Optimization',
            'Modern Authentication Methods',
        ];

        $descriptions = [
            'A comprehensive guide to building scalable and maintainable applications using modern development practices.',
            'Learn about the latest features and performance improvements that make development faster and more efficient.',
            'Step-by-step tutorial with practical examples and real-world use cases.',
            'Deep dive into advanced concepts with code examples and best practices.',
            'Complete walkthrough from setup to deployment with detailed explanations.',
            'Practical tips and tricks for optimizing your development workflow.',
            'Best practices for building user-friendly and accessible applications.',
            'Learn how to implement modern patterns in your next project.',
            'Performance tips that will make your applications run faster and smoother.',
            'Industry insights and lessons learned from real-world implementations.',
        ];

        $domain = fake()->randomElement($domains);
        $path = strtolower(str_replace(' ', '-', fake()->randomElement($titles)));
        $randomString = fake()->lexify('????'); // Add random string to ensure uniqueness

        return [
            'url' => "https://{$domain}/{$path}-{$randomString}",
            'title' => fake()->randomElement($titles),
            'description' => fake()->randomElement($descriptions),
            'category' => fake()->randomElement(LinkCategory::cases()),
            'submitted_by_user_id' => User::factory(),
            'metadata' => [
                'og_title' => fake()->randomElement($titles),
                'og_description' => fake()->randomElement($descriptions),
                'og_image' => 'https://via.placeholder.com/1200x630/3B82F6/FFFFFF?text='.urlencode('Tech Article'),
                'scraped_at' => fake()->dateTimeBetween('-30 days', 'now')->format('c'),
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
