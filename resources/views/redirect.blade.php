{{-- © Atia Hegazy — atiaeno.com --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? config('app.name') }}</title>

    @if(!empty($ogTitle))
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription ?? '' }}">
    <meta property="og:url" content="{{ $ogUrl ?? '' }}">
    <meta property="og:type" content="website">
    @if(!empty($ogImage))
    <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? '' }}">
    @if(!empty($ogImage))
    <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    @if(!empty($redirectCaptcha) && !empty($captchaSiteKey))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --font-display:'Oswald',sans-serif;
            --font-body:'Crimson Pro',serif;
            --red:#c0392b;
            --red-dark:#a93226;
            --red-light:#f9ebea;
            --ink:#1a1a1a;
            --muted:#71717a;
            --surface:#fff;
            --bg:#fafafa;
            --border:#e4e4e7;
            --radius:6px;
        }
        body{font-family:var(--font-body);background:var(--bg);color:var(--ink);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;-webkit-font-smoothing:antialiased}
        .shell{width:100%;max-width:520px;text-align:center}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:48px 40px;position:relative;overflow:hidden}
        .card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--red) 40%,var(--border) 40%)}
        .marker{font-family:var(--font-display);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--red);display:block;margin-bottom:8px}
        .heading{font-family:var(--font-display);font-size:22px;font-weight:600;color:var(--ink);margin:0 0 8px}
        .sub{font-family:var(--font-body);font-size:16px;font-style:italic;color:var(--muted);margin:0 0 28px;line-height:1.5}
        .icon-wrap{width:48px;height:48px;margin:0 auto 20px;border-radius:50%;display:flex;align-items:center;justify-content:center}
        .icon-wrap svg{width:24px;height:24px;stroke-width:1.5}
        .icon-red{background:var(--red-light)}.icon-red svg{stroke:var(--red)}
        .icon-muted{background:#f4f4f5}.icon-muted svg{stroke:var(--muted)}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 28px;border-radius:var(--radius);font-family:var(--font-display);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;cursor:pointer;transition:all .2s;text-decoration:none;border:1px solid transparent}
        .btn-primary{background:var(--red);border-color:var(--red);color:var(--surface)}
        .btn-primary:hover{background:var(--red-dark);border-color:var(--red-dark)}
        .btn-primary:disabled{opacity:.5;pointer-events:none}
        .btn-ghost{background:transparent;border-color:var(--border);color:var(--ink)}
        .btn-ghost:hover{border-color:var(--ink)}
        .field-input{width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:var(--radius);font-family:var(--font-body);font-size:16px;color:var(--ink);background:var(--surface);transition:border-color .2s;outline:none}
        .field-input:focus{border-color:var(--red)}
        .field-input::placeholder{color:#a1a1aa;font-style:italic}
        .field-error{font-family:var(--font-body);font-size:14px;color:var(--red);margin-top:8px;font-style:italic;display:block;text-align:left}
        .footer{margin-top:20px;font-family:var(--font-body);font-size:14px;color:var(--muted);font-style:italic}

        /* Countdown ring */
        .ring-wrap{width:80px;height:80px;margin:0 auto 20px;position:relative}
        .ring-wrap svg{width:80px;height:80px;transform:rotate(-90deg)}
        .ring-wrap circle{fill:none;stroke-width:3}
        .ring-bg{stroke:var(--border)}
        .ring-fg{stroke:var(--red);stroke-dasharray:226;stroke-dashoffset:0;stroke-linecap:round;transition:stroke-dashoffset 1s linear}
        .ring-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:28px;font-weight:700;color:var(--ink)}

        /* Promotion container — raw HTML/JS renders here */
        .promotion-wrap{margin:24px 0;padding:0;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);text-align:left;overflow:hidden;word-break:break-word}
        .promotion-info-bar{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--surface);border-bottom:1px solid var(--border);font-family:var(--font-display);font-size:9px;text-transform:uppercase;letter-spacing:.04em}
        .promotion-placement{color:var(--red);font-weight:600}
        .promotion-format{color:var(--muted)}
        .promotion-content{padding:24px}
        .promotion-content img{width:100%;height:auto;border-radius:8px}

        /* Header Promotion */
        .header-promotion{width:100%;background:var(--surface);border-bottom:1px solid var(--border);position:relative;margin-bottom:16px}
        .header-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .header-promotion .promotion-content:hover{opacity:0.9}
        .header-promotion .promotion-content img{width:100%;height:auto;display:block}
        .header-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .header-promotion .promotion-type{color:#ff6b6b;font-weight:600}
        .header-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .header-promotion .promotion-close:hover{opacity:1}

        /* Footer Promotion */
        .footer-promotion{width:100%;background:var(--surface);border-top:1px solid var(--border);position:relative;margin-top:16px}
        .footer-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .footer-promotion .promotion-content:hover{opacity:0.9}
        .footer-promotion .promotion-content img{width:100%;height:auto;display:block}
        .footer-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .footer-promotion .promotion-type{color:#4ecdc4;font-weight:600}
        .footer-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .footer-promotion .promotion-close:hover{opacity:1}

        /* Left Side Promotion */
        .left-side-promotion{position:fixed;top:50%;left:20px;transform:translateY(-50%);width:300px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:0 4px 12px rgba(0,0,0,0.1);z-index:10}
        .left-side-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .left-side-promotion .promotion-content:hover{opacity:0.9}
        .left-side-promotion .promotion-content img{width:100%;height:auto;display:block}
        .left-side-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .left-side-promotion .promotion-type{color:#f39c12;font-weight:600}
        .left-side-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .left-side-promotion .promotion-close:hover{opacity:1}

        /* Right Side Promotion */
        .right-side-promotion{position:fixed;top:50%;right:20px;transform:translateY(-50%);width:300px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:0 4px 12px rgba(0,0,0,0.1);z-index:10}
        .right-side-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .right-side-promotion .promotion-content:hover{opacity:0.9}
        .right-side-promotion .promotion-content img{width:100%;height:auto;display:block}
        .right-side-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .right-side-promotion .promotion-type{color:#9b59b6;font-weight:600}
        .right-side-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .right-side-promotion .promotion-close:hover{opacity:1}

        /* Before Counter Promotion */
        .before-counter-promotion{margin:0 0 24px 0;padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);text-align:center}
        .before-counter-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .before-counter-promotion .promotion-content:hover{opacity:0.9}
        .before-counter-promotion .promotion-content img{width:100%;height:auto;max-width:400px;border-radius:8px}
        .before-counter-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .before-counter-promotion .promotion-type{color:#e74c3c;font-weight:600}
        .before-counter-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .before-counter-promotion .promotion-close:hover{opacity:1}

        /* Under Counter Promotion */
        .under-counter-promotion{margin:24px 0;padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);text-align:center}
        .under-counter-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .under-counter-promotion .promotion-content:hover{opacity:0.9}
        .under-counter-promotion .promotion-content img{width:100%;height:auto;max-width:400px;border-radius:8px}
        .under-counter-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .under-counter-promotion .promotion-type{color:#3498db;font-weight:600}
        .under-counter-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .under-counter-promotion .promotion-close:hover{opacity:1}

        /* Above Button Promotion */
        .above-button-promotion{margin:24px 0 16px 0;padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);text-align:center}
        .above-button-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .above-button-promotion .promotion-content:hover{opacity:0.9}
        .above-button-promotion .promotion-content img{width:100%;height:auto;max-width:300px;border-radius:8px}
        .above-button-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .above-button-promotion .promotion-type{color:#2ecc71;font-weight:600}
        .above-button-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .above-button-promotion .promotion-close:hover{opacity:1}

        /* Under Button Promotion */
        .under-button-promotion{margin:16px 0 24px 0;padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);text-align:center}
        .under-button-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .under-button-promotion .promotion-content:hover{opacity:0.9}
        .under-button-promotion .promotion-content img{width:100%;height:auto;max-width:300px;border-radius:8px}
        .under-button-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .under-button-promotion .promotion-type{color:#f39c12;font-weight:600}
        .under-button-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .under-button-promotion .promotion-close:hover{opacity:1}

        /* Popup Promotion */
        .popup-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity 0.3s,visibility 0.3s}
        .popup-overlay.active{opacity:1;visibility:visible}
        .popup-promotion{background:var(--surface);border-radius:var(--radius);max-width:500px;width:90%;max-height:90vh;overflow-y:auto;position:relative}
        .popup-promotion .promotion-content{cursor:pointer;transition:opacity 0.2s}
        .popup-promotion .promotion-content:hover{opacity:0.9}
        .popup-promotion .promotion-content img{width:100%;height:auto;border-radius:8px}
        .popup-promotion .promotion-info{position:absolute;top:8px;right:8px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-family:var(--font-display);text-transform:uppercase;letter-spacing:0.5px}
        .popup-promotion .promotion-type{color:#e74c3c;font-weight:600}
        .popup-promotion .promotion-close{cursor:pointer;opacity:0.7;transition:opacity 0.2s}
        .popup-promotion .promotion-close:hover{opacity:1}

        .btn-skip{opacity:.4;pointer-events:none;transition:opacity .3s}
        .btn-skip.active{opacity:1;pointer-events:auto}

        .dest-link{font-family:var(--font-body);font-size:14px;color:var(--muted);font-style:italic;margin-top:16px;word-break:break-all}
        .dest-link a{color:var(--muted);text-decoration:underline}

        @media(max-width:560px){
            .card{padding:36px 24px}.heading{font-size:19px}
            .left-side-promotion,.right-side-promotion{position:static;transform:none;width:100%;max-width:300px;margin:10px auto}
            .before-counter-promotion,.under-counter-promotion,.above-button-promotion,.under-button-promotion{margin:16px 0}
            .popup-promotion{width:95%;max-width:400px}
        }
    </style>
</head>
<body>
    <!-- Header Promotion (Top of page) -->
    @if($headerPromotion)
        <div class="header-promotion">
            <div class="promotion-content" onclick="window.open('{{ $headerPromotion->target_url }}', '_blank')">
                {!! $headerPromotion->content !!}
            </div>
            <div class="promotion-info">
                <span class="promotion-type">Header Promotion</span>
                <span class="promotion-close" onclick="this.closest('.header-promotion').style.display='none'">×</span>
            </div>
        </div>
    @endif
    
    <!-- Left Side Promotion -->
    @if($leftSidePromotion)
        <div class="left-side-promotion">
            <div class="promotion-content" onclick="window.open('{{ $leftSidePromotion->target_url }}', '_blank')">
                {!! $leftSidePromotion->content !!}
            </div>
            <div class="promotion-info">
                <span class="promotion-type">Left Promotion</span>
                <span class="promotion-close" onclick="this.closest('.left-side-promotion').style.display='none'">×</span>
            </div>
        </div>
    @endif
    
    <!-- Right Side Promotion -->
    @if($rightSidePromotion)
        <div class="right-side-promotion">
            <div class="promotion-content" onclick="window.open('{{ $rightSidePromotion->target_url }}', '_blank')">
                {!! $rightSidePromotion->content !!}
            </div>
            <div class="promotion-info">
                <span class="promotion-type">Right Promotion</span>
                <span class="promotion-close" onclick="this.closest('.right-side-promotion').style.display='none'">×</span>
            </div>
        </div>
    @endif

<div class="shell">
    <div class="card">

        {{-- ── PASSWORD GATE ─────────────────────────── --}}
        @if($state === 'password')
            <div class="icon-wrap icon-red">
                <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <span class="marker">Protected</span>
            <h1 class="heading">Password Required</h1>
            <p class="sub">This link is private. Enter the password to continue.</p>
            <form method="POST" action="{{ url('/' . $shortCode) }}" style="text-align:left">
                @csrf
                <input type="password" name="password" class="field-input" placeholder="Enter password…" autofocus required>
                @if(!empty($error))
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
            @if($beforeCounterPromotion)
                <div class="before-counter-promotion" style="position:relative;">
                    <div class="promotion-content" onclick="window.open('{{ $beforeCounterPromotion->target_url }}', '_blank')">
                        {!! $beforeCounterPromotion->content !!}
                    </div>
                    <div class="promotion-info">
                        <span class="promotion-type">Before Counter</span>
                        <span class="promotion-close" onclick="this.closest('.before-counter-promotion').style.display='none'">×</span>
                    </div>
                </div>
            @endif
            @if($countdown > 0)
                <div class="ring-wrap" id="ringWrap">
                    <svg viewBox="0 0 80 80">
                        <circle class="ring-bg" cx="40" cy="40" r="36"/>
                        <circle class="ring-fg" id="ringFg" cx="40" cy="40" r="36"/>
                    </svg>
                    <span class="ring-num" id="ringNum">{{ $countdown }}</span>
                </div>
                <h1 class="heading">You'll be redirected shortly</h1>
                <p class="sub">Please wait <strong id="secText" style="color:var(--ink);font-style:normal">{{ $countdown }}</strong> seconds</p>
            @else
                <h1 class="heading" style="margin-top:8px">Ready to redirect</h1>
                <p class="sub">Click the button below to continue.</p>
            @endif
            <!-- Under Counter Promotion -->
            @if($underCounterPromotion)
                <div class="under-counter-promotion" style="position:relative;">
                    <div class="promotion-content" onclick="window.open('{{ $underCounterPromotion->target_url }}', '_blank')">
                        {!! $underCounterPromotion->content !!}
                    </div>
                    <div class="promotion-info">
                        <span class="promotion-type">Under Counter</span>
                        <span class="promotion-close" onclick="this.closest('.under-counter-promotion').style.display='none'">×</span>
                    </div>
                </div>
            @endif
            
            @if(!empty($adContent))
                <div class="promotion-wrap">
                    <div class="promotion-info-bar">
                        <span class="promotion-placement">{{ ucfirst($adPlacement ?? 'redirect') }} Promotion</span>
                        <span class="promotion-format">{{ ucfirst($adFormat ?? 'unknown') }}</span>
                    </div>
                    <div class="promotion-content">{!! $adContent !!}</div>
                </div>
            @endif
            @if(!empty($redirectCaptcha) && !empty($captchaSiteKey))
                <div id="captchaWrap" style="display:flex;justify-content:center;margin-bottom:20px">
                    <div class="g-recaptcha" data-sitekey="{{ $captchaSiteKey }}" data-callback="onCaptchaPass"></div>
                </div>
            @endif
            
            <!-- Above Button Promotion -->
            @if($aboveButtonPromotion)
                <div class="above-button-promotion" style="position:relative;">
                    <div class="promotion-content" onclick="window.open('{{ $aboveButtonPromotion->target_url }}', '_blank')">
                        {!! $aboveButtonPromotion->content !!}
                    </div>
                    <div class="promotion-info">
                        <span class="promotion-type">Above Button</span>
                        <span class="promotion-close" onclick="this.closest('.above-button-promotion').style.display='none'">×</span>
                    </div>
                </div>
            @endif
            
            <a href="{{ $destination }}" class="btn btn-primary btn-skip" id="skipBtn">Continue to Destination</a>
            
            <!-- Under Button Promotion -->
            @if($underButtonPromotion)
                <div class="under-button-promotion" style="position:relative;">
                    <div class="promotion-content" onclick="window.open('{{ $underButtonPromotion->target_url }}', '_blank')">
                        {!! $underButtonPromotion->content !!}
                    </div>
                    <div class="promotion-info">
                        <span class="promotion-type">Under Button</span>
                        <span class="promotion-close" onclick="this.closest('.under-button-promotion').style.display='none'">×</span>
                    </div>
                </div>
            @endif
            
            <div class="dest-link">Destination: <a href="{{ $destination }}">{{ Str::limit($destination, 60) }}</a></div>

        {{-- ── NOT FOUND / EXPIRED ───────────────────── --}}
        @elseif($state === 'not-found')
            <div class="icon-wrap icon-muted">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <span class="marker">404</span>
            <h1 class="heading">Link Not Found</h1>
            <p class="sub">The short link <strong style="color:var(--ink);font-style:normal">{{ $shortCode }}</strong> does not exist or has been removed.</p>
            <a href="{{ url('/') }}" class="btn btn-ghost">Go Home</a>

        @elseif($state === 'expired')
            <div class="icon-wrap icon-muted">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <span class="marker">Expired</span>
            <h1 class="heading">This Link Has Expired</h1>
            <p class="sub">Expired on <strong style="color:var(--ink);font-style:normal">{{ $expiresAt->format('M j, Y \a\t g:i A') }}</strong> and is no longer available.</p>
            <a href="{{ url('/') }}" class="btn btn-ghost">Go Home</a>
        @endif

    </div>
    
    <!-- Footer Promotion -->
    @if($footerPromotion)
        <div class="footer-promotion">
            <div class="promotion-content" onclick="window.open('{{ $footerPromotion->target_url }}', '_blank')">
                {!! $footerPromotion->content !!}
            </div>
            <div class="promotion-info">
                <span class="promotion-type">Footer Promotion</span>
                <span class="promotion-close" onclick="this.closest('.footer-promotion').style.display='none'">×</span>
            </div>
        </div>
    @endif
    
    <div class="footer">{{ config('app.name', 'ShortLink') }}</div>
</div>

@if($state === 'redirect')
<script>
(function(){
    var mode='{{ $redirectMode ?? "auto" }}';
    var needCaptcha={{ !empty($redirectCaptcha) && !empty($captchaSiteKey) ? 'true' : 'false' }};
    var captchaPassed=false;
    var btn=document.getElementById('skipBtn');
    var dest='{{ $destination }}';
    var total={{ $countdown }};

    // Captcha callback — called by reCAPTCHA when user passes
    window.onCaptchaPass=function(){
        captchaPassed=true;
        if(btn.dataset.ready==='true') btn.classList.add('active');
    };

    function enableButton(){
        btn.dataset.ready='true';
        if(!needCaptcha||captchaPassed) btn.classList.add('active');
    }

    function autoRedirect(){
        if(needCaptcha&&!captchaPassed) return;
        window.location.href=dest;
    }

    if(total>0){
        var circ=2*Math.PI*36,sec=total,
            fg=document.getElementById('ringFg'),
            num=document.getElementById('ringNum'),
            txt=document.getElementById('secText');
        fg.style.strokeDasharray=circ;
        fg.style.strokeDashoffset='0';
        var t=setInterval(function(){
            sec--;
            num.textContent=sec;
            txt.textContent=sec;
            fg.style.strokeDashoffset=circ*(1-sec/total);
            if(sec<=0){
                clearInterval(t);
                num.textContent='\u2713';
                enableButton();
                if(mode==='auto') autoRedirect();
            }
        },1000);
    } else {
        enableButton();
    }

    // Button click always navigates (if allowed)
    btn.addEventListener('click',function(e){
        if(!btn.classList.contains('active')){e.preventDefault();return;}
    });

    // Popup Promotion Logic
    @if($popupPromotion)
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
                infoDiv.innerHTML = '<span class="promotion-type">Popup</span><span class="promotion-close">×</span>';
                
                var closeButton = infoDiv.querySelector('.promotion-close');
                closeButton.onclick = function() {
                    overlay.classList.remove('active');
                };
                
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
