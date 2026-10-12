@extends('assinatura.layout')

@section('title', 'Documentos')
@section('selo', 'Documentos')

@push('css')
    @include('layouts.favicon')
    @include('layouts.publico.estilos')
    @include('layouts.publico.tema-assinatura')
@endpush

@section('content')
    <div id="app" v-cloak>
        <input type="hidden" id="dd" value="{{ json_encode($payload) }}">

        {{-- Gate CPF + código --}}
        <acesso-por-codigo
            v-if="!dados.autenticado"
            :url-base="urlBase"
            :extras="extras"
            :inicial="dados"
            titulo="Validação de segurança"
            @autenticado="aoAutenticar"
        >
            <template #origem>
                <div class="assin-origin mb-3">
                    A empresa <strong>{{ $marca['nome'] ?: 'contratante' }}</strong> solicitou o envio dos seus
                    <strong>documentos</strong>.
                </div>
            </template>
        </acesso-por-codigo>

        <div v-else class="card assin-card">
            <div class="card-header">
                <h5 class="mb-0">Documentos</h5>
                <p class="assin-subtitle">Confira seus dados e anexe os documentos solicitados</p>
            </div>
            <div class="card-body">
                <div class="assin-origin mb-3">
                    A empresa <strong>{{ $marca['nome'] ?: 'contratante' }}</strong> solicitou o envio dos seus
                    <strong>documentos</strong>.
                </div>
                <div class="mb-3">
                    <span class="assin-chip">@{{ dados.curriculo?.nome }}</span>
                </div>
                <documento :curriculo="dados.curriculo" :apelido="apelido" :termo="dados.termo"></documento>
            </div>
        </div>
    </div>
    @include('layouts.publico.carregando')
@endsection

@push('scripts')
    <script src="{{ mix('js/documentos/app.js') }}"></script>
@endpush
