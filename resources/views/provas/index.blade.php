<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="msapplication-TileColor" content="#072433">
    <meta name="msapplication-TileImage" content="{{asset('/')}}ms-icon-144x144.png">
    <meta name="theme-color" content="#072433">
    <title>@yield('title')</title>
    <link rel="preload" href="{{mix('js/app.js')}}" as="script">
    <link rel="preload" href="{{mix('js/funcoes.js')}}" as="script">
    <link rel="apple-touch-icon" sizes="57x57" href="{{asset('/')}}apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="{{asset('/')}}apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="{{asset('/')}}apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('/')}}apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="{{asset('/')}}apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="{{asset('/')}}apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="{{asset('/')}}apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="{{asset('/')}}apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('/')}}apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="{{asset('/')}}android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('/')}}favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="{{asset('/')}}favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('/')}}favicon-16x16.png">
    <link rel="manifest" href="{{asset('/')}}manifest.json">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    @stack('css')
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
    </style>
</head>
<body>
<div id="app" v-cloak>

{{--    <modal id="vinculo" size="g" titulo="Informações" :fechar="false">--}}
{{--        <template slot="conteudo">--}}
{{--            <fieldset>--}}
{{--                <legend>Informações</legend>--}}
{{--                <div class="form-group">--}}
{{--                    <label>--}}
{{--                        Têm parentes trabalhando no Grupo Equatorial (consanguíneos, por afinidade ou relação--}}
{{--                        íntima)?</label>--}}
{{--                    <select class="form-control" v-model="formVinculo.parente" onblur="valida_campo_vazio(this,1)"--}}
{{--                            onchange="valida_campo_vazio(this,1)">--}}
{{--                        <option value="">Selecione...</option>--}}
{{--                        <option :value="true">Sim</option>--}}
{{--                        <option :value="false">Não</option>--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                <template v-if="formVinculo.parente">--}}

{{--                    <div class="form-group">--}}
{{--                        <label>Nome:</label>--}}
{{--                        <input type="text" class="form-control" v-model="formVinculo.nome"--}}
{{--                               onblur="valida_campo_vazio(this,1)">--}}
{{--                    </div>--}}

{{--                    <div class="form-group">--}}
{{--                        <label>Função:</label>--}}
{{--                        <input type="text" class="form-control" v-model="formVinculo.funcao"--}}
{{--                               onblur="valida_campo_vazio(this,1)">--}}
{{--                    </div>--}}

{{--                    <div class="form-group">--}}
{{--                        <label>Qual o grau de parentesco com esta pessoa:</label>--}}
{{--                        <input type="text" class="form-control" v-model="formVinculo.grau_parentesco"--}}
{{--                               onblur="valida_campo_vazio(this,1)">--}}
{{--                    </div>--}}
{{--                </template>--}}

{{--                <div class="form-group">--}}
{{--                    <label>Já foi empregado no Grupo Equatorial ou empresas parceiras?</label>--}}
{{--                    <select class="form-control" v-model="formVinculo.foi_empregado"--}}
{{--                            onblur="valida_campo_vazio(this,1)"--}}
{{--                            onchange="valida_campo_vazio(this,1)">--}}
{{--                        <option value="">Selecione...</option>--}}
{{--                        <option :value="true">Sim</option>--}}
{{--                        <option :value="false">Não</option>--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                <div class="form-group" v-if="formVinculo.foi_empregado">--}}
{{--                    <label>Já foi empregado no Grupo Equatorial ou empresas parceiras?</label>--}}
{{--                    <select class="form-control" v-model="formVinculo.local_empregado"--}}
{{--                            onblur="valida_campo_vazio(this,1)"--}}
{{--                            onchange="valida_campo_vazio(this,1)">--}}
{{--                        <option value="">Selecione...</option>--}}
{{--                        <option value="Equatorial Maranhão / Cemar">Equatorial Maranhão / Cemar</option>--}}
{{--                        <option value="Celpa">Celpa</option>--}}
{{--                        <option value="55 Soluções">55 Soluções</option>--}}
{{--                        <option value="55 Atende">55 Atende</option>--}}
{{--                        <option value="Outras empresas parceiras">Outras empresas parceiras</option>--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                <div class="form-group" v-if="formVinculo.local_empregado === 'Outras empresas parceiras'">--}}
{{--                    <label>Nome da empresa parceira:</label>--}}
{{--                    <input type="text" class="form-control" v-model="formVinculo.outra_empresa_parceira"--}}
{{--                           onblur="valida_campo_vazio(this,1)">--}}
{{--                </div>--}}

