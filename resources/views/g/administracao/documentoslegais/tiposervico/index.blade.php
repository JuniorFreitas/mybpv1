@extends('layouts.sistema')
@section('title', 'Tipo Serviço')
@section('content_header','Tipo Serviço')
@section('content')
    <tiposervico></tiposervico>
@stop
@push('js')
    <script src="{{mix('js/g/documentoslegais/tiposervico/app.js')}}"></script>
@endpush
