@extends('layouts.sistema')
@section('title', 'Minhas Avaliações')
@section('content_header','Minhas Avaliações')
@section('content')
    <avaliar :tipo_pj="true"></avaliar>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliar/app.js')}}"></script>
@endpush
