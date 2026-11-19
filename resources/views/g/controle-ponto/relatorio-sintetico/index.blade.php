@extends('layouts.sistema')
@section('title', 'Relatório Sintético')
@section('content_header', "Relatorio Sintético")
@section('content')
    <relatorio-sintetico></relatorio-sintetico>
@stop
@push('js')
    @vite(['resources/js/g/controle-ponto/relatorio-sintetico/app.js'])
@endpush
