@extends('layouts.sistema')
@section('title', 'Certificados')
@section('content_header','Certificados')
@section('content')
    <treinamento-sgi></treinamento-sgi>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/treinamentosgi/app.js'])
@endpush
