<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="msapplication-TileColor" content="#072433">
    <title>Carta Oferta</title>
    <link rel="preload" href="{{mix('js/app.js')}}" as="script">
    <link rel="preload" href="{{mix('js/funcoes.js')}}" as="script">
    @include('layouts.favicon')
    <link rel="manifest" href="{{asset('/')}}manifest.json">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<body class="my-login-page bg-white">
<div id="app" v-cloak>
    <preload v-if="preload" :msg="msgPreload"></preload>
    <input type="hidden" id="dd" value="{{ json_encode($cartaOferta) }}">

    <div class="container-fluid min-vh-100">

        <div class="row bg-primary">
            <div class="mx-auto">
                <img src="{{env('AWS_URL')}}/public/email_{{$Empresa->apelido}}.jpg" class="img-fluid" alt="">
            </div>
        </div>

        <div class="row py-3">

            <preload v-if="preload"></preload>
            <div class="col-12" v-if="!preload && form.status === 'Aceito pelo RH'">
                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <p>Nome: <strong>@{{ form.curriculo.nome }}</strong></p>
                            <p>Cargo: <strong>@{{ form.vaga_aberta.vaga.nome }}</strong></p>
                            <div class="alert alert-success text-center">
                                Carta Oferta aceita com sucesso!
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-12" v-if="!preload && form.status === '{{\App\Models\CartaOferta::STATUS_AGUARDANDO_RH}}'">
                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <p>
                                Nome: <strong>@{{ form.curriculo.nome }}</strong><br>
                                Cargo: <strong>@{{ form.vaga_aberta.vaga.nome }}</strong>
                            </p>
                            <div class="alert alert-warning text-center">
                                Agradecemos o envio! Carta Oferta aguardando verificação pelo RH!<br>
                                Fique atento(a) ao seu e-mail para receber a lista de documentos para admissão.
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-12" v-if="!preload && form.status === '{{\App\Models\CartaOferta::STATUS_RECUSADO_RH}}'">
                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <p>
                                Nome: <strong>@{{ form.curriculo.nome }}</strong><br>
                                Cargo: <strong>@{{ form.vaga_aberta.vaga.nome }}</strong>
                            </p>
                            <div class="alert alert-danger text-center">
                                Carta Oferta recusada!
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-12" v-if="!preload && form.status === '{{\App\Models\CartaOferta::STATUS_EXPIRADO}}'">
                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <p>
                                Nome: <strong>@{{ form.curriculo.nome }}</strong><br>
                                Cargo: <strong>@{{ form.vaga_aberta.vaga.nome }}</strong>
                            </p>
                            <div class="alert alert-danger text-center">
                                Prazo expirado!
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-12" v-if="!preload && form.status === '{{\App\Models\CartaOferta::STATUS_PENDENTE_ANEXO}}'">
                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <p>
                                Nome: <strong>@{{ form.curriculo.nome }}</strong><br>
                                Cargo: <strong>@{{ form.vaga_aberta.vaga.nome }}</strong>
                            </p>
                        </div>
                    </div>
                </fieldset>
                <div class="alert alert-info">
                    A fim de prosseguir com o processo, solicitamos que você anexe a carta oferta assinada.
                    <br>O arquivo deve estar no formato PDF.
                </div>
                <fieldset>
                    <legend>Anexar CARTA OFERTA</legend>
                    <upload label="Anexar"
                            :dados-ajax="{tipo:'carta_oferta',curriculo_id: form.curriculo_id }"
                            :model="anexos"
                            :model-delete="anexosDel" :url="urlAnexoUpload"
                            :apenas-pdf="true"
                            @onprogresso="anexoUploadAndamento=true"
                            @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
                </fieldset>

                <button class="btn btn-primary btn-sm" v-if="anexos.length && !anexos[0].falhou && !anexoUploadAndamento" @click.prevent="salvar()">Salvar e enviar</button>
            </div>
        </div>

        <div class="row py-3 bg-white" style="border-top: 1px solid #cccccc">
            <div class="col-12">
                <div class="py-3 d-flex justify-content-center">
                    <img src="{{ asset('images/inova_maranhao.png') }}" alt=""
                         class=" " style="height: 60px">
                    <img src="{{ asset('images/fapema-logo.png') }}" alt=""
                         class=" " style="height: 60px">
                    <img src="https://bpse.com.br/img/logo_procem.png" alt=""
                         class=" " style="height: 90px">
                    <img src="https://bpse.com.br/img/selo_gptw.png" alt="" class=" "
                         style="height: 90px">
                </div>
            </div>
        </div>
    </div>


</div>
<script src="{{mix('js/app.js')}}"></script>
<script src="{{mix('js/funcoes.js')}}"></script>
<script src="{{mix('js/cartaoferta/app.js')}}"></script>
</body>
</html>
