<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;
use App\Models\UserStatus;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds for demo purposes.
     */
    public function run(): void
    {
        // Create diverse users with realistic usernames
        $users = collect([
            ['name' => 'sarah_chen', 'email' => 'sarah@example.com'],
            ['name' => 'alex_martinez', 'email' => 'alex@example.com'],
            ['name' => 'dev_mike', 'email' => 'mike@example.com'],
            ['name' => 'techie_sam', 'email' => 'sam@example.com'],
            ['name' => 'design_guru', 'email' => 'guru@example.com'],
            ['name' => 'startup_founder', 'email' => 'founder@example.com'],
            ['name' => 'fullstack_dev', 'email' => 'fullstack@example.com'],
            ['name' => 'ai_researcher', 'email' => 'ai@example.com'],
            ['name' => 'product_manager', 'email' => 'pm@example.com'],
            ['name' => 'ux_designer', 'email' => 'ux@example.com'],
        ])->map(fn ($userData) => User::factory()->create($userData));

        // Create trending/popular links that multiple users have bookmarked
        $trendingLinks = collect([
            [
                'url' => 'https://laravel.com/docs/11.x/new-features',
                'title' => 'Laravel 11 New Features and Performance Improvements',
                'description' => 'Comprehensive overview of Laravel 11\'s groundbreaking features including improved performance, streamlined application structure, and enhanced developer experience.',
                'category' => LinkCategory::READ,
            ],
            [
                'url' => 'https://react.dev/learn/thinking-in-react',
                'title' => 'Thinking in React: A Complete Developer Guide',
                'description' => 'Master the React mindset with this step-by-step guide to building user interfaces the React way.',
                'category' => LinkCategory::READ,
            ],
            [
                'url' => 'https://web.dev/vitals/',
                'title' => 'Web Vitals: Essential Metrics for a Healthy Site',
                'description' => 'Learn about Core Web Vitals and how to optimize your site for the best user experience.',
                'category' => LinkCategory::REFERENCE,
            ],
            [
                'url' => 'https://tailwindcss.com/docs/utility-first',
                'title' => 'Tailwind CSS: Utility-First Fundamentals',
                'description' => 'Deep dive into utility-first CSS methodology and how it revolutionizes frontend development.',
                'category' => LinkCategory::REFERENCE,
            ],
            [
                'url' => 'https://www.youtube.com/watch?v=docker-tutorial',
                'title' => 'Docker for Developers: Complete Tutorial Series',
                'description' => 'Comprehensive video series covering Docker fundamentals, containerization strategies, and production deployment.',
                'category' => LinkCategory::WATCH,
            ],
        ])->map(function ($linkData) use ($users) {
            return Link::factory()->create(array_merge($linkData, [
                'submitted_by_user_id' => $users->random()->id,
            ]));
        });

        // Create additional diverse links
        $additionalLinks = Link::factory(25)->create([
            'submitted_by_user_id' => fn () => $users->random()->id,
        ]);

        $allLinks = $trendingLinks->concat($additionalLinks);

        // Create user-link relationships for trending links (higher bookmark counts)
        foreach ($trendingLinks as $link) {
            $bookmarkerCount = fake()->numberBetween(4, 7); // More bookmarks for trending
            $bookmarkers = $users->random($bookmarkerCount);

            foreach ($bookmarkers as $user) {
                $userLink = UserLink::factory()->create([
                    'user_id' => $user->id,
                    'link_id' => $link->id,
                    'status' => fake()->randomElement([
                        LinkStatus::UNREAD,
                        LinkStatus::READING,
                        LinkStatus::READ,
                        LinkStatus::REFERENCE,
                    ]),
                ]);

                // 70% chance of having notes for trending links
                if (fake()->boolean(70)) {
                    LinkNote::factory(fake()->numberBetween(1, 2))->create([
                        'user_id' => $user->id,
                        'link_id' => $link->id,
                    ]);
                }
            }
        }

        // Create user-link relationships for regular links
        foreach ($users as $user) {
            $bookmarkCount = fake()->numberBetween(8, 18);
            $linksToBookmark = $additionalLinks->random($bookmarkCount);

            foreach ($linksToBookmark as $link) {
                UserLink::factory()->create([
                    'user_id' => $user->id,
                    'link_id' => $link->id,
                    'status' => fake()->randomElement([
                        LinkStatus::UNREAD,
                        LinkStatus::READING,
                        LinkStatus::READ,
                        LinkStatus::REFERENCE,
                        LinkStatus::ARCHIVED,
                    ]),
                ]);

                // 40% chance of having notes for regular links
                if (fake()->boolean(40)) {
                    LinkNote::factory(fake()->numberBetween(1, 2))->create([
                        'user_id' => $user->id,
                        'link_id' => $link->id,
                    ]);
                }
            }
        }

        // Create user statuses (recent activity)
        foreach ($users->take(6) as $user) { // Only some users have recent activity
            $recentLinks = $allLinks->random(fake()->numberBetween(1, 3));

            foreach ($recentLinks as $link) {
                UserStatus::factory()->create([
                    'user_id' => $user->id,
                    'link_id' => $link->id,
                    'status' => fake()->randomElement([
                        'just bookmarked this article',
                        'is currently reading this',
                        'found this really helpful',
                        'shared this with the team',
                        'added notes to this',
                        'marked this as reference material',
                    ]),
                    'created_at' => fake()->dateTimeBetween('-2 days', 'now'),
                ]);
            }
        }

        $this->command->info('Demo data created successfully!');
        $this->command->info("Created {$users->count()} users");
        $this->command->info("Created {$allLinks->count()} links");
        $this->command->info('Created user-link relationships and notes');
        $this->command->info('Created recent user activities');
    }
}
