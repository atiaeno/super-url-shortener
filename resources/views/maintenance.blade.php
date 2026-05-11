<!-- © Atia Hegazy — atiaeno.com -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode</title>
    <meta name="robots" content="noindex,nofollow">
    <link href="{{ asset('css/redirect.css') }}" rel="stylesheet">
    <style>
        .maintenance-content {
            width: 100%;
            max-width: 520px;
            text-align: center;
            margin: auto;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--red) 40%, var(--border) 40%);
        }

        .marker {
            font-family: var(--font-display);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--red);
            display: block;
            margin-bottom: 8px;
        }

        .heading {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 600;
            color: var(--ink);
            margin: 0 0 8px;
        }

        .sub {
            font-family: var(--font-body);
            font-size: 16px;
            font-style: italic;
            color: var(--muted);
            margin: 0 0 28px;
            line-height: 1.5;
        }

        .icon-wrap {
            width: 48px;
            height: 48px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--red-light);
        }

        .icon-wrap svg {
            width: 24px;
            height: 24px;
            stroke: var(--red);
            stroke-width: 1.5;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 28px;
            border-radius: var(--radius);
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            border: 1px solid transparent;
            background: var(--red);
            border-color: var(--red);
            color: var(--surface);
        }

        .btn:hover {
            background: var(--red-dark);
            border-color: var(--red-dark);
        }

        .footer {
            margin-top: 20px;
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--muted);
            font-style: italic;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="maintenance-content">
        <div class="card">
            <span class="marker">Maintenance</span>
            <h1 class="heading">Under Maintenance</h1>
            <p class="sub">{{ $message }}</p>

            <div class="icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path
                        d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                </svg>
            </div>

            <p style="font-family: var(--font-body); font-size: 16px; color: var(--muted); margin-bottom: 20px;">
                We're working hard to improve our service. We'll be back shortly!
            </p>

            <button onclick="location.reload()" class="btn">
                Refresh Page
            </button>

            <div class="footer">
                <p>You can try refreshing this page in a few minutes.</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>

</html>
