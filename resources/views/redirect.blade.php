{{-- © Atia Hegazy — atiaeno.com --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? config('app.name') }}</title>

    @if (!empty($ogTitle))
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription ?? '' }}">
        <meta property="og:url" content="{{ $ogUrl ?? '' }}">
        <meta property="og:type" content="website">
        @if (!empty($ogImage))
            <meta property="og:image" content="{{ $ogImage }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription ?? '' }}">
        @if (!empty($ogImage))
            <meta name="twitter:image" content="{{ $ogImage }}">
        @endif
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    @if (!empty($redirectCaptcha) && !empty($captchaSiteKey))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <link href="{{ asset('css/redirect.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Header Promotion (Top of page) -->
    @if ($headerPromotion)
        <div class="header-promotion">
            <div class="promotion-content" onclick="window.open('{{ $headerPromotion->target_url }}', '_blank')">
                {!! $headerPromotion->content !!}
            </div>
        </div>
    @endif

    <!-- Left Side Promotion -->
    @if ($leftSidePromotion)
        <div class="left-side-promotion">
            <div class="promotion-content" onclick="window.open('{{ $leftSidePromotion->target_url }}', '_blank')">
                {!! $leftSidePromotion->content !!}
            </div>
        </div>
    @endif

    <!-- Right Side Promotion -->
    @if ($rightSidePromotion)
        <div class="right-side-promotion">
            <div class="promotion-content" onclick="window.open('{{ $rightSidePromotion->target_url }}', '_blank')">
                {!! $rightSidePromotion->content !!}
            </div>
        </div>
    @endif

    <div class="shell">
        <div class="card">

            {{-- ── PASSWORD GATE ─────────────────────────── --}}
            @if ($state === 'password')
                <div class="icon-wrap icon-red">
                    <svg viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <span class="marker">Protected</span>
                <h1 class="heading">Password Required</h1>
                <p class="sub">This link is private. Enter the password to continue.</p>
                <form method="POST" action="{{ url('/' . $shortCode) }}" style="text-align:left">
                    @csrf
                    <input type="password" name="password" class="field-input" placeholder="Enter password…" autofocus
                        required>
                    @if (!empty($error))
                        <span class="field-error">Incorrect password. Please try again.</span>
                    @endif
                    <div style="margin-top:20px;text-align:center">
                        <button type="submit" class="btn btn-primary">Unlock</button>
                    </div>
                </form>

                {{-- ── REDIRECT PAGE (TIMER + OPTIONAL AD) ──── --}}
            @elseif($state === 'redirect')
                <span class="marker">Redirecting</span>

                <!-- Before Counter Promotion -->
                @if ($beforeCounterPromotion)
                    <div class="before-counter-promotion">
                        <div class="promotion-content"
                            onclick="window.open('{{ $beforeCounterPromotion->target_url }}', '_blank')">
                            {!! $beforeCounterPromotion->content !!}
                        </div>
                    </div>
                @endif
                @if ($countdown > 0)
                    <div class="ring-wrap" id="ringWrap">
                        <svg viewBox="0 0 80 80">
                            <circle class="ring-bg" cx="40" cy="40" r="36" />
                            <circle class="ring-fg" id="ringFg" cx="40" cy="40" r="36" />
                        </svg>
                        <span class="ring-num" id="ringNum">{{ $countdown }}</span>
                    </div>
                    <h1 class="heading">You'll be redirected shortly</h1>
                    <p class="sub">Please wait <strong id="secText"
                            style="color:var(--ink);font-style:normal">{{ $countdown }}</strong> seconds</p>
                @else
                    <h1 class="heading" style="margin-top:8px">Ready to redirect</h1>
                    <p class="sub">Click the button below to continue.</p>
                @endif
                <!-- Under Counter Promotion -->
                @if ($underCounterPromotion)
                    <div class="under-counter-promotion">
                        <div class="promotion-content"
                            onclick="window.open('{{ $underCounterPromotion->target_url }}', '_blank')">
                            {!! $underCounterPromotion->content !!}
                        </div>
                    </div>
                @endif

                @if (!empty($adContent))
                    <div class="promotion-wrap">
                        <div class="promotion-content">{!! $adContent !!}</div>
                    </div>
                @endif
                @if (!empty($redirectCaptcha) && !empty($captchaSiteKey))
                    <div id="captchaWrap" style="display:flex;justify-content:center;margin-bottom:20px">
                        <div class="g-recaptcha" data-sitekey="{{ $captchaSiteKey }}" data-callback="onCaptchaPass">
                        </div>
                    </div>
                @endif

                <!-- Above Button Promotion -->
                @if ($aboveButtonPromotion)
                    <div class="above-button-promotion">
                        <div class="promotion-content"
                            onclick="window.open('{{ $aboveButtonPromotion->target_url }}', '_blank')">
                            {!! $aboveButtonPromotion->content !!}
                        </div>
                    </div>
                @endif

                <a href="{{ $destination }}" class="btn btn-primary btn-skip" id="skipBtn">Continue to
                    Destination</a>

                <!-- Under Button Promotion -->
                @if ($underButtonPromotion)
                    <div class="under-button-promotion">
                        <div class="promotion-content"
                            onclick="window.open('{{ $underButtonPromotion->target_url }}', '_blank')">
                            {!! $underButtonPromotion->content !!}
                        </div>
                    </div>
                @endif

                <div class="dest-link">Destination: <a
                        href="{{ $destination }}">{{ Str::limit($destination, 60) }}</a></div>

                {{-- ── NOT FOUND / EXPIRED ───────────────────── --}}
            @elseif($state === 'not-found')
                <div class="icon-wrap icon-muted">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </div>
                <span class="marker">404</span>
                <h1 class="heading">Link Not Found</h1>
                <p class="sub">The short link <strong
                        style="color:var(--ink);font-style:normal">{{ $shortCode }}</strong> does not exist or has
                    been removed.</p>
                <a href="{{ url('/') }}" class="btn btn-ghost">Go Home</a>
            @elseif($state === 'expired')
                <div class="icon-wrap icon-muted">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
                <span class="marker">Expired</span>
                <h1 class="heading">This Link Has Expired</h1>
                <p class="sub">Expired on <strong
                        style="color:var(--ink);font-style:normal">{{ $expiresAt->format('M j, Y \a\t g:i A') }}</strong>
                    and is no longer available.</p>
                <a href="{{ url('/') }}" class="btn btn-ghost">Go Home</a>
            @endif

        </div>

        <!-- Footer Promotion -->


    </div>

    
        <div class="footer">
            @if ($footerPromotion)
            <div class="footer-promotion">
                <div class="promotion-content" onclick="window.open('{{ $footerPromotion->target_url }}', '_blank')">
                    {!! $footerPromotion->content !!}
                </div>
            </div>
        @endif
        </div>

    @if ($state === 'redirect')
        <script>
            (function() {
                var mode = '{{ $redirectMode ?? 'auto' }}';
                var needCaptcha = {{ !empty($redirectCaptcha) && !empty($captchaSiteKey) ? 'true' : 'false' }};
                var captchaPassed = false;
                var btn = document.getElementById('skipBtn');
                var dest = '{{ $destination }}';
                var total = {{ $countdown }};

                // Captcha callback — called by reCAPTCHA when user passes
                window.onCaptchaPass = function() {
                    captchaPassed = true;
                    if (btn.dataset.ready === 'true') btn.classList.add('active');
                };

                function enableButton() {
                    btn.dataset.ready = 'true';
                    if (!needCaptcha || captchaPassed) btn.classList.add('active');
                }

                function autoRedirect() {
                    if (needCaptcha && !captchaPassed) return;
                    window.location.href = dest;
                }

                if (total > 0) {
                    var circ = 2 * Math.PI * 36,
                        sec = total,
                        fg = document.getElementById('ringFg'),
                        num = document.getElementById('ringNum'),
                        txt = document.getElementById('secText');
                    fg.style.strokeDasharray = circ;
                    fg.style.strokeDashoffset = '0';
                    var t = setInterval(function() {
                        sec--;
                        num.textContent = sec;
                        txt.textContent = sec;
                        fg.style.strokeDashoffset = circ * (1 - sec / total);
                        if (sec <= 0) {
                            clearInterval(t);
                            num.textContent = '\u2713';
                            enableButton();
                            if (mode === 'auto') autoRedirect();
                        }
                    }, 1000);
                } else {
                    enableButton();
                }

                // Button click always navigates (if allowed)
                btn.addEventListener('click', function(e) {
                    if (!btn.classList.contains('active')) {
                        e.preventDefault();
                        return;
                    }
                });

                // Popup Promotion Logic
                @if ($popupPromotion)
                    (function() {
                        var popupData = {!! json_encode($popupPromotion) !!};
                        if (popupData && popupData.target_url && popupData.content) {
                            // Create popup overlay
                            var overlay = document.createElement('div');
                            overlay.className = 'popup-overlay';

                            var promotionContent = document.createElement('div');
                            promotionContent.className = 'popup-promotion';

                            var contentDiv = document.createElement('div');
                            contentDiv.className = 'promotion-content';
                            contentDiv.innerHTML = popupData.content;
                            contentDiv.onclick = function() {
                                window.open(popupData.target_url, '_blank');
                            };

                            var infoDiv = document.createElement('div');
                            infoDiv.className = 'promotion-info';
                            infoDiv.innerHTML = '<span class="promotion-type">Popup</span>';

                            promotionContent.appendChild(contentDiv);
                            promotionContent.appendChild(infoDiv);
                            overlay.appendChild(promotionContent);
                            document.body.appendChild(overlay);

                            // Show popup after 3 seconds
                            setTimeout(function() {
                                overlay.classList.add('active');
                            }, 3000);

                            // Auto-hide after 10 seconds
                            setTimeout(function() {
                                overlay.classList.remove('active');
                            }, 13000);

                            // Close on overlay click
                            overlay.addEventListener('click', function(e) {
                                if (e.target === overlay) {
                                    overlay.classList.remove('active');
                                }
                            });
                        }
                    })();
                @endif
            })();
        </script>
    @endif
</body>

</html>
