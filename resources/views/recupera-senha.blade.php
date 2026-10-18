<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperação de senha — {{ config('app.name', 'MyBPIN') }}</title>
    @if(\App\Models\Sistema::verificaHdev())
        <meta name="robots" content="noindex">
    @endif
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icons.min.css') }}">
    @include('layouts.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    @if(env('APP_ENV') !== 'local')
        <script type="text/javascript">
            (function (c, l, a, r, i, t, y) {
                c[a] = c[a] || function () {
                    (c[a].q = c[a].q || []).push(arguments)
                };
                t = l.createElement(r);
                t.async = 1;
                t.src = "https://www.clarity.ms/tag/" + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y);
            })(window, document, "clarity", "script", "mltvhh6s7v");
        </script>
    @endif
    <style>
        :root {
            --login-azul: #0047D6;
            --login-azul-profundo: #022B9D;
            --login-marinho: #0B1B3D;
            --login-noite: #070B14;
            --login-nevoa: #F4F6FA;
            --login-ink: var(--login-marinho);
            --login-teal: var(--login-marinho);
            --login-teal-deep: var(--login-noite);
            --login-muted: #5b6b86;
            --login-line: rgba(11, 27, 61, 0.12);
            --login-surface: #ffffff;
            --login-focus: rgba(11, 27, 61, 0.18);
            --login-ok: #1b7a4a;
            --login-ok-bg: #e8f6ee;
            --login-font-titulo: 'Montserrat', Arial, sans-serif;
            --login-font-texto: 'Nunito', Verdana, sans-serif;
        }

        * { box-sizing: border-box; }

        [v-cloak] { display: none; }

        body.my-login-page {
            min-height: 100vh;
            margin: 0;
            font-family: var(--login-font-texto);
            color: var(--login-ink);
            background-color: #072333;
            background-image:
                linear-gradient(135deg, rgba(7, 35, 51, 0.72) 0%, rgba(7, 35, 51, 0.45) 45%, rgba(3, 30, 45, 0.78) 100%),
                url({{ asset('images/bg_login_bpin_mybp.jpg') }});
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .login-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 1.25rem;
            background: var(--login-surface);
            box-shadow: 0 28px 64px rgba(3, 20, 30, 0.42);
            overflow: hidden;
            animation: loginEnter 420ms ease-out;
        }

        @keyframes loginEnter {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card__brand {
            padding: 1.75rem 1.75rem 1.15rem;
            text-align: center;
            border-bottom: 1px solid var(--login-line);
        }

        .login-card__brand img {
            display: block;
            width: auto;
            height: auto;
            max-width: min(280px, 100%);
            max-height: 72px;
            margin: 0 auto;
            object-fit: contain;
        }

        .login-card__body {
            padding: 1.5rem 1.75rem 1.5rem;
        }

        .login-card__title {
            margin: 0 0 0.35rem;
            font-family: var(--login-font-titulo);
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--login-ink);
        }

        .login-card__subtitle {
            margin: 0 0 1.25rem;
            font-family: var(--login-font-texto);
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--login-muted);
            line-height: 1.45;
        }

        .login-field {
            margin-bottom: 1rem;
        }

        .login-field label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--login-ink);
        }

        .login-field .form-control {
            min-height: 46px;
            border: 1px solid rgba(11, 27, 61, 0.2);
            border-radius: 0.75rem;
            background-color: #fff !important;
            color: var(--login-ink);
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .login-field .form-control::placeholder { color: #8aa0ab; }

        .login-field .form-control:focus {
            border-color: var(--login-teal);
            box-shadow: 0 0 0 3px var(--login-focus);
            outline: 0;
        }

        .login-password {
            position: relative;
        }

        .login-password .form-control {
            padding-right: 2.75rem;
        }

        .login-password__toggle {
            position: absolute;
            top: 50%;
            right: 0.55rem;
            transform: translateY(-50%);
            width: 2rem;
            height: 2rem;
            border: 0;
            border-radius: 0.5rem;
            background: transparent;
            color: var(--login-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .login-password__toggle:hover,
        .login-password__toggle:focus {
            color: var(--login-marinho);
            background: rgba(11, 27, 61, 0.08);
            outline: 0;
        }

        .login-actions .btn.btn-primary {
            min-height: 48px;
            width: 100%;
            border: 0;
            border-radius: 0.8rem;
            font-family: var(--login-font-titulo);
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #fff !important;
            background: var(--login-marinho) !important;
            box-shadow: 0 10px 20px rgba(11, 27, 61, 0.28);
            transition: transform .15s ease, box-shadow .2s ease, background .2s ease;
        }

        .login-actions .btn.btn-primary:hover:not(:disabled),
        .login-actions .btn.btn-primary:focus:not(:disabled) {
            background: #13264f !important;
            box-shadow: 0 12px 24px rgba(11, 27, 61, 0.36);
            transform: translateY(-1px);
        }

        .login-actions .btn.btn-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .login-footer-link {
            display: block;
            margin-top: 1rem;
            text-align: center;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--login-teal);
            text-decoration: none;
        }

        .login-footer-link:hover {
            color: var(--login-teal-deep);
            text-decoration: underline;
        }
    </style>
</head>
<body class="my-login-page">
<div id="app" class="login-shell" v-cloak>
    <recupera-senha token="{{ $recuperacao->token }}"></recupera-senha>
</div>
<script src="{{ mix('js/funcoes.js') }}"></script>
<script src="{{ mix('js/recuperasenha/app.js') }}"></script>
</body>
</html>
