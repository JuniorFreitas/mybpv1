@extends('layouts.sistema')
@section('title', 'Tópico Avaliação')
@section('content_header','Tópico Avaliação')
@section('content')
    <avaliacaotopico></avaliacaotopico>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliacaotopico/app.js')}}"></script>
@endpush
