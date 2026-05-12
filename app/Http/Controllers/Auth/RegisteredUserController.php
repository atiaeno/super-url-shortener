<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Setting;
use App\Models\User;
use App\Rules\NotDisposableEmail;
use App\Services\CaptchaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $captcha = app(\App\Services\CaptchaService::class);

        // Simplified logic: Show CAPTCHA only if global CAPTCHA is enabled AND register page checkbox is checked
        $globalCaptchaEnabled = $captcha->isEnabled();
        $registerCaptchaSetting = Setting::get('captcha_register', 'false');
        $registerCaptchaEnabled = filter_var($registerCaptchaSetting, FILTER_VALIDATE_BOOLEAN);
        $showCaptcha = $globalCaptchaEnabled && $registerCaptchaEnabled;

        return Inertia::render('Auth/Register', [
            'recaptchaSiteKey' => $showCaptcha ? $captcha->siteKey() : '',
            'captchaProvider' => $captcha->getProviderType(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class, new NotDisposableEmail],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'referral_code' => ['nullable', 'string', 'max:50'],
            'recaptcha_token' => ['nullable', 'string'],
        ]);

        // Verify CAPTCHA token server-side if enabled
        $captcha = app(CaptchaService::class);
        if (!$captcha->verify($request->input('recaptcha_token'), $request->ip())) {
            throw ValidationException::withMessages([
                'recaptcha_token' => 'CAPTCHA verification failed. Please try again.',
            ]);
        }

        // Validate and process referral code if provided
        $referredByAffiliateId = null;
        $referralCode = $request->input('referral_code')
            ?: $request->input('ref')
            ?: session('referral_code');

        if ($referralCode) {
            $referralCode = strtoupper(trim($referralCode));
            $referringAffiliate = Affiliate::where('referral_code', $referralCode)
                ->where('is_active', true)
                ->first();

            if (!$referringAffiliate) {
                throw ValidationException::withMessages([
                    'referral_code' => 'Invalid referral code.',
                ]);
            }

            $referredByAffiliateId = $referringAffiliate->id;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referred_by_affiliate_id' => $referredByAffiliateId,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Clear referral code from session after successful registration
        session()->forget('referral_code');

        return redirect(route('dashboard', absolute: false));
    }
}
