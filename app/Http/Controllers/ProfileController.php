<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    private const AVATAR_SIZES = [64, 128, 256];

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'connectedProviders' => $request
                ->user()
                ->socialAccounts()
                ->pluck('provider')
                ->toArray(),
        ]);
    }

    /**
     * Story 2.6: Upload and resize avatar to 64/128/256px.
     */
    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        $user = $request->user();
        $file = $request->file('avatar');
        $base = 'avatars/' . $user->id;

        // Delete old avatars
        foreach (self::AVATAR_SIZES as $size) {
            Storage::disk('public')->delete("{$base}/{$size}.jpg");
        }

        $manager = new ImageManager(new Driver());
        foreach (self::AVATAR_SIZES as $size) {
            $img = $manager
                ->read($file->getRealPath())
                ->cover($size, $size)
                ->toJpeg(90);

            Storage::disk('public')->put("{$base}/{$size}.jpg", $img);
        }

        $user->update(['avatar' => Storage::url("{$base}/128.jpg")]);

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
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

        return Redirect::to('/');
    }
}
