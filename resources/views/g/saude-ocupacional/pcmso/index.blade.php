@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — PCMSO')
@section('content_header','Saúde Ocupacional — PCMSO')
@section('content')
    <pcmso-programas></pcmso-programas>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/pcmso/app.js')}}"></script>
@endpush
