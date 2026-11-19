@extends('layouts.sistema')
@section('title', 'Tipo Serviço')
@section('content_header','Tipo Serviço')
@section('content')
    <tiposervico></tiposervico>
@stop
@push('js')
    @vite(['resources/js/g/administracao/documentoslegais/tiposervico/app.js'])
@endpush
