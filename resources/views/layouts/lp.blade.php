<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')


    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">


        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {

            --brand: #2563EB;

            --brand-dark: #1D4ED8;

            --border: #E5E5E5;

            --ink: #171717;

            --ink-dim: #737373;

        }

        html {

            scroll-behavior: smooth;

        }

        body {

            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;

            background: #fff;

            color: var(--ink);

        }

        .mono {

            font-family: 'JetBrains Mono', ui-monospace, monospace;

        }

        .boxed {

            max-width: 80rem;

            margin-inline: auto;

            position: relative;

            padding-inline: 1rem;

        }

        @media (min-width: 1280px) {

            .boxed {

                border-left: 1px solid var(--border);

                border-right: 1px solid var(--border);

                padding-inline: 4rem;

            }

        }

        .corner-dot {

            position: absolute;

            top: -4px;

            width: 7px;

            height: 7px;

            background: var(--brand);

            z-index: 10;

        }

        .corner-dot.left {

            left: -4px;

        }

        .corner-dot.right {

            right: -4px;

        }

        .grid-bg {

            background-image:

                linear-gradient(to right, rgba(0, 0, 0, 0.05) 1px, transparent 1px),

                linear-gradient(to bottom, rgba(0, 0, 0, 0.05) 1px, transparent 1px);

            background-size: 40px 40px;

            -webkit-mask-image: radial-gradient(ellipse 70% 100% at 50% 0%, black 25%, transparent 85%);

            mask-image: radial-gradient(ellipse 70% 100% at 50% 0%, black 25%, transparent 85%);

        }

        .btn-brand {

            background: var(--brand);

            color: #fff;

        }

        .btn-brand:hover {

            background: var(--brand-dark);

        }

        .btn-outline {

            border: 1px solid var(--border);

            color: var(--ink);

            background: #fff;

        }

        .btn-outline:hover {

            background: #fafafa;

            border-color: #d4d4d4;

        }

        .eyebrow,

        .step-num {

            font-family: 'JetBrains Mono', monospace;

            color: var(--brand);

        }

        .img-zoom {

            cursor: zoom-in;

            transition: opacity .15s ease;

        }

        .img-zoom:hover {

            opacity: .92;

        }

        [x-cloak] {

            display: none !important;

        }
    </style>
    </head>
    <body>
        <div class="h-screen max-w-7xl mx-auto p-3">
            {{ $slot }}
        </div>
    </body>
</html>
