<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LinkCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'url',
        'title',
        'description',
        'category',
        'submitted_by_user_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'category' => LinkCategory::class,
        'metadata' => 'array',
    ];

    /**
     * Get the user who submitted this link.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /**
     * Get all user relationships with this link.
     */
    public function userLinks(): HasMany
    {
        return $this->hasMany(UserLink::class);
    }

    /**
     * Get all notes for this link.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LinkNote::class);
    }

    /**
     * Get users who have bookmarked this link.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_links')
            ->withPivot(['category', 'status', 'created_at', 'updated_at', 'deleted_at'])
            ->withTimestamps();
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory(Builder $query, LinkCategory $category): void
    {
        $query->where('category', $category);
    }

    /**
     * Scope to get recent links.
     */
    public function scopeRecent(Builder $query): void
    {
        $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to get popular links (most bookmarked).
     */
    public function scopePopular(Builder $query): void
    {
        $query->withCount('userLinks')
            ->orderBy('user_links_count', 'desc');
    }
}
