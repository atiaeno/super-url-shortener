<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use App\Services\CaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        $captcha = app(\App\Services\CaptchaService::class);

        // Simplified logic: Show CAPTCHA only if global CAPTCHA is enabled AND login page checkbox is checked
        $globalCaptchaEnabled = $captcha->isEnabled();
        $loginCaptchaSetting = Setting::get('captcha_login', 'false');
        $loginCaptchaEnabled = filter_var($loginCaptchaSetting, FILTER_VALIDATE_BOOLEAN);
        $showCaptcha = $globalCaptchaEnabled && $loginCaptchaEnabled;

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'recaptchaSiteKey' => $showCaptcha ? $captcha->siteKey() : '',
            'captchaProvider' => $captcha->getProviderType(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