{{--            </fieldset>--}}
{{--        </template>--}}
{{--        <template slot="rodape">--}}
{{--            <button class="btn btn-primary" @click="salvarVinculo"><i class="fa fa-save"></i> Salvar</button>--}}
{{--        </template>--}}
{{--    </modal>--}}

    <div class="container-fluid"
         style="background: url(https://site.bpse.com.br/img/b_blue.png) no-repeat #072333; background-size: cover;"
         v-if="!autenticado">
        <div class="container">
            <div class="row">
                <div class="col-md-12 min-vh-100 d-flex flex-column justify-content-center">
                    <div class="row">
                        <div class="col-lg-6 col-md-8 mx-auto">
                            <div class="card rounded shadow shadow-sm">
                                <div class="card-header">
                                    <img src="{{ asset('logo_bpse_color.png') }}" class="img-fluid" alt="logo_bpse">
                                </div>
                                <div class="card-body">
                                    <form @submit.prevent="autenticar" class="form" role="form" id="formAutenticar">

                                        <input type="hidden" id="vaga_id" value="{{$simulado->vaga_id}}">
                                        <input type="hidden" id="simulado_vaga_id" value="{{$simulado->id}}">
                                        <input type="hidden" id="simulado_id" value="{{$simulado->Simulado->id}}">
                                        <input type="hidden" id="empresa_id" value="{{$empresa_id}}">
                                        <input type="hidden" id="vagas_abertas_id" value="{{$vagas_abertas_id}}">
                                        <div class="form-group">
                                            <label for="cpf">CPF</label>
                                            <input value="040.870.766-60" type="text" class="form-control" autofocus
                                                   v-model="formUser.cpf" v-mascara:cpf onblur="valida_cpf_vazio(this)">
                                        </div>
                                        <div class="form-group">
                                            <label>Data nascimento</label>
                                            <input value="27/10/1980" type="text" class="form-control"
                                                   v-model="formUser.nascimento" v-mascara:data
                                                   onblur="valida_data_vazio(this)">
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-lg btn-block float-right">
                                            ACESSAR PROVA
                                        </button>
                                    </form>

{{--                                    <div class="footer py-3 d-flex justify-content-center">--}}
                                    {{--                                        <img src="https://site.bpse.com.br/img/logo_procem.png" alt=""--}}
                                    {{--                                             class=" " style="height: 130px">--}}
                                    {{--                                        <img src="https://site.bpse.com.br/img/selo_gptw.png" alt="" class=" "--}}
                                    {{--                                             style="height: 130px">--}}
                                    {{--                                    </div>--}}

                                    {{--                                    <div class="social-links d-flex justify-content-around">--}}
                                    {{--                                        <a href="https://instagram.com/sejabpse" target="_blank" class="instagram"><i--}}
                                    {{--                                                class="fab fa-instagram"></i></a>--}}
                                    {{--                                        <a href="https://www.linkedin.com/company/bpse/" target="_blank"--}}
                                    {{--                                           class="linkedin"><i class="fab fa-linkedin"></i></a>--}}
                                    {{--                                        <a href="https://fb.com/bpse1" target="_blank" class="facebook"><i--}}
                                    {{--                                                class="fab fa-facebook"></i></a>--}}
                                    {{--                                    </div>--}}

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid min-vh-100" v-if="autenticado && !preloadAutenticacao">

        <div class="row py-3 bg-primary">
            <div class="mx-auto">
                <img src="{{ asset('img/logo_white.png') }}" class="img-fluid" alt="">
            </div>
        </div>

        <div class="row py-3">
            <div class="col-12">
                <h4>Prova: {{ $simulado->Simulado->titulo }}</h4>
                <h4>Duração total: {{ $simulado->duracao }} minutos</h4>
            </div>
        </div>

        <div class="row bg-white py-3">
            <div class="col-12">
                <simulado :vagas_abertas_id="formUser.vagas_abertas_id"
                          :empresa_id="formUser.empresa_id"
                          :simulado_id="formUser.simulado_id"
                          :simulado_vaga_id="formUser.simulado_vaga_id"
                          :feedback_id="formUser.feedback_id"
                          :curriculo_id="formUser.curriculo_id"></simulado>
            </div>
        </div>

        <div class="row py-3 bg-primary">
            <div class="col-12">
                <div class="py-3 d-flex justify-content-center">
                    <img src="https://site.bpse.com.br/img/logo_procem.png" alt=""
                         class=" " style="height: 100px">
                    <img src="https://site.bpse.com.br/img/selo_gptw.png" alt="" class=" "
                         style="height: 100px">
                </div>
            </div>
        </div>
    </div>

</div>
<script src="{{mix('js/app.js')}}"></script>
<script src="{{mix('js/funcoes.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script src="{{mix('js/provas/app.js')}}"></script>
</body>
</html>
