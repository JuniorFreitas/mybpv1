<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Documentos de Pré-admissão</title>
    <link rel="preload" href="{{mix('js/app.js')}}" as="script">
    <link rel="preload" href="{{mix('js/funcoes.js')}}" as="script">
    @include('layouts.favicon')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @include('layouts.publico.estilos')
    <style>
        .co-card--largo { max-width: 860px; }
    </style>
</head>
<body class="co-page">
@include('layouts.publico.cabecalho', ['marca' => $marca])
<div id="app" v-cloak>
    <input type="hidden" id="dd" value="{{ json_encode($payload) }}">

    <main class="co-main">
        {{-- Gate CPF + código --}}
        <acesso-por-codigo
            v-if="!dados.autenticado"
            :url-base="urlBase"
            :extras="extras"
            :inicial="dados"
            titulo="Documentos de Pré-admissão"
            @autenticado="aoAutenticar"
        ></acesso-por-codigo>

        <section v-else class="co-card co-card--largo">
            <h1 class="co-card__titulo">Documentos de Pré-admissão</h1>
            <p class="co-card__texto">Confira seus dados e anexe os documentos solicitados.</p>
            <documento :curriculo="dados.curriculo" :apelido="apelido"></documento>
        </section>
    </main>

    @include('layouts.publico.rodape')
</div>
@include('layouts.publico.carregando')
<script src="{{mix('js/app.js')}}"></script>
<script src="{{mix('js/funcoes.js')}}"></script>
<script src="{{mix('js/documentos/app.js')}}"></script>
</body>
</html>
