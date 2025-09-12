<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Database\Seeder;

class LinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create some users
        $users = User::factory(5)->create();

        // Create some popular links that multiple users have bookmarked
        $popularLinks = Link::factory(10)->create();

        // Create additional links
        Link::factory(20)->create();

        // Create user-link relationships for popular links
        foreach ($popularLinks as $link) {
            // Each popular link is bookmarked by 2-4 users
            $bookmarkerCount = fake()->numberBetween(2, 4);
            $bookmarkers = $users->random($bookmarkerCount);

            foreach ($bookmarkers as $user) {
                $userLink = UserLink::factory()->create([
                    'user_id' => $user->id,
                    'link_id' => $link->id,
                ]);

                // 50% chance of having notes
                if (fake()->boolean()) {
                    LinkNote::factory(fake()->numberBetween(1, 3))->create([
                        'user_id' => $user->id,
                        'link_id' => $link->id,
                    ]);
                }
            }
        }

        // Create additional user-link relationships for remaining links
        $remainingLinks = Link::whereNotIn('id', $popularLinks->pluck('id'))->get();

        foreach ($users as $user) {
            // Each user bookmarks 5-15 additional links
            $bookmarkCount = fake()->numberBetween(5, 15);
            $linksToBookmark = $remainingLinks->random($bookmarkCount);

            foreach ($linksToBookmark as $link) {
                $userLink = UserLink::factory()->create([
                    'user_id' => $user->id,
                    'link_id' => $link->id,
                ]);

                // 30% chance of having notes for these
                if (fake()->boolean(30)) {
                    LinkNote::factory(fake()->numberBetween(1, 2))->create([
                        'user_id' => $user->id,
                        'link_id' => $link->id,
                    ]);
                }
            }
        }

        $this->command->info('Created links with user relationships and notes');
    }
}
