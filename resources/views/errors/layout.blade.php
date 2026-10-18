<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') — {{ config('app.name', 'MyBPIN') }}</title>
    @include('layouts.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --login-marinho: #0B1B3D;
            --login-muted: #5b6b86;
            --login-line: rgba(11, 27, 61, 0.12);
            --login-surface: #ffffff;
            --login-font-titulo: 'Montserrat', Arial, sans-serif;
            --login-font-texto: 'Nunito', Verdana, sans-serif;
            --badge-bg: #eef2ff;
            --badge-fg: #3347a8;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--login-font-texto);
            color: var(--login-marinho);
            background-color: #072333;
            background-image:
                linear-gradient(135deg, rgba(7, 35, 51, 0.72) 0%, rgba(7, 35, 51, 0.45) 45%, rgba(3, 30, 45, 0.78) 100%),
                url({{ asset('images/bg_login_bpin_mybp.jpg') }});
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
        }

        .card {
            width: 100%;
            max-width: 460px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 1.25rem;
            background: var(--login-surface);
            box-shadow: 0 28px 64px rgba(3, 20, 30, 0.42);
            overflow: hidden;
            text-align: center;
            animation: enter 420ms ease-out;
        }

        @keyframes enter {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card__brand {
            padding: 1.75rem 1.75rem 1.15rem;
            border-bottom: 1px solid var(--login-line);
        }

        .card__brand img {
            display: block;
            max-width: min(240px, 100%);
            max-height: 64px;
            margin: 0 auto;
            object-fit: contain;
        }

        .card__body {
            padding: 1.75rem 1.75rem 1.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: var(--badge-bg);
            color: var(--badge-fg);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .icon {
            width: 3.25rem;
            height: 3.25rem;
            margin: 0 auto 1rem;
            border-radius: 999px;
            background: rgba(11, 27, 61, 0.08);
            color: var(--login-marinho);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            font-family: var(--login-font-titulo);
            letter-spacing: -0.02em;
        }

        h1 {
            margin: 0 0 0.5rem;
            font-family: var(--login-font-titulo);
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        p {
            margin: 0 0 0.75rem;
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--login-muted);
        }

        .hint {
            margin: 1rem 0 1.35rem;
            padding: 0.85rem 1rem;
            border-radius: 0.85rem;
            background: #f4f6fa;
            border: 1px solid var(--login-line);
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--login-marinho);
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 1.25rem;
            border-radius: 0.8rem;
            font-family: var(--login-font-titulo);
            font-weight: 700;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .2s ease, background .2s ease;
            border: 0;
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background: var(--login-marinho);
            box-shadow: 0 10px 20px rgba(11, 27, 61, 0.28);
        }

        .btn-primary:hover {
            background: #13264f;
            transform: translateY(-1px);
        }

        .btn-secondary {
            color: var(--login-marinho);
            background: #fff;
            border: 1px solid var(--login-marinho);
        }

        .btn-secondary:hover {
            background: #f4f6fa;
        }

        .code {
            margin-top: 1.15rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #9aa8b8;
        }

        .theme-warn {
            --badge-bg: #fff4e8;
            --badge-fg: #b45a00;
        }

        .theme-danger {
            --badge-bg: #fdecec;
            --badge-fg: #a32020;
        }

        .theme-info {
            --badge-bg: #e8f4fc;
            --badge-fg: #1a5f8a;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="shell">
    <div class="card @yield('theme')">
        <div class="card__brand">
            <img src="{{ asset('images/mybpin-logo-fundo-claro.webp') }}" alt="MyBPIN">
        </div>
        <div class="card__body">
            @hasSection('badge')
                <div class="badge">@yield('badge')</div>
            @endif

            @hasSection('icon')
                <div class="icon" aria-hidden="true">@yield('icon')</div>
            @endif

            <h1>@yield('heading')</h1>
            <p>@yield('message')</p>

            @hasSection('hint')
                <div class="hint">@yield('hint')</div>
            @endif

            <div class="actions">
                @yield('actions')
            </div>

            @hasSection('code')
                <div class="code">@yield('code')</div>
            @endif
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
