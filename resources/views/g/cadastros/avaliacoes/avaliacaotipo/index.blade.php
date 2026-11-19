@extends('layouts.sistema')
@section('title', 'Tipo Avaliação')
@section('content_header','Tipo Avaliação')
@section('content')
    <avaliacaotipo></avaliacaotipo>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/avaliacoes/avaliacaotipo/app.js'])
@endpush
