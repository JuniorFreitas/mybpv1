@extends('layouts.sistema')
@section('title', 'Competências')
@section('content_header','Competências')
@section('content')
    <avaliacaotopico :tipo-pj="true"></avaliacaotopico>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliacaotopico/app.js')}}"></script>
@endpush
