@extends('layouts.sistema')
@section('title', 'Tipo Avaliação')
@section('content_header','Tipo Avaliação')
@section('content')
    <avaliacaotipo></avaliacaotipo>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliacaotipo/app.js')}}"></script>
@endpush
