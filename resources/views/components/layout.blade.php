<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="/favicons/favicon-96x96.png?v=20260330" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicons/favicon.svg?v=20260330" />
    <link rel="shortcut icon" href="/favicons/favicon.ico?v=20260330" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png?v=20260330" />
    <meta name="apple-mobile-web-app-title" content="CoCar" />
    <link rel="manifest" href="/favicons/site.webmanifest?v=20260330" />
    @if (isset($head))
        {{ $head }}
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @if (isset($body))
        {{ $body }}
    @else
        <div class="content-wrapper">
            {{ $content }}
        </div>
    @endif
</body>


</html>
