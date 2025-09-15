<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\HasApiTokens as SanctumTrait;
use Laravel\Sanctum\Sanctum;

/**
 * A delegate helper that manages Sanctum token operations
 * without importing Sanctum's $accessToken property into the User model.
 *
 * This manager works seamlessly with the HasAllTokens trait's dual token
 * storage system, ensuring Sanctum tokens are handled separately from
 * Passport tokens while maintaining full API compatibility.
 */
final class SanctumTokenManager
{
    use SanctumTrait {
        // expose the trait's internals under unique names
        tokens as private traitTokens;
        createToken as traitCreateToken;
        currentAccessToken as traitCurrentAccessToken;
        withAccessToken as traitWithAccessToken;
        tokenCan as traitTokenCan;
        tokenCant as traitTokenCant;
    }

    public function __construct(private Authenticatable&Model $owner)
    {
        // nothing else – the trait methods will call $this->
        // so we forward relations back to the owner model.
    }

    /* ---------- relation helpers ---------- */

    // Sanctum's original tokens() uses $this->morphMany().
    // We override it to call morphMany on the *real* User
    // so the morph_type column still stores `App\Models\User`.
    public function tokens(): MorphMany
    {
        return $this->owner->morphMany(
            Sanctum::$personalAccessTokenModel,
            'tokenable'
        );
    }

    /* ---------- simple pass-throughs ---------- */

    public function createToken(...$args)
    {
        return $this->traitCreateToken(...$args);
    }

    public function currentAccessToken()
    {
        return $this->traitCurrentAccessToken();
    }

    public function withAccessToken($t)
    {
        return $this->traitWithAccessToken($t);
    }

    public function tokenCan(string $a): bool
    {
        return $this->traitTokenCan($a);
    }

    public function tokenCant(string $a): bool
    {
        return $this->traitTokenCant($a);
    }
}
