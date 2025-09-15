<?php

declare(strict_types=1);

namespace App\Traits;

use App\Auth\SanctumTokenManager;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\NewAccessToken;

/**
 * Unified token management for both Passport and Sanctum.
 *
 * This trait allows a User model to work with both authentication systems
 * without property conflicts. It uses Passport's HasApiTokens trait directly
 * and delegates Sanctum operations to a separate SanctumTokenManager instance.
 *
 * Key Features:
 * - Passport: Uses standard $accessToken property and all normal methods
 * - Sanctum: Uses separate $currentSanctumToken property to avoid conflicts
 * - Smart Context Detection: Methods automatically route to correct token system
 * - Full Backward Compatibility: All existing code continues working
 */
trait HasAllTokens
{
    use \Laravel\Passport\HasApiTokens;   // Only Passport here - no conflicts!

    /** Lazily-created bridge for Sanctum operations */
    protected ?SanctumTokenManager $sanctum = null;

    /** Separate storage for current Sanctum token to avoid Passport conflicts */
    protected $currentSanctumToken;

    /* -------- public "superset" API -------- */

    // Note: We don't override Passport methods anymore
    // Instead, we provide explicit Sanctum alternatives below

    /* -------- Sanctum-specific methods for backward compatibility -------- */

    /**
     * Wrapper for Sanctum-specific token creation
     */
    public function createSanctumToken(string $name, array $abilities = ['*'], ?\DateTimeInterface $expiresAt = null): NewAccessToken
    {
        return $this->sanctum()->createToken($name, $abilities, $expiresAt);
    }

    /**
     * Wrapper for Sanctum-specific tokens relationship
     */
    public function sanctumTokens(): MorphMany
    {
        return $this->sanctum()->tokens();
    }

    /* -------- Token Routing for Dual Authentication -------- */

    /**
     * Override withAccessToken to prevent Sanctum from interfering with Passport.
     *
     * When Sanctum's middleware tries to set a token, we route it to our
     * separate storage to avoid conflicts with Passport's $accessToken property.
     */
    public function withAccessToken(?\Laravel\Passport\Contracts\ScopeAuthorizable $accessToken): static
    {
        // If it's null or a Passport token, delegate to parent
        if ($accessToken === null || ! $this->isSanctumToken($accessToken)) {
            return parent::withAccessToken($accessToken);
        }

        // If we reach here, it's likely a Sanctum token that doesn't match the type
        // In this case, we'll store it separately and return $this
        $this->currentSanctumToken = $accessToken;

        return $this;
    }

    /**
     * Alternative method for Sanctum to set its token without type conflicts.
     *
     * This method can be called by Sanctum middleware or other Sanctum code
     * to set the current Sanctum token without interfering with Passport.
     */
    public function setSanctumAccessToken($token): static
    {
        $this->currentSanctumToken = $token;
        $this->sanctum()->withAccessToken($token);

        return $this;
    }

    /**
     * Get the current Sanctum access token.
     *
     * This provides access to Sanctum tokens without interfering with
     * Passport's currentAccessToken() method.
     */
    public function currentSanctumToken()
    {
        return $this->currentSanctumToken;
    }

    /* -------- helper internals -------- */

    protected function sanctum(): SanctumTokenManager
    {
        return $this->sanctum ??= new SanctumTokenManager($this);
    }

    /**
     * Determine if a given token is a Sanctum token.
     *
     * We can identify Sanctum tokens by:
     * - Instance of Laravel\Sanctum\PersonalAccessToken
     * - Having a 'tokenable_type' property (Sanctum's polymorphic relationship)
     * - Table name being 'personal_access_tokens'
     */
    protected function isSanctumToken($token): bool
    {
        if (! $token) {
            return false;
        }

        // Check if it's a Sanctum PersonalAccessToken instance
        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            return true;
        }

        // Check for Sanctum-specific properties
        if (is_object($token) && property_exists($token, 'tokenable_type')) {
            return true;
        }

        // Check table name if it's a model
        if (method_exists($token, 'getTable') && $token->getTable() === 'personal_access_tokens') {
            return true;
        }

        return false;
    }
}
