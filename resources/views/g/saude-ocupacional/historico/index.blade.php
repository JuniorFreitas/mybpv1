@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — Histórico de ASO')
@section('content_header','Saúde Ocupacional — Histórico de ASO')
@section('content')
    <historico-aso></historico-aso>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/historico/app.js')}}"></script>
@endpush
