@extends('layouts.sistema')
@section('title', 'Treinamento Sgi')
@section('content_header','Treinamento Sgi')
@section('content')
    <treinamento-sgi></treinamento-sgi>
@stop
@push('js')
    <script src="{{mix('js/g/treinamentosgi/app.js')}}"></script>
@endpush
