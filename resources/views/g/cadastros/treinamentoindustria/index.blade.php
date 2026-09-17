@extends('layouts.sistema')
@section('title', 'Lista de Treinamentos')
@section('content_header','Lista de Treinamentos')
@section('content')
    <treinamento-industria></treinamento-industria>
@stop
@push('js')
    <script src="{{mix('js/g/treinamentoindustria/app.js')}}"></script>
@endpush
