<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialiteController
{
    public function redirectToGitHub(): SymfonyRedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGitHubCallback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'GitHub authentication failed.']);
        }

        $user = User::where('github_id', $githubUser->getId())
            ->orWhere('email', $githubUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'github_id' => $githubUser->getId(),
                'github_username' => $githubUser->getNickname(),
                'avatar' => $githubUser->getAvatar(),
                'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                'email' => $githubUser->getEmail(),
            ]);
        } else {
            $user = User::create([
                'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                'email' => $githubUser->getEmail(),
                'github_id' => $githubUser->getId(),
                'github_username' => $githubUser->getNickname(),
                'avatar' => $githubUser->getAvatar(),
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('home'));
    }
}
