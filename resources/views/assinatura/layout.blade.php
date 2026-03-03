<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Assinatura Digital')</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <style>
        :root {
            --assin-primary: #0f4c60;
            --assin-primary-dark: #083748;
            --assin-accent: #2f8f83;
            --assin-bg: #eef3f6;
            --assin-card-border: #dbe5eb;
            --assin-muted: #5b6b74;
        }

        body.assin-page {
            background: linear-gradient(180deg, #f7fafc 0%, var(--assin-bg) 100%);
            min-height: 100vh;
            color: #1f2d35;
        }

        .assin-shell {
            max-width: 980px;
        }

        .assin-brand {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--assin-primary);
            background: #e7f0f4;
            border: 1px solid #cfe0e8;
            border-radius: 999px;
            padding: .4rem .8rem;
            margin-bottom: 1.1rem;
        }

        .assin-logo-wrap {
            height: 86px;
            display: flex;
            align-items: center;
            margin-bottom: .35rem;
        }

        .assin-brand-logo {
            height: 60px;
            width: auto;
            display: block;
        }

        .assin-brand-text {
            color: #21424f;
        }

        .assin-card {
            border: 1px solid var(--assin-card-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 28px rgba(8, 41, 54, .08);
        }

        .assin-card .card-header h5 {
            color: white !important;
        }

        .assin-card .card-header {
            background: linear-gradient(90deg, var(--assin-primary) 0%, #166179 100%);
            color: #fff;
            border: 0;
            padding: 1rem 1.25rem;
        }

        .assin-card .card-body {
            padding: 1.25rem;
        }

        .assin-subtitle {
            font-size: .9rem;
            opacity: .9;
            margin-top: .15rem;
            margin-bottom: 0;
        }

        .assin-chip {
            display: inline-flex;
            align-items: center;
            background: #f1f6f8;
            border: 1px solid #d6e3ea;
            color: #24414f;
            border-radius: 999px;
            padding: .3rem .7rem;
            font-size: .82rem;
            margin-right: .45rem;
            margin-bottom: .45rem;
        }

        .assin-section {
            border: 1px solid #dbe5eb;
            border-radius: 12px;
            background: #f8fbfd;
            padding: 1rem;
        }

        .assin-section-title {
            font-size: .9rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--assin-muted);
            margin-bottom: .6rem;
        }

        .assin-origin {
            background: #eaf3f7;
            border: 1px solid #d0e1e9;
            color: #1f3b48;
            border-radius: 10px;
            padding: .75rem .9rem;
        }

        .assin-pdf-wrap {
            border: 1px solid #dbe5eb;
            border-radius: 10px;
            background: #fff;
            padding: .5rem;
        }

        .assin-page .alert {
            border-radius: 10px;
            border: 1px solid transparent;
        }

        .assin-page .alert-success {
            border-color: #b8e0cb;
            background: #ecf9f1;
            color: #175d3a;
        }

        .assin-page .alert-danger {
            border-color: #f3c7cd;
            background: #fff1f3;
            color: #7b1f2d;
        }

        .assin-page .btn-primary {
            background: var(--assin-primary);
            border-color: var(--assin-primary);
        }

        .assin-page .btn-primary:hover,
        .assin-page .btn-primary:focus {
            background: var(--assin-primary-dark);
            border-color: var(--assin-primary-dark);
        }

        .assin-page .btn-success {
            background: var(--assin-accent);
            border-color: var(--assin-accent);
        }

        .assin-page .btn-success:hover,
        .assin-page .btn-success:focus {
            background: #27796f;
            border-color: #27796f;
        }

        .assin-status-icon {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .assin-status-ok {
            background: #e7f8ee;
            color: #1f7a48;
            border: 1px solid #bfe7cf;
        }

        .assin-status-info {
            background: #e9f3fb;
            color: #1d5f8c;
            border: 1px solid #c8e0f3;
        }

        .assin-status-warn {
            background: #fff8e8;
            color: #915e00;
            border: 1px solid #f3dfac;
        }
    </style>
</head>

<body class="assin-page">
    <div class="container assin-shell py-4">
        <div class="assin-logo-wrap">
            <img src="{{ asset('images/bpin_mybp_color.svg') }}" alt="BPIN MyBP" class="assin-brand-logo">
        </div>
        <div class="assin-brand">
            <span class="assin-brand-text">Assinatura Digital</span>
        </div>
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
    <script src="{{ asset('js/funcoes.js') }}"></script>
    @stack('scripts')
</body>

</html>
