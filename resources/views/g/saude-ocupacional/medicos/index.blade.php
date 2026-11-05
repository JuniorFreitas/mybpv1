@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — Médicos responsáveis')
@section('content_header','Saúde Ocupacional — Médicos responsáveis')
@section('content')
    <medicos-responsaveis></medicos-responsaveis>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/medicos/app.js')}}"></script>
@endpush
