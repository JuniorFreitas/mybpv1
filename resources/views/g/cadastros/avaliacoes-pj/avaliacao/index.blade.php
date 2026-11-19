@extends('layouts.sistema')
@section('title', 'Avaliação')
@section('content_header','Avaliação')
@section('content')
    <avaliacao :tipo-pj="true"></avaliacao>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/avaliacoes/avaliacao/app.js'])
@endpush
