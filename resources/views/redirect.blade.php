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

        /* Ad container — raw HTML/JS renders here */
        .ad-wrap{margin:24px 0;padding:24px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);text-align:left;overflow:hidden;word-break:break-word}

        .btn-skip{opacity:.4;pointer-events:none;transition:opacity .3s}
        .btn-skip.active{opacity:1;pointer-events:auto}

        .dest-link{font-family:var(--font-body);font-size:14px;color:var(--muted);font-style:italic;margin-top:16px;word-break:break-all}
        .dest-link a{color:var(--muted);text-decoration:underline}

        @media(max-width:560px){.card{padding:36px 24px}.heading{font-size:19px}}
    </style>
</head>
<body>
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
            @if(!empty($adContent))
                <div class="ad-wrap">{!! $adContent !!}</div>
            @endif
            @if(!empty($redirectCaptcha) && !empty($captchaSiteKey))
                <div id="captchaWrap" style="display:flex;justify-content:center;margin-bottom:20px">
                    <div class="g-recaptcha" data-sitekey="{{ $captchaSiteKey }}" data-callback="onCaptchaPass"></div>
                </div>
            @endif
            <a href="{{ $destination }}" class="btn btn-primary btn-skip" id="skipBtn">Continue to Destination</a>
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
})();
</script>
@endif
</body>
</html>
