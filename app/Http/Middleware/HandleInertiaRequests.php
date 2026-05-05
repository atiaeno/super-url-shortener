<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $captcha = app(CaptchaService::class);

        $toasts = [];
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            $message = $request->session()->get($type);
            if ($message) {
                $toasts[] = ['id' => uniqid(), 'type' => $type, 'message' => $message];
            }
        }

        // Add validation errors as error toasts
        $errors = $request->session()->get('errors');
        if ($errors && $errors->any()) {
            foreach ($errors->all() as $message) {
                $toasts[] = ['id' => uniqid(), 'type' => 'error', 'message' => $message];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'recaptchaEnabled' => $captcha->isEnabled(),
            'recaptchaSiteKey' => $captcha->isEnabled() ? $captcha->siteKey() : '',
            'flash' => [
                'toast' => $toasts,
                'new_token' => $request->session()->get('new_token'),
                'new_token_name' => $request->session()->get('new_token_name'),
            ],
        ];
    }
}
