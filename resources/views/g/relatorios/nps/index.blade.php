@extends('layouts.sistema')
@section('title', 'Relatório NPS')
@section('content_header', 'Gerenciamento e Resultados NPS')
@section('content')
    <nps-relatorio></nps-relatorio>
@stop
@push('js')
    <script src="{{ mix('js/g/relatorios/nps/app.js') }}"></script>
@endpush
