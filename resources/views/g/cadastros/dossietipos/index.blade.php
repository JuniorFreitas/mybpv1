@extends('layouts.sistema')
@section('title', 'Tipos de Dossiê')
@section('content_header','Tipos de Dossiê')
@section('content')
    <dossie-tipos></dossie-tipos>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/dossietipos/app.js'])
@endpush
