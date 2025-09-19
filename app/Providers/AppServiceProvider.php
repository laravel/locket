<?php

namespace App\Providers;

use App\Models\LinkNote;
use App\Models\UserLink;
use App\Models\UserStatus;
use App\Policies\LinkNotePolicy;
use App\Policies\UserLinkPolicy;
use App\Policies\UserStatusPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Register the authorization view for OAuth consent screen...
        /**
         * @param  array{client: \Laravel\Passport\Client, user: \App\Models\User, scopes: array<string>, request: \Illuminate\Http\Request, authToken: string}  $parameters
         */
        /** @phpstan-ignore-next-line */
        Passport::authorizationView(function ($parameters) {
            return view('auth.authorize', $parameters);
        });

        // Register authorization policies...
        Gate::policy(UserLink::class, UserLinkPolicy::class);
        Gate::policy(LinkNote::class, LinkNotePolicy::class);
        Gate::policy(UserStatus::class, UserStatusPolicy::class);
    }
}
