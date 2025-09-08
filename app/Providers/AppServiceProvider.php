<?php

namespace App\Providers;

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

        // Register the authorization view for OAuth consent screen
        /**
         * @param  array{client: \Laravel\Passport\Client, user: \App\Models\User, scopes: array<string>, request: \Illuminate\Http\Request, authToken: string}  $parameters
         */
        Passport::authorizationView(function ($parameters) {
            return view('auth.authorize', $parameters);
        });
    }
}
