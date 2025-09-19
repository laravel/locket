<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Resources\UserStatusResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User $user
 * @property-read Link $link
 */
class UserStatus extends Model
{
    /** @use HasFactory<\Database\Factories\UserStatusFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'status',
        'link_id',
    ];

    /**
     * Get the user that owns the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the link associated with this status.
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    /**
     * Convert this status to its frontend representation.
     *
     * @return array<string, mixed>
     */
    public function toFrontendFormat(): array
    {
        // Ensure user relationship is loaded
        $this->loadMissing('user');

        return UserStatusResource::make($this)->toArray(request());
    }
}
