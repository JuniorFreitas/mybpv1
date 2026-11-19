@extends('layouts.sistema')
@section('title', 'Padrões de Treinamento')
@section('content_header', 'Padrões de Treinamento')
@section('content')
    <p class="text-muted mb-3">
        Monte o conjunto de treinamentos exigidos para cada área ou unidade,
        e indique se gera carteira e etiqueta de bloqueio. Depois, basta escolher o padrão na admissão.
    </p>
    <padroes-treinamento-cadastro></padroes-treinamento-cadastro>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/padroestreinamento/app.js'])
@endpush
