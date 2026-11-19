@extends('layouts.sistema')
@section('title', 'Competências')
@section('content_header','Competências')
@section('content')
    <avaliacaotopico :tipo-pj="true"></avaliacaotopico>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/avaliacoes/avaliacaotopico/app.js'])
@endpush
