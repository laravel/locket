<?php

namespace App\Http\Controllers\Settings;

use App\Actions\CreateApiToken;
use App\Actions\RevokeApiToken;
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
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'tokens' => $request->user()->sanctumTokens()->orderBy('created_at', 'desc')->get(),
            'createdToken' => $request->session()->get('token'),
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
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

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
    public function createToken(CreateApiTokenRequest $request, CreateApiToken $createToken): RedirectResponse|JsonResponse
    {
        $newToken = $createToken->handle($request->user(), $request->validated()['name']);

        if ($request->wantsJson()) {
            return response()->json([
                'token' => $newToken->plainTextToken,
                'accessToken' => $newToken->accessToken->only(['id', 'name', 'last_used_at', 'created_at']),
            ]);
        }

        return back()->with([
            'token' => $newToken->plainTextToken,
            'tokenData' => $newToken->accessToken->only(['id', 'name', 'last_used_at', 'created_at']),
        ]);
    }

    /**
     * Revoke an API token.
     */
    public function revokeToken(Request $request, RevokeApiToken $revokeToken, int $tokenId): RedirectResponse|JsonResponse
    {
        $success = $revokeToken->handle($request->user(), $tokenId);

        if (! $success) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Token not found'], 404);
            }

            return back()->withErrors(['token' => 'Token not found or could not be revoked.']);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Token revoked successfully']);
        }

        return back()->with('message', 'Token revoked successfully.');
    }
}
