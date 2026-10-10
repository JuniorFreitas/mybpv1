<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }}</title>
    @include('layouts.favicon')
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

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
        <script src="https://www.google.com/recaptcha/api.js?hl=pt-BR" async defer></script>
        <script type="text/javascript">
            function onSubmit(token) {
                document.getElementById("demo-form").submit();
            }

            function getToken(dados) {
                document.getElementById('token').value = dados;
            }

            function limpaToken() {
                document.getElementById('token').value = '';
            }

            function erroToken() {
                alert('Erro ao validar o reCapTcha. Tente mais tarde')
                document.getElementById('token').value = '';
            }
        </script>
    @endif
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

        * {
            box-sizing: border-box;
        }

        [v-cloak] {
            display: none;
        }

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
            animation: loginEnter 420ms ease-out;
        }

        @keyframes loginEnter {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            padding: 1.5rem 1.75rem 1.35rem;
        }

        .login-card__title {
            margin: 0 0 1.15rem;
            font-family: var(--login-font-titulo);
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--login-ink);
        }

        .login-card__title span {
            display: block;
            margin-top: 0.25rem;
            font-family: var(--login-font-texto);
            font-size: 0.9rem;
            font-weight: 400;
            letter-spacing: 0;
            color: var(--login-muted);
        }

        .login-field {
            margin-bottom: 1rem;
        }

        .login-field label {
            display: block;
            margin-bottom: 0.4rem;
            font-family: var(--login-font-texto);
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
            font-family: var(--login-font-texto);
            font-size: 0.95rem;
            font-weight: 400;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .login-field .form-control::placeholder {
            color: #8aa0ab;
        }

        .login-field .form-control:focus {
            border-color: var(--login-teal);
            background-color: #fff !important;
            box-shadow: 0 0 0 3px var(--login-focus);
            outline: 0;
        }

        .login-field .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
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
            transition: color .2s ease, background .2s ease;
        }

        .login-password__toggle:hover,
        .login-password__toggle:focus {
            color: var(--login-marinho);
            background: rgba(11, 27, 61, 0.08);
            outline: 0;
        }

        .login-forgot-row {
            display: flex;
            justify-content: flex-end;
            margin: -0.15rem 0 1rem;
        }

        .login-forgot {
            font-family: var(--login-font-texto);
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--login-teal);
            text-decoration: none;
        }

        .login-forgot:hover {
            color: var(--login-teal-deep);
            text-decoration: underline;
        }

        .login-actions {
            margin: 0;
        }

        .login-actions .btn.btn-primary {
            min-height: 48px;
            border: 0;
            border-radius: 0.8rem;
            font-family: var(--login-font-titulo);
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #fff !important;
            background: var(--login-marinho) !important;
            box-shadow: 0 10px 20px rgba(11, 27, 61, 0.28);
            transition: transform .15s ease, box-shadow .2s ease, filter .2s ease, background .2s ease;
        }

        .login-actions .btn.btn-primary:hover,
        .login-actions .btn.btn-primary:focus {
            filter: none;
            background: #13264f !important;
            box-shadow: 0 12px 24px rgba(11, 27, 61, 0.36);
            transform: translateY(-1px);
            color: #fff !important;
        }

        .login-actions .btn.btn-primary:active {
            transform: translateY(0);
            background: var(--login-noite) !important;
        }

        .login-actions .btn.btn-secondary {
            min-height: 44px;
            border: 1px solid var(--login-marinho) !important;
            border-radius: 0.8rem;
            background: #fff !important;
            color: var(--login-marinho) !important;
            box-shadow: none;
            font-family: var(--login-font-texto);
            font-weight: 600;
            letter-spacing: 0;
            transition: background .15s ease, color .15s ease, box-shadow .2s ease;
        }

        .login-actions .btn.btn-secondary:hover,
        .login-actions .btn.btn-secondary:focus {
            filter: none;
            background: var(--login-marinho) !important;
            color: #fff !important;
            box-shadow: 0 8px 16px rgba(11, 27, 61, 0.22);
            transform: none;
        }

        .login-captcha-error {
            margin-bottom: 0.85rem;
        }

        .login-parceiros {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 0.85rem 1.1rem;
            margin-top: 1.4rem;
            padding-top: 1.2rem;
            border-top: 1px solid var(--login-line);
        }

        .login-parceiros img {
            display: block;
            height: 44px;
            max-height: 44px;
            width: auto;
            max-width: 100px;
            object-fit: contain;
            opacity: 0.92;
            transition: opacity .2s ease, transform .2s ease;
        }

        .login-parceiros img:hover {
            opacity: 1;
            transform: translateY(-1px);
        }

        .social-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.65rem;
            margin-top: 1.05rem;
        }

        .social-links a {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            background: var(--login-teal);
            text-decoration: none;
            transition: transform .15s ease, background .2s ease, box-shadow .2s ease;
        }

        .social-links a:hover {
            background: var(--login-teal-deep);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(8, 56, 71, 0.28);
        }

        @media (max-width: 575.98px) {
            .login-shell {
                padding: 1rem 0.75rem;
            }

            .login-card__brand,
            .login-card__body {
                padding-left: 1.2rem;
                padding-right: 1.2rem;
            }

            .login-card__brand img {
                max-height: 60px;
            }

            .login-parceiros img {
                height: 40px;
                max-height: 40px;
                max-width: 88px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-card {
                animation: none;
            }

            .login-actions .btn,
            .social-links a,
            .login-parceiros img {
                transition: none;
            }
        }
    </style>
</head>
<body class="my-login-page">
<div id="app" v-cloak class="login-shell">
    <div class="login-card">
        <div class="login-card__brand">
            <img src="{{ asset('images/mybpin-logo-fundo-claro.webp') }}" alt="MyBPin" width="280" height="74">
        </div>

        <div class="login-card__body">
            <form method="POST" id="demo-form" v-show="!recuperaSenha" action="{{ route('login') }}">
                @csrf
                <p class="login-card__title">
                    Acesse sua conta
                    <span>Entre com seu e-mail e senha</span>
                </p>

                <div class="login-field form-group">
                    <label for="login">Usuário</label>
                    <input id="login" type="text"
                           class="form-control{{ $errors->has('login') ? ' is-invalid' : '' }}"
                           name="login"
                           autocomplete="username"
                           placeholder="seu.email@empresa.com"
                           onblur="removeEspaco(this);validaEmailVazio(this);"
                           onkeyup="removeEspaco(this);validaEmailVazio(this);"
                           value="" required autofocus>
                    @if($errors->has('login'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('login') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="login-field form-group">
                    <label for="password">Senha</label>
                    <div class="login-password">
                        <input id="password" :type="mostraSenha ? 'text' : 'password'"
                               class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                               name="password"
                               autocomplete="current-password"
                               placeholder="••••••••"
                               required>
                        <button type="button" class="login-password__toggle"
                                @click="mostraSenha = !mostraSenha"
                                :aria-label="mostraSenha ? 'Ocultar senha' : 'Mostrar senha'">
                            <i :class="mostraSenha ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                    @if($errors->has('password'))
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="login-forgot-row">
                    <a href="javascript://" @click.prevent="recuperaSenha = !recuperaSenha" class="login-forgot">
                        Esqueceu a senha?
                    </a>
                </div>

                <input type="hidden" ref="token" id="token">
                @if($errors->has('g-recaptcha-response'))
                    <div class="login-captcha-error">
                        <span class="text-danger">
                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                        </span>
                    </div>
                @endif

                <div class="form-group login-actions">
                    <button data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-callback="onSubmit"
                            type="submit" class="btn btn-primary btn-block g-recaptcha">
                        Entrar no sistema
                    </button>
                </div>
            </form>

            <form @submit.prevent="solicitaSenha" id="formSenha" v-if="recuperaSenha">
                <p class="login-card__title">
                    Recuperar senha
                    <span>Enviaremos as instruções para o seu e-mail</span>
                </p>

                <div class="login-field form-group">
                    <label for="login-recupera">E-mail</label>
                    <input id="login-recupera" type="text"
                           onblur="removeEspaco(this);validaEmailVazio(this);"
                           onkeyup="removeEspaco(this);validaEmailVazio(this);"
                           class="form-control"
                           placeholder="seu.email@empresa.com"
                           v-model="form.login">
                </div>

                <div class="form-group login-actions">
                    <button type="submit" @click.prevent="solicitaSenha"
                            class="btn btn-primary btn-block">
                        Recuperar senha
                    </button>
                </div>

                <div class="form-group login-actions mt-2 mb-0">
                    <button type="button" @click.prevent="recuperaSenha = !recuperaSenha"
                            class="btn btn-secondary btn-block">
                        Voltar
                    </button>
                </div>
            </form>

            <div class="login-parceiros" aria-label="Parceiros e selos">
                <img src="{{ asset('images/inova_maranhao.png') }}" alt="Inova Maranhão">
                <img src="{{ asset('images/fapema-logo.png') }}" alt="FAPEMA">
                <img src="https://bpse.com.br/img/logo_procem.png" alt="Procem">
                <img src="https://bpse.com.br/img/selo_gptw.png" alt="Great Place to Work">
            </div>

            <div class="social-links" aria-label="Redes sociais">
                <a href="https://instagram.com/sejabpse" target="_blank" rel="noopener noreferrer"
                   class="instagram" aria-label="Instagram">
                    <i class="fab fa-instagram" aria-hidden="true"></i>
                </a>
                <a href="https://www.linkedin.com/company/bpse/" target="_blank"
                   rel="noopener noreferrer" class="linkedin" aria-label="LinkedIn">
                    <i class="fab fa-linkedin" aria-hidden="true"></i>
                </a>
                <a href="https://fb.com/bpse1" target="_blank" rel="noopener noreferrer"
                   class="facebook" aria-label="Facebook">
                    <i class="fab fa-facebook" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ mix('js/funcoes.js') }}"></script>
<script>
    const app = Vue.createApp({
        data() {
            return {
                mostraSenha: false,
                recuperaSenha: false,
                form: {
                    login: ''
                }
            }
        },
        methods: {
            solicitaSenha() {
                $('#formSenha :input:visible').trigger('blur');
                if ($('#formSenha :input:visible.is-invalid').length) {
                    mostraErro('', 'Verifique o erro')
                    return false;
                }

                axios.post(`${URL_ADMIN}/enviaSolicitacaoSenha`, this.form)
                    .then(response => {
                        mostraSucesso('', response.data.msg);
                        this.recuperaSenha = false;
                        this.form.login = '';
                    })
                    .catch(error => {
                        mostraErro('', error.response.data.msg);
                    });
            },
        }
    })

    if (window.registerGlobals) {
        window.registerGlobals(app)
    }
    app.mount('#app')

    function removeEspaco(campo) {
        campo.value = campo.value.replace(/\s/g, '');
    }
</script>
</body>
</html>
