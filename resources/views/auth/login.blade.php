<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{env('APP_NAME')}}</title>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @if(env('APP_ENV') !== 'local')
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
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <style>
        .my-login-page .footer {
            margin: inherit;
        }


        .footer {
            margin-top: 20px;
            width: 100%;
            height: inherit;
            line-height: inherit;
        }

        .social-links a {
            font-size: 18px;
            display: inline-block;
            background: #0F4C60;
            color: #fff;
            line-height: 1;
            padding: 8px 0;
            margin-right: 4px;
            border-radius: 50%;
            text-align: center;
            width: 36px;
            height: 36px;
            transition: .3s;
        }

        .social-links a:hover {
            background: #031E2D;
            color: #fff;
        }

        [v-cloak] {
            display: none;
        }
    </style>
</head>
<body class="my-login-page"
      style="background: url({{ asset('images/bg_login_bpin_mybp.jpg') }}) no-repeat #072333; background-size: cover;"
    {{--      style="background: url({{assets('imagens/bg-business.jpg')}});"--}}
>
<div id="app" v-cloak class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-md-12 min-vh-100 d-flex flex-column justify-content-center">
                <div class="row">
                    <div class="col-lg-6 col-md-8 mx-auto">
                        <div class="card rounded shadow shadow-sm">
                            <div class="card-header bg-white text-center">
                                <img src="{{ asset('images/bpin_mybp_color.svg') }}" class="" alt="logo_bpse"
                                     style="height: 120px">
                            </div>
                            <div class="card-body">
                                <form method="POST" id="demo-form" v-show="!recuperaSenha"
                                      action="{{ route('login') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="login">Usuário</label>
                                        <input id="login" type="text"
                                               class="form-control{{ $errors->has('login') ? ' is-invalid' : '' }}"
                                               name="login"
                                               onblur="removeEspaco(this);validaEmailVazio(this);"
                                               onkeyup="removeEspaco(this);validaEmailVazio(this);"
                                               value="" required autofocus>
                                        @if($errors->has('login'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('login') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Senha</label>
                                        <input id="password" type="password"
                                               class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                               name="password" required>
                                        @if($errors->has('password'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                        <a href="javascript://" @click.prevent="recuperaSenha = !recuperaSenha"
                                           class="float-right text-default">
                                            Esqueceu a Senha?
                                        </a>
                                    </div>

                                    {{--                                    <div class="form-group" style="display: none">--}}
                                    {{--                                        <div class="custom-checkbox custom-control">--}}
                                    {{--                                            <input type="checkbox" name="remember" id="remember"--}}
                                    {{--                                                   class="custom-control-input tbtn-default">--}}
                                    {{--                                            <label for="remember" class="custom-control-label">Lembrar-me</label>--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </div>--}}

                                    <div class="pb-3 ">
                                        {{--                                        <div class="g-recaptcha form-group" data-sitekey="{{env('RECAPTCHA_SITE_KEY')}}"--}}
                                        {{--                                             data-callback="getToken"--}}
                                        {{--                                             data-expired-callback="limpaToken"--}}
                                        {{--                                             data-error-callback="erroToken"></div>--}}
                                        <input type="hidden" ref="token" id="token">
                                        @if($errors->has('g-recaptcha-response'))
                                            <span class="text-danger">
                                                <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group m-0">
                                        <button data-sitekey="{{env('RECAPTCHA_SITE_KEY')}}" data-callback='onSubmit'
                                                type="submit" class="btn btn-primary btn-block g-recaptcha">
                                            ENTRAR NO SISTEMA
                                        </button>
                                    </div>
                                </form>

                                <form @submit.prevent="solicitaSenha" id="formSenha" v-if="recuperaSenha">
                                    <div class="form-group">
                                        <label for="login">E-mail</label>
                                        <input id="login" type="text"
                                               onblur="removeEspaco(this);validaEmailVazio(this);"
                                               onkeyup="removeEspaco(this);validaEmailVazio(this);"
                                               class="form-control"
                                               v-model="form.login">
                                    </div>

                                    <div class="form-group m-0">
                                        <button type="submit" @click.prevent="solicitaSenha"
                                                class="btn btn-primary btn-block">
                                            RECUPERAR SENHA
                                        </button>
                                    </div>

                                    <div class="form-group mt-2">
                                        <button type="submit" @click.prevent="recuperaSenha=!recuperaSenha"
                                                class="btn btn-secondary btn-sm btn-block">
                                            VOLTAR
                                        </button>
                                    </div>
                                </form>

                                <div class="py-3"
                                     style="display: flex; justify-content: space-around; align-items: center;">
                                    <img src="{{ asset('images/inova_maranhao.png') }}" alt=""
                                         class=" " style="height: 60px">
                                    <img src="{{ asset('images/fapema-logo.png') }}" alt=""
                                         class=" " style="height: 60px">
                                    <img src="https://bpse.com.br/img/logo_procem.png" alt=""
                                         class=" " style="height: 90px">
                                    <img src="https://bpse.com.br/img/selo_gptw.png" alt="" class=" "
                                         style="height: 90px">

                                </div>

                                <div class="social-links d-flex justify-content-around">
                                    <a href="https://instagram.com/sejabpse" target="_blank" class="instagram"><i
                                            class="fab fa-instagram"></i></a>
                                    <a href="https://www.linkedin.com/company/bpse/" target="_blank" class="linkedin"><i
                                            class="fab fa-linkedin"></i></a>
                                    <a href="https://fb.com/bpse1" target="_blank" class="facebook"><i
                                            class="fab fa-facebook"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

</div>
<script src="{{ mix('js/app.js')}}"></script>
<script src="{{mix('js/funcoes.js')}}"></script>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            mostraSenha: false,
            recuperaSenha: false,

            form: {
                login: '',
            },
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

        },
    });

    function removeEspaco(campo) {
        campo.value = campo.value.replace(/\s/g, '');
    }
</script>
</body>
</html>
