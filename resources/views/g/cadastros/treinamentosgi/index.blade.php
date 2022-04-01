@extends('layouts.sistema')
@section('title', 'Treinamento')
@section('content_header','Treinamento')
@section('content')
    <treinamento-sgi></treinamento-sgi>
@stop
@push('js')
    <script src="{{mix('js/g/treinamentosgi/app.js')}}"></script>
@endpush
