<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class HandleReferralCode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only handle guests (non-authenticated users)
        if (!\Illuminate\Support\Facades\Auth::check()) {
            $referralCode = $request->input('ref');

            // Store referral code in session if it exists and user hasn't registered yet
            if ($referralCode && !session()->has('referral_code')) {
                session(['referral_code' => $referralCode]);
            }
        }

        return $next($request);
    }
}
