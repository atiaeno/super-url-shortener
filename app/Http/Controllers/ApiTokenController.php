<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    /**
     * Show API tokens page.
     */
    public function index(): Response
    {
        $tokens = ApiToken::where('user_id', auth()->id())
            ->select('id', 'name', 'last_used_at', 'expires_at', 'created_at')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Profile/ApiTokens', [
            'tokens' => $tokens,
        ]);
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'expires_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $token = Str::random(64);

        $apiToken = ApiToken::create([
            'user_id' => auth()->id(),
            'token' => $token,
            'name' => $request->input('name', 'API Token'),
            'expires_at' => $request->has('expires_days')
                ? now()->addDays($request->input('expires_days'))
                : null,
        ]);

        // Store the plain token in session to display once
        return redirect()->route('profile.api-tokens')
            ->with('new_token', $token)
            ->with('new_token_name', $apiToken->name);
    }

    /**
     * Revoke an API token.
     */
    public function destroy(int $id): RedirectResponse
    {
        $token = ApiToken::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$token) {
            return back()->with('error', 'Token not found.');
        }

        $token->delete();

        return back()->with('success', 'Token revoked successfully.');
    }
}
