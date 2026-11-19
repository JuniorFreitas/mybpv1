@extends('layouts.sistema')
@section('title', 'Treinamentos Vencimentos')
@section('content_header', 'Treinamentos Vencimentos')
@section('content')
    <treinamento></treinamento>
@stop
@push('js')
    @vite(['resources/js/g/relatorios/treinamento/app.js'])
@endpush
