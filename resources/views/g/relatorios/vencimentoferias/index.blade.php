@extends('layouts.sistema')
@section('title', 'Relatorio de Vencimento de Férias')
@section('content_header', 'Relatorio de Vencimento de Férias')
@section('content')
    <vencimento-ferias></vencimento-ferias>
@stop
@push('js')
    @vite(['resources/js/g/relatorios/vencimentoferias/app.js'])
@endpush
