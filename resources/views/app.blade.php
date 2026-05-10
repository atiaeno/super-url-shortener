<?php
use App\Models\Setting;

$appName = Setting::get('app_name', config('app.name', 'Super Url Shortener'));
$appTagline = Setting::get('app_tagline', 'Shorten URLs with style and analytics');
$faviconUrl = Setting::get('favicon_url', '');
$logoUrl = Setting::get('logo_url', '');
$metaDescription = Setting::get('meta_description', 'Shorten URLs with style and analytics. Track clicks, geographic data, and more.');
$metaKeywords = Setting::get('meta_keywords', 'url shortener, link shortener, url tracker, analytics, qr code');
$ogImage = Setting::get('og_image', '');
$schemaJson = Setting::get('schema_json', '{"@context":"https://schema.org","@type":"WebApplication","name":"Super Url Shortener","applicationCategory":"UtilitiesApplication"}');
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Basic Meta -->
    <title inertia></title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="author" content="atiaeno">

    <!-- Favicon -->
    @if ($faviconUrl)
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $appName }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @elseif ($logoUrl)
        <meta property="og:image" content="{{ $logoUrl }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $appName }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    <!-- Schema.org JSON-LD -->
    @if ($schemaJson)
        <script type="application/ld+json">
        {!! $schemaJson !!}
        </script>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.3.2/css/flag-icons.min.css" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
