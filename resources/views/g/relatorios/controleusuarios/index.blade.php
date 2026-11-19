@extends('layouts.sistema')
@section('title', 'Controle de Usuário')
@section('content_header', 'Controle de Usuário')
@section('content')
    <controle-usuarios></controle-usuarios>
@stop
@push('js')
    @vite(['resources/js/g/relatorios/controleusuarios/app.js'])
@endpush
