@extends('layouts.sistema')
@section('title', 'Relatorio de Férias')
@section('content_header', 'Relatorio de Férias')
@section('content')
    <ferias></ferias>
@stop
@push('js')
    @vite(['resources/js/g/relatorios/ferias/app.js'])
@endpush
