<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }} — Verificação</title>
    @include('layouts.favicon')
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
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
            --login-font-titulo: 'Montserrat', Arial, sans-serif;
            --login-font-texto: 'Nunito', Verdana, sans-serif;
        }

        * { box-sizing: border-box; }

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
            max-width: 440px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 1.25rem;
            background: var(--login-surface);
            box-shadow: 0 28px 64px rgba(3, 20, 30, 0.42);
            overflow: hidden;
        }

        .login-card__brand {
            padding: 1.75rem 1.75rem 0.5rem;
            text-align: center;
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
            padding: 0.75rem 1.75rem 1.75rem;
        }

        .login-card__title {
            margin: 0 0 1.25rem;
            font-family: var(--login-font-titulo);
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--login-ink);
            line-height: 1.25;
        }

        .login-card__title span {
            display: block;
            margin-top: 0.35rem;
            font-family: var(--login-font-texto);
            font-size: 0.92rem;
            font-weight: 400;
            letter-spacing: 0;
            color: var(--login-muted);
        }

        .login-field {
            margin-bottom: 1rem;
        }

        .login-field label {
            display: block;
            margin-bottom: 0.35rem;
            font-family: var(--login-font-texto);
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--login-ink);
        }

        .login-field .form-control {
            min-height: 48px;
            border-radius: 0.8rem;
            border-color: var(--login-line);
            letter-spacing: 0.28em;
            text-align: center;
            text-transform: uppercase;
            font-family: var(--login-font-titulo);
            font-weight: 700;
            font-size: 1.15rem;
        }

        .login-field .form-control:focus {
            border-color: var(--login-teal);
            box-shadow: 0 0 0 3px var(--login-focus);
        }

        .login-field .form-control:disabled {
            background: #f1f4f6;
            cursor: not-allowed;
        }

        .login-destinos {
            margin: 0 0 1rem;
            padding: 0.85rem 1rem;
            border-radius: 0.8rem;
            background: rgba(11, 27, 61, 0.06);
            font-size: 0.88rem;
            color: var(--login-muted);
        }

        .login-destinos strong {
            color: var(--login-ink);
        }

        .login-actions .btn.btn-primary {
            min-height: 48px;
            border: 0;
            border-radius: 0.8rem;
            font-family: var(--login-font-titulo);
            font-weight: 700;
            color: #fff !important;
            background: var(--login-marinho) !important;
            box-shadow: 0 10px 20px rgba(11, 27, 61, 0.28);
            transition: transform .15s ease, box-shadow .2s ease, background .2s ease;
        }

        .login-actions .btn.btn-primary:hover,
        .login-actions .btn.btn-primary:focus {
            background: #13264f !important;
            box-shadow: 0 12px 24px rgba(11, 27, 61, 0.36);
            transform: translateY(-1px);
            color: #fff !important;
        }

        .login-actions .btn.btn-primary:active {
            transform: translateY(0);
            background: var(--login-noite) !important;
        }

        .login-actions .btn.btn-primary:disabled {
            opacity: 0.65;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .login-actions .btn.btn-secondary {
            min-height: 44px;
            border: 1px solid var(--login-marinho) !important;
            border-radius: 0.8rem;
            background: #fff !important;
            color: var(--login-marinho) !important;
            font-family: var(--login-font-texto);
            font-weight: 600;
            transition: background .15s ease, color .15s ease, box-shadow .2s ease;
        }

        .login-actions .btn.btn-secondary:hover,
        .login-actions .btn.btn-secondary:focus {
            background: var(--login-marinho) !important;
            color: #fff !important;
            box-shadow: 0 8px 16px rgba(11, 27, 61, 0.22);
        }

        .login-actions .btn.btn-secondary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            box-shadow: none;
        }

        .login-alert {
            margin-bottom: 1rem;
            padding: 0.75rem 0.9rem;
            border-radius: 0.7rem;
            font-size: 0.9rem;
        }

        .login-alert--ok {
            background: rgba(25, 135, 84, 0.12);
            color: #146c43;
        }

        .login-alert--error {
            background: rgba(220, 53, 69, 0.12);
            color: #b02a37;
        }

        .login-countdown {
            margin: 0 0 1rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--login-muted);
            font-variant-numeric: tabular-nums;
        }

        .login-countdown strong {
            color: var(--login-teal);
            font-weight: 700;
        }

        .login-countdown[hidden] {
            display: none;
        }
    </style>
