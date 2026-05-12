<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $captcha = app(\App\Services\CaptchaService::class);

        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
            'recaptchaSiteKey' => $captcha->isEnabled() ? $captcha->siteKey() : '',
        ]);
    }
}
