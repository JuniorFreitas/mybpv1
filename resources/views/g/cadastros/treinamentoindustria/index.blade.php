@extends('layouts.sistema')
@section('title', 'Lista de Treinamentos')
@section('content_header','Lista de Treinamentos')
@section('content')
    <treinamento-industria></treinamento-industria>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/treinamentoindustria/app.js'])
@endpush