</head>
<body class="my-login-page">
<div class="login-shell">
    <div class="login-card">
        <div class="login-card__brand">
            <img src="{{ asset('images/mybpin-logo-fundo-claro.webp') }}" alt="MyBPIN" width="280" height="74">
        </div>

        <div class="login-card__body"
             data-codigo-segundos="{{ (int) ($codigoSegundos ?? 0) }}"
             data-reenvio-segundos="{{ (int) ($reenvioSegundos ?? 0) }}"
             data-bloqueio-segundos="{{ (int) ($bloqueioSegundos ?? 0) }}">
            <p class="login-card__title">
                Verificação em duas etapas
                <span id="mfa-titulo-validade">
                    Informe o código enviado para você.
                    Válido por <strong id="mfa-codigo-valor">{{ $ttlMinutos }} min</strong>.
                </span>
            </p>

            @if(session('success'))
                <div class="login-alert login-alert--ok">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="login-alert login-alert--error" id="mfa-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <p class="login-countdown" id="mfa-countdown-bloqueio" hidden>
                Bloqueado por tentativas. Tente novamente em <strong id="mfa-bloqueio-valor">0</strong>.
            </p>

            @if(!empty($destinosMascarados))
                <div class="login-destinos">
                    Código enviado para:
                    @if(!empty($destinosMascarados['email']))
                        <div><strong>E-mail:</strong> {{ $destinosMascarados['email'] }}</div>
                    @endif
                    @if(!empty($destinosMascarados['whatsapp']))
                        <div><strong>WhatsApp:</strong> {{ $destinosMascarados['whatsapp'] }}</div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('login.mfa.verify') }}" id="mfa-form-verify">
                @csrf
                <div class="login-field form-group">
                    <label for="codigo">Código de verificação</label>
                    <input id="codigo" type="text"
                           class="form-control{{ $errors->has('codigo') ? ' is-invalid' : '' }}"
                           name="codigo"
                           maxlength="8"
                           autocomplete="one-time-code"
                           inputmode="text"
                           placeholder="XXXXXXXX"
                           required autofocus>
                </div>

                <div class="form-group login-actions">
                    <button type="submit" class="btn btn-primary btn-block" id="mfa-btn-confirmar">
                        Confirmar e entrar
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('login.mfa.resend') }}" class="mt-2" id="mfa-form-resend">
                @csrf
                <div class="form-group login-actions mb-2">
                    <button type="submit" class="btn btn-secondary btn-block" id="mfa-btn-reenviar">
                        Reenviar código
                    </button>
                </div>
            </form>

            <div class="form-group login-actions mb-0">
                <a href="{{ route('login') }}" class="btn btn-secondary btn-block">
                    Voltar ao login
                </a>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var root = document.querySelector('.login-card__body');
    if (!root) return;

    var codigoRestante = parseInt(root.getAttribute('data-codigo-segundos') || '0', 10) || 0;
    var reenvioRestante = parseInt(root.getAttribute('data-reenvio-segundos') || '0', 10) || 0;
    var bloqueioRestante = parseInt(root.getAttribute('data-bloqueio-segundos') || '0', 10) || 0;

    var elCodigoValor = document.getElementById('mfa-codigo-valor');
    var elTituloValidade = document.getElementById('mfa-titulo-validade');
    var elBloqueio = document.getElementById('mfa-countdown-bloqueio');
    var elBloqueioValor = document.getElementById('mfa-bloqueio-valor');
    var btnReenviar = document.getElementById('mfa-btn-reenviar');
    var btnConfirmar = document.getElementById('mfa-btn-confirmar');
    var inputCodigo = document.getElementById('codigo');
    var alertError = document.getElementById('mfa-alert-error');

    function formatar(segundos) {
        segundos = Math.max(0, segundos | 0);
        if (segundos < 60) {
            return segundos + (segundos === 1 ? ' segundo' : ' segundos');
        }
        var m = Math.floor(segundos / 60);
        var s = segundos % 60;
        if (s === 0) {
            return m + (m === 1 ? ' minuto' : ' minutos');
        }
        return m + ' min ' + String(s).padStart(2, '0') + 's';
    }

    function atualizarValidadeCodigo() {
        if (!elCodigoValor || !elTituloValidade) return;

        if (codigoRestante > 0) {
            elTituloValidade.innerHTML =
                'Informe o código enviado para você. Válido por <strong id="mfa-codigo-valor">' +
                formatar(codigoRestante) +
                '</strong>.';
            elCodigoValor = document.getElementById('mfa-codigo-valor');
            if (btnConfirmar && bloqueioRestante <= 0) btnConfirmar.disabled = false;
            if (inputCodigo && bloqueioRestante <= 0) inputCodigo.disabled = false;
        } else {
            elTituloValidade.innerHTML =
                'O código expirou. Use <strong>Reenviar código</strong> para receber um novo.';
            if (btnConfirmar) btnConfirmar.disabled = true;
            if (inputCodigo) inputCodigo.disabled = true;
        }
    }

    function atualizarUi() {
        atualizarValidadeCodigo();

        if (bloqueioRestante > 0) {
            elBloqueio.hidden = false;
            elBloqueioValor.textContent = formatar(bloqueioRestante);
            if (btnConfirmar) btnConfirmar.disabled = true;
            if (inputCodigo) inputCodigo.disabled = true;
            if (btnReenviar) btnReenviar.disabled = true;
            return;
        }

        elBloqueio.hidden = true;

        if (reenvioRestante > 0) {
            if (btnReenviar) {
                btnReenviar.disabled = true;
                btnReenviar.textContent = 'Reenviar código (' + reenvioRestante + 's)';
            }
        } else {
            if (btnReenviar) {
                btnReenviar.disabled = false;
                btnReenviar.textContent = 'Reenviar código';
            }
            if (alertError && /reenviar/i.test(alertError.textContent || '')) {
                alertError.hidden = true;
            }
        }
    }

    function tick() {
        var ativo = false;
        if (bloqueioRestante > 0) {
            bloqueioRestante -= 1;
            ativo = true;
        }
        if (reenvioRestante > 0) {
            reenvioRestante -= 1;
            ativo = true;
        }
        if (codigoRestante > 0) {
            codigoRestante -= 1;
            ativo = true;
        }
        atualizarUi();
        if (bloqueioRestante > 0 || reenvioRestante > 0 || codigoRestante > 0) {
            window.setTimeout(tick, 1000);
        } else if (alertError && /tentativas|reenviar/i.test(alertError.textContent || '')) {
            alertError.hidden = true;
        }
    }

    atualizarUi();
    if (bloqueioRestante > 0 || reenvioRestante > 0 || codigoRestante > 0) {
        window.setTimeout(tick, 1000);
    }
})();
</script>
</body>
</html>
