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
    @if (isset($headerPromotion) && $headerPromotion)
        <div class="header-promotion">
            <div class="promotion-content" onclick="window.open('{{ $headerPromotion->target_url }}', '_blank')">
                {!! $headerPromotion->content !!}
            </div>
        </div>
    @endif

    <!-- Left Side Promotion -->
    @if (isset($leftSidePromotion) && $leftSidePromotion)
        <div class="left-side-promotion">
            <div class="promotion-content" onclick="window.open('{{ $leftSidePromotion->target_url }}', '_blank')">
                {!! $leftSidePromotion->content !!}
            </div>
        </div>
    @endif

    <!-- Right Side Promotion -->
    @if (isset($rightSidePromotion) && $rightSidePromotion)
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
                @if (isset($beforeCounterPromotion) && $beforeCounterPromotion)
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
                @if (isset($underCounterPromotion) && $underCounterPromotion)
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
                @if (isset($aboveButtonPromotion) && $aboveButtonPromotion)
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
                @if (isset($underButtonPromotion) && $underButtonPromotion)
                    <div class="under-button-promotion">
                        <div class="promotion-content"
                            onclick="window.open('{{ $underButtonPromotion->target_url }}', '_blank')">
                            {!! $underButtonPromotion->content !!}
                        </div>
                    </div>
                @endif

                <div class="dest-link" id="destLink" @if ($redirectCaptcha) style="display:none" @endif>
                    Destination: <a href="{{ $destination }}">{{ Str::limit($destination, 60) }}</a></div>

                <!-- Report and Share Actions -->
                <div class="action-buttons">
                    @if ($link)
                        <button class="action-btn action-btn--report" onclick="openReportModal()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                                <line x1="4" y1="22" x2="4" y2="15" />
                            </svg>
                            Report Link
                        </button>
                    @endif
                    <button class="action-btn action-btn--share" onclick="openShareModal()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="18" cy="5" r="3" />
                            <circle cx="6" cy="12" r="3" />
                            <circle cx="18" cy="19" r="3" />
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                        </svg>
                        Share
                    </button>
                </div>

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
        @if (isset($footerPromotion) && $footerPromotion)
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
                    // Show destination link after captcha passed
                    var destLink = document.getElementById('destLink');
                    if (destLink) destLink.style.display = 'block';
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

                            //  var infoDiv = document.createElement('div');
                            //  infoDiv.className = 'promotion-info';
                            //   infoDiv.innerHTML = '<span class="promotion-type">Popup</span>';

                            promotionContent.appendChild(contentDiv);
                            //promotionContent.appendChild(infoDiv);
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
    <!-- Report Modal -->
    <div id="reportModal" class="modal" style="display: none;">
        <div class="modal-backdrop" onclick="closeReportModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Report This Link</h3>
                <button class="modal-close" onclick="closeReportModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reportForm" onsubmit="submitReport(event)">
                    <div class="form-group">
                        <label>Reason for reporting:</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="reason" value="spam" required>
                                <span>Spam</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="reason" value="phishing" required>
                                <span>Phishing</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="reason" value="malware" required>
                                <span>Malware</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="reason" value="violence" required>
                                <span>Violence</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="reason" value="other" required>
                                <span>Other</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="details">Additional details (optional):</label>
                        <textarea id="details" name="details" rows="3" placeholder="Please provide any additional information..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" onclick="closeReportModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="modal" style="display: none;">
        <div class="modal-backdrop" onclick="closeShareModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Share This Link</h3>
                <button class="modal-close" onclick="closeShareModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="share-options">
                    <a href="#" class="share-btn share-btn--twitter" onclick="shareOnTwitter(); return false;">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                        </svg>
                        Twitter
                    </a>
                    <a href="#" class="share-btn share-btn--facebook"
                        onclick="shareOnFacebook(); return false;">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        Facebook
                    </a>
                    <a href="#" class="share-btn share-btn--linkedin"
                        onclick="shareOnLinkedIn(); return false;">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                        LinkedIn
                    </a>
                    <a href="#" class="share-btn share-btn--whatsapp"
                        onclick="shareOnWhatsApp(); return false;">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.371-.025-.52-.075-.149-.669-1.611-.916-2.206-.242-.579-.487-.501-.87-.501l-.87-.015c-.3 0-.783.099-1.193.495-.409.398-1.562 1.527-1.562 3.72 0 2.193 1.587 4.307 1.807 4.597.22.29 3.105 4.736 7.525 6.636 1.049.452 1.87.722 2.506.925.857.267 1.638.23 2.258.14.688-.1 2.115-.862 2.412-1.695.298-.834.298-1.547.223-1.696-.074-.149-.272-.223-.57-.371z" />
                            <path
                                d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm6.44 17.562c-.297.834-1.724 1.595-2.412 1.695-.688.1-1.371.127-2.258-.14-.858-.203-3.105-.896-6.367-4.347C2.935 10.875 2.25 8.761 2.25 6.568c0-2.193 1.153-3.322 1.562-3.72.41-.396.893-.495 1.193-.495h.87c.3 0 .628.099.87.501.242.595.669 2.057.916 2.206.075.149.025.323-.025.52-.075.199-.149.347-.3.495-.149.149-.298.347-.446.521-.149.174-.298.347-.149.521.149.174.669 1.611 1.695 2.206.525.595 1.48 1.324 2.005 1.474.525.149.644.099.87-.074.223-.174.767-.966 1.064-1.695.298-.834.298-1.547.223-1.696-.075-.149-.272-.223-.57-.371z" />
                        </svg>
                        WhatsApp
                    </a>
                    <a href="#" class="share-btn share-btn--telegram"
                        onclick="shareOnTelegram(); return false;">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-.818.404l-4.48-4.48-1.746 1.677c-.22.22-.404.22-.404-.058l.58-5.28.003-.003c.003-.022.003-.043.003-.065 0-.22-.18-.4-.4-.4-.146 0-.273.08-.34.197l-2.776 5.095c-.146.267-.058.603.197.749.073.04.155.058.236.058.04 0 .08-.008.12-.025l5.28-2.776c.117-.06.197-.194.197-.34 0-.022-.003-.043-.003-.065l.003-.003-.58-5.28c-.278-.278-.058-.278.404.058l1.677 1.746 4.48 4.48c.414.281.746.146.818-.404l1.97-9.28c.073-.365-.146-.585-.511-.512z" />
                        </svg>
                        Telegram
                    </a>
                    <button class="share-btn share-btn--copy" onclick="copyLink()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                        </svg>
                        Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        function openReportModal() {
            document.getElementById('reportModal').style.display = 'flex';
        }

        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
            document.getElementById('reportForm').reset();
        }

        function openShareModal() {
            document.getElementById('shareModal').style.display = 'flex';
        }

        function closeShareModal() {
            document.getElementById('shareModal').style.display = 'none';
        }

        // Report submission
        async function submitReport(event) {
            event.preventDefault();

            const formData = new FormData(event.target);

            try {
                const response = await fetch(`/links/{{ $link ? $link->id : '' }}/report`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                // Check if response is JSON before parsing
                const contentType = response.headers.get('content-type');
                let result;

                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    // If not JSON, get text response to see what the server returned
                    const text = await response.text();
                    console.error('Server returned non-JSON response:', text);

                    if (response.ok) {
                        alert('Report submitted successfully. Thank you for keeping our platform safe!');
                        closeReportModal();
                        return;
                    } else {
                        alert('Error submitting report. Please try again.');
                        return;
                    }
                }

                if (response.ok) {
                    alert('Report submitted successfully. Thank you for keeping our platform safe!');
                    closeReportModal();
                } else {
                    alert(result.error || 'Error submitting report. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error submitting report. Please try again.');
            }
        }

        // Share functionality
        const currentUrl = window.location.href;
        const shareText = 'Check out this link:';

        function shareOnTwitter() {
            const width = 550;
            const height = 420;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            const popup = window.open(
                `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText + ' ' + currentUrl)}`,
                'twitter-share',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );

            // Close popup after 5 seconds
            setTimeout(() => {
                if (popup && !popup.closed) {
                    popup.close();
                }
            }, 5000);
        }

        function shareOnFacebook() {
            const width = 580;
            const height = 400;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            const popup = window.open(
                `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`,
                'facebook-share',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );

            // Close popup after 5 seconds
            setTimeout(() => {
                if (popup && !popup.closed) {
                    popup.close();
                }
            }, 5000);
        }

        function shareOnLinkedIn() {
            const width = 750;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            const popup = window.open(
                `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}`,
                'linkedin-share',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );

            // Close popup after 5 seconds
            setTimeout(() => {
                if (popup && !popup.closed) {
                    popup.close();
                }
            }, 5000);
        }

        function shareOnWhatsApp() {
            const width = 400;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            const popup = window.open(
                `https://wa.me/?text=${encodeURIComponent(shareText + ' ' + currentUrl)}`,
                'whatsapp-share',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );

            // Close popup after 5 seconds
            setTimeout(() => {
                if (popup && !popup.closed) {
                    popup.close();
                }
            }, 5000);
        }

        function shareOnTelegram() {
            const width = 500;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            const popup = window.open(
                `https://t.me/share/url?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(shareText)}`,
                'telegram-share',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );

            // Close popup after 5 seconds
            setTimeout(() => {
                if (popup && !popup.closed) {
                    popup.close();
                }
            }, 5000);
        }

        async function copyLink() {
            try {
                await navigator.clipboard.writeText(currentUrl);
                const copyBtn = document.querySelector('.share-btn--copy');
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML =
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> Copied!';
                copyBtn.style.background = '#10b981';

                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                    copyBtn.style.background = '';
                }, 2000);
            } catch (error) {
                console.error('Error copying link:', error);
                alert('Error copying link. Please try again.');
            }
        }

        // Close modals on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeReportModal();
                closeShareModal();
            }
        });
    </script>

</body>

</html>
