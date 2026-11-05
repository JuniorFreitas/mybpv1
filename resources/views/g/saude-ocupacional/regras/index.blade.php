@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — Regras da empresa')
@section('content_header','Saúde Ocupacional — Regras da empresa')
@section('content')
    <regras-empresa></regras-empresa>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/regras/app.js')}}"></script>
@endpush
