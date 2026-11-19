@extends('layouts.sistema')
@section('title', 'Minhas Avaliações')
@section('content_header','Minhas Avaliações')
@section('content')
    <avaliar :tipo_pj="true"></avaliar>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/avaliacoes/avaliar/app.js'])
@endpush
