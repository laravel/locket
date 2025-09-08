<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    /**
     * Get the user that owns the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
