# Laravel Action Pattern

- This application uses the Action pattern and business logic should be encapsulated in reusable and composable Action classes.
- Actions live in `app/Actions/`, they are named based on what they do, with no suffix.
- Actions will be called from many different places: jobs, commands, HTTP requests, API requests, MCP requests, and more.
- Create dedicated Action classes for business logic with a single `handle()` method.
- Inject dependencies via constructor using protected properties.
- Create new actions with `php artisan make:class "App\Actions\{name}" --no-interaction`
- Wrap complex operations in `DB::transaction()` within actions when multiple models are involved.
- Some actions won't require dependencies via `__construct` and they can use just the `handle()` method.
- `handle()` should always have a non-void return type.

@boostsnippet('Example action class', 'php')
<?php
declare(strict_types=1);

namespace App\Actions;

final class CreateFavorite {
    public function __construct(protected FavoriteApi $fav) { }

    public function handle(User $user, string $favorite): bool
    {
        return $this->fav->add($user, $favorite);
    }
}
@endboostsnippet
