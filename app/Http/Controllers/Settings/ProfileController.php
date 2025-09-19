<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CreateApiTokenRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $tokens = $request->user()->tokens()
            ->where('revoked', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (\Laravel\Passport\Token $token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'created_at' => $token->created_at->toDateTimeString(),
                ];
            });

        // Get and immediately clear the created token from session
        $createdToken = session('created_token');
        if ($createdToken) {
            session()->forget('created_token');
        }

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'tokens' => $tokens,
            'createdToken' => $createdToken,
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Create a new API token.
     */
    public function createToken(CreateApiTokenRequest $request): JsonResponse|RedirectResponse
    {
        $tokenResult = $request->user()->createToken($request->validated()['name']);

        // For web/Inertia requests, store token in session then redirect
        session(['created_token' => $tokenResult->accessToken]);

        return back();
    }

    /**
     * Revoke an API token.
     */
    public function revokeToken(Request $request, string $tokenId): JsonResponse|RedirectResponse
    {
        $token = $request->user()->tokens()->where('id', $tokenId)->first();

        if (! $token) {
            return back()->withErrors(['token' => 'Token not found or could not be revoked.']);
        }

        $token->revoke();

        return back()->with('message', 'Token revoked successfully.');
    }
}
