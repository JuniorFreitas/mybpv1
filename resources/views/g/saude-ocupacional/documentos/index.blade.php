@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — Documentos SST')
@section('content_header','Saúde Ocupacional — Documentos SST')
@section('content')
    <documentos-sst></documentos-sst>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/documentos/app.js')}}"></script>
@endpush
