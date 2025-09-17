<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Jobs\FetchLinkTitle;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AddLink
{
    /**
     * Add a link (or find existing) and create user bookmark.
     */
    public function handle(string $url, User $user, ?string $categoryHint = null): array
    {
        $validator = Validator::make(['url' => $url], [
            'url' => 'required|url|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $link = Link::firstOrCreate(
            ['url' => $url],
            [
                'title' => $this->extractTitleFromUrl($url),
                'category' => $this->suggestCategory($url, $categoryHint),
                'submitted_by_user_id' => $user->id,
            ]
        );

        if ($link->wasRecentlyCreated) {
            FetchLinkTitle::dispatch($link);
        }

        $userLink = UserLink::where('user_id', $user->id)
            ->where('link_id', $link->id)
            ->first();

        if (! $userLink) {
            $userLink = UserLink::create([
                'user_id' => $user->id,
                'link_id' => $link->id,
                'category' => $categoryHint ? LinkCategory::from($categoryHint) : $link->category,
                'status' => LinkStatus::UNREAD,
            ]);
        }

        return [
            'link' => [
                'id' => $link->id,
                'url' => $link->url,
                'title' => $link->title,
                'description' => $link->description,
                'category' => $link->category->value,
            ],
            'user_link' => [
                'id' => $userLink->id,
                'category' => $userLink->category->value,
                'status' => $userLink->status->value,
                'created_at' => $userLink->created_at->toISOString(),
            ],
            'already_bookmarked' => $userLink->wasRecentlyCreated === false,
        ];
    }

    /**
     * Extract a basic title from URL.
     */
    private function extractTitleFromUrl(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if ($path && $path !== '/') {
            $pathParts = array_filter(explode('/', $path));
            $lastPart = end($pathParts);

            // Clean up the last part of the path...
            $title = ucwords(str_replace(['-', '_', '.html', '.php'], ' ', $lastPart));

            if (strlen($title) > 3) {
                return $title;
            }
        }

        return $domain ? ucfirst(str_replace('www.', '', $domain)) : 'Unknown';
    }

    /**
     * Suggest category based on URL patterns or hint.
     */
    private function suggestCategory(string $url, ?string $categoryHint): LinkCategory
    {
        // Use provided hint if valid...
        if ($categoryHint && in_array($categoryHint, ['read', 'reference', 'watch', 'tools'])) {
            return LinkCategory::from($categoryHint);
        }

        $url = strtolower($url);

        // Video platforms...
        if (str_contains($url, 'youtube.com') ||
            str_contains($url, 'vimeo.com') ||
            str_contains($url, 'twitch.tv')) {
            return LinkCategory::WATCH;
        }

        // Documentation sites...
        if (str_contains($url, 'docs.') ||
            str_contains($url, '/docs/') ||
            str_contains($url, 'api.') ||
            str_contains($url, 'developer.')) {
            return LinkCategory::REFERENCE;
        }

        // Tool / service sites...
        if (str_contains($url, 'github.com') ||
            str_contains($url, 'npm.') ||
            str_contains($url, 'packagist.org')) {
            return LinkCategory::TOOLS;
        }

        // Default to read...
        return LinkCategory::READ;
    }
}
