@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — Painel')
@section('content_header','Saúde Ocupacional — Painel')
@section('content')
    <painel-saude-ocupacional></painel-saude-ocupacional>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/painel/app.js')}}"></script>
@endpush
