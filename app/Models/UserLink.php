<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLink extends Model
{
    /** @use HasFactory<\Database\Factories\UserLinkFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'link_id',
        'category',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'category' => LinkCategory::class,
        'status' => LinkStatus::class,
    ];

    /**
     * Get the user that owns this link relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the link in this relationship.
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    /**
     * Get all notes for this user-link relationship.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LinkNote::class, 'link_id', 'link_id');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory(Builder $query, LinkCategory $category): void
    {
        $query->where('category', $category);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus(Builder $query, LinkStatus $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope to get unread items.
     */
    public function scopeUnread(Builder $query): void
    {
        $query->where('status', LinkStatus::UNREAD);
    }

    /**
     * Scope to get reading items.
     */
    public function scopeReading(Builder $query): void
    {
        $query->where('status', LinkStatus::READING);
    }

    /**
     * Scope to get read items.
     */
    public function scopeRead(Builder $query): void
    {
        $query->where('status', LinkStatus::READ);
    }

    /**
     * Scope to get active (non-archived) items.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', '!=', LinkStatus::ARCHIVED);
    }

    /**
     * Check if the status can transition to a new status.
     */
    public function canTransitionTo(LinkStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    /**
     * Transition to a new status if allowed.
     */
    public function transitionTo(LinkStatus $newStatus): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;

        return $this->save();
    }
}
