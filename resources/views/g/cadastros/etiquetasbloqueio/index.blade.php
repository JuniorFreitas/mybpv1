@extends('layouts.sistema')
@section('title', 'Etiquetas de Bloqueio')
@section('content_header', 'Etiquetas de Bloqueio')
@section('content')
    <p class="text-muted mb-3">
        Modelos de textos/ramal do PDF de etiqueta de bloqueio.
        @if((int) auth()->user()->empresa_id === 100)
            Nesta empresa você cadastra templates <strong>globais</strong>.
        @else
            Cadastre templates <strong>customizados</strong> da sua empresa.
        @endif
    </p>
    <etiquetas-bloqueio-cadastro
        :is-admin-global="{{ (int) auth()->user()->empresa_id === 100 ? 'true' : 'false' }}"
    ></etiquetas-bloqueio-cadastro>
@stop
@push('js')
    <script src="{{ mix('js/g/etiquetasbloqueio/app.js') }}"></script>
@endpush
