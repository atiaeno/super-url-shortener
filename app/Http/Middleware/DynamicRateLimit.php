<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class DynamicRateLimit
{
    public function handle(Request $request, Closure $next, string $limitName): Response
    {
        $key = $this->resolveRequestSignature($request, $limitName);
        
        $maxAttempts = $this->getMaxAttempts($limitName);
        $decayMinutes = $this->getDecayMinutes($limitName);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Too many requests.',
                'message' => 'Rate limit exceeded. Try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds,
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addSeconds($seconds)->timestamp,
            ]);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        $remaining = max(0, $maxAttempts - RateLimiter::attempts($key));
        
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);
        
        return $response;
    }
    
    protected function resolveRequestSignature(Request $request, string $limitName): string
    {
        if ($user = $request->user()) {
            return sha1($user->id . '|' . $request->ip() . '|' . $limitName);
        }
        
        return sha1($request->ip() . '|' . $limitName);
    }
    
    protected function getMaxAttempts(string $limitName): int
    {
        return match($limitName) {
            'api' => intval(Setting::get('api_rate_limit_per_hour', 100)),
            'api.tokens' => intval(Setting::get('api_token_rate_limit_per_hour', 10)),
            'api.sensitive' => max(10, intval(Setting::get('api_token_rate_limit_per_hour', 10))),
            default => 60,
        };
    }
    
    protected function getDecayMinutes(string $limitName): int
    {
        return match($limitName) {
            'api', 'api.tokens', 'api.sensitive' => 60, // 1 hour
            'guest' => 60,
            'redirect' => 1, // 1 minute for redirects
            default => 60,
        };
    }
}
