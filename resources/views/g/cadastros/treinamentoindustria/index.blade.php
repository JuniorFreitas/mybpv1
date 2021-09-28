@extends('layouts.sistema')
@section('title', 'Treinamento Indústria')
@section('content_header','Treinamento Indústria')
@section('content')
    <treinamento-industria></treinamento-industria>
@stop
@push('js')
    <script src="{{mix('js/g/treinamentoindustria/app.js')}}"></script>
@endpush
