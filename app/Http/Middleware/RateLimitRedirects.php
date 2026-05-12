<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitRedirects
{
    private const MAX_REQUESTS = 60;
    private const WINDOW_SECONDS = 60;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'rate_limit:redirect:' . hash('sha256', $request->ip());
        $hits = (int) Cache::get($key, 0);

        if ($hits >= self::MAX_REQUESTS) {
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
            ], 429)->withHeaders([
                'Retry-After' => self::WINDOW_SECONDS,
                'X-RateLimit-Limit' => self::MAX_REQUESTS,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        if ($hits === 0) {
            Cache::put($key, 1, self::WINDOW_SECONDS);
        } else {
            Cache::increment($key);
        }

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', self::MAX_REQUESTS);
        $response->headers->set('X-RateLimit-Remaining', max(0, self::MAX_REQUESTS - $hits - 1));
        return $response;
    }
}
