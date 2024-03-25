@extends('layouts.sistema')
@section('title', 'Tipo Avaliador')
@section('content_header','Tipo Avaliador')
@section('content')
    <avaliadortipo></avaliadortipo>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliadortipo/app.js')}}"></script>
@endpush
