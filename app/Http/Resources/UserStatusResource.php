<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserStatus
 */
class UserStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $email = strtolower(trim($this->user?->email ?? ''));
        $hash = md5($email);
        $gravatar = "https://www.gravatar.com/avatar/{$hash}?s=128&d=404";
        $fallback = 'https://avatars.laravel.cloud/'.urlencode($email).'?vibe=stealth';

        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at?->toAtomString(),
            'user' => [
                'name' => $this->user?->name ?? 'Unknown',
                'avatar' => $gravatar,
                'avatar_fallback' => $fallback,
            ],
            'link' => $this->link ? [
                'id' => $this->link->id,
                'url' => $this->link->url,
                'title' => $this->link->title,
                'description' => $this->link->description,
            ] : null,
        ];
    }
}
