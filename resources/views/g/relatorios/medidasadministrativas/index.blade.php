@extends('layouts.sistema')
@section('title', 'Medidas Administrativas')
@section('content_header', 'Medidas Administrativas')
@section('content')
    <medidas-administrativas></medidas-administrativas>
@stop
@push('js')
    @vite(['resources/js/g/relatorios/medidasadministrativas/app.js'])
@endpush
