@extends('layouts.sistema')
@section('title', 'Relatório de Aniversariantes')
@section('content_header', "Relatório de Aniversariantes")
@section('content')
    <aniversariantes></aniversariantes>
@stop
@push('js')
    @vite(['resources/js/g/relatorios/aniversariantes/app.js'])
@endpush
