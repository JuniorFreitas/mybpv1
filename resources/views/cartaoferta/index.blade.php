<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta Oferta</title>
    <link rel="preload" href="{{mix('js/app.js')}}" as="script">
    <link rel="preload" href="{{mix('js/funcoes.js')}}" as="script">
    @include('layouts.favicon')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @include('layouts.publico.estilos')
</head>
<body class="co-page">
@include('layouts.publico.cabecalho', ['marca' => $marca])
<div id="app" v-cloak>
    <preload v-if="preload" :msg="msgPreload"></preload>
    <input type="hidden" id="dd" value="{{ json_encode($payload) }}">

    <main class="co-main">
        {{-- Gate CPF + código --}}
        <acesso-por-codigo
            v-if="!dados.autenticado"
            :url-base="urlBase"
            :inicial="dados"
            titulo="Acesso à Carta Oferta"
            @autenticado="aoAutenticar"
        ></acesso-por-codigo>

        {{-- Conteúdo autenticado (dados mínimos) --}}
        <section v-else class="co-card">
            <h1 class="co-card__titulo">Carta Oferta</h1>
            <p class="co-card__texto">@{{ dados.empresa?.nome }}</p>

            <dl class="co-resumo">
                <dt>Nome</dt>
                <dd>@{{ dados.candidato?.nome }}</dd>
                <dt>Cargo</dt>
                <dd>@{{ dados.cargo }}</dd>
            </dl>

            <div v-if="dados.status === 'Aceito pelo RH'" class="co-alert co-alert--ok mt-0">
                <i class="fa fa-check-circle"></i> Carta Oferta aceita com sucesso!
            </div>
            <div v-else-if="dados.status === 'Aguardando RH'" class="co-alert co-alert--aviso mt-0">
                <i class="fa fa-hourglass-half"></i>
                Agradecemos o envio! Carta Oferta aguardando verificação pelo RH.
                Fique atento(a) ao seu e-mail.
            </div>
            <div v-else-if="dados.status === 'Recusado pelo RH'" class="co-alert co-alert--erro mt-0">
                <i class="fa fa-times-circle"></i> Carta Oferta recusada!
            </div>
            <div v-else-if="dados.status === 'Expirado'" class="co-alert co-alert--erro mt-0">
                <i class="fa fa-clock"></i> Prazo expirado!
            </div>
            <template v-else-if="dados.status === 'Pendente Anexo'">
                <div class="co-alert co-alert--info mt-0 mb-3">
                    Anexe a carta oferta assinada em PDF para continuar.
                </div>
                <fieldset>
                    <legend>Anexar CARTA OFERTA</legend>
                    <upload
                        label="Anexar"
                        :dados-ajax="{ tipo: 'carta_oferta', curriculo_id: dados.curriculo_id }"
                        :model="anexos"
                        :model-delete="anexosDel"
                        :url="urlAnexoUpload"
                        :apenas-pdf="true"
                        @onprogresso="anexoUploadAndamento = true"
                        @onfinalizado="anexoUploadAndamento = false"
                        :quantidade="1"
                        :multi="false"
                    ></upload>
                </fieldset>
                <button
                    type="button"
                    class="co-btn"
                    v-if="anexos.length && !anexos[0].falhou && !anexoUploadAndamento"
                    @click.prevent="salvar"
                >Salvar e enviar</button>
            </template>

            <div v-if="erro" class="co-alert co-alert--erro" role="alert">@{{ erro }}</div>
        </section>
    </main>

    @include('layouts.publico.rodape')
</div>
@include('layouts.publico.carregando')
<script src="{{mix('js/app.js')}}"></script>
<script src="{{mix('js/funcoes.js')}}"></script>
<script src="{{mix('js/cartaoferta/app.js')}}"></script>
</body>
</html>
