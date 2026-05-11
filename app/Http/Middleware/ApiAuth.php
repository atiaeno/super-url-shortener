<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API token is required.',
            ], 401);
        }

        $apiToken = ApiToken::where('token', $token)
            ->valid()
            ->first();

        if (!$apiToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired API token.',
            ], 401);
        }

        // Update last used timestamp (throttled to once per minute)
        if ($apiToken->last_used_at === null || $apiToken->last_used_at->diffInMinutes(now()) >= 1) {
            $apiToken->markAsUsed();
        }

        // Set authenticated user for the request
        $request->setUserResolver(fn() => $apiToken->user);

        // Set the user in Laravel's auth system
        $guard = app('auth')->guard();
        $guard->setUser($apiToken->user);

        // Also set in the request's user resolver for consistency
        app('auth')->setUser($apiToken->user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        // Only check Authorization header (Bearer token)
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }
}
