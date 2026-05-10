<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'github', 'facebook'];

    /**
     * Redirect to OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback — create or login user.
     */
    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')->withErrors(['social' => 'OAuth authentication failed. Please try again.']);
        }

        // Find existing social account
        $social = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($social) {
            // Update tokens
            $social->update([
                'access_token' => encrypt($socialUser->token),
                'refresh_token' => $socialUser->refreshToken ? encrypt($socialUser->refreshToken) : null,
            ]);

            Auth::login($social->user, true);
            return redirect()->intended(route('dashboard'));
        }

        // Find user by email or create new
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),  // OAuth email is auto-verified
            ]
        );

        // Create social account link
        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'access_token' => encrypt($socialUser->token),
            'refresh_token' => $socialUser->refreshToken ? encrypt($socialUser->refreshToken) : null,
        ]);

        Auth::login($user, true);
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Disconnect an OAuth provider from the authenticated user.
     */
    public function disconnect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        $user = Auth::user();

        // Prevent disconnecting last auth method if no password
        $socialCount = $user->socialAccounts()->count();
        $hasPassword = !empty($user->password);

        if ($socialCount <= 1 && !$hasPassword) {
            return back()->withErrors(['social' => 'You must have at least one login method. Set a password before disconnecting.']);
        }

        $user
            ->socialAccounts()
            ->where('provider', $provider)
            ->delete();

        return back()->with('status', "{$provider} account disconnected.");
    }
}
