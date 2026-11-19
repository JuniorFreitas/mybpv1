@extends('layouts.sistema')
@section('title', 'Tipo Avaliador')
@section('content_header','Tipo Avaliador')
@section('content')
    <avaliadortipo></avaliadortipo>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/avaliacoes/avaliadortipo/app.js'])
@endpush
