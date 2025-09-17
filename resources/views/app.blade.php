<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
        <meta name="apple-mobile-web-app-title" content="Locket" />
        <link rel="manifest" href="/site.webmanifest" />

        <meta property="og:type" content="website">
        <meta property="og:title" content="Locket">
        <meta property="og:url" content="https://locket.laravel.cloud">
        <meta property="og:image" content="https://locket.laravel.cloud/og.png">
        <meta property="og:description" content="Link sharing social feed & read later app for developers">
        <meta property="og:site_name" content="Locket">

        <meta name="twitter:site" content="@laravelphp" />
        <meta name="twitter:creator" content="@laravelphp" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:image" content="https://locket.laravel.cloud/og.png" />
        <meta name="twitter:title" content="Locket" />
        <meta name="twitter:description" content="Link sharing social feed & read later app for developers" />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
