@extends('layouts.sistema')
@section('title', 'Certificados')
@section('content_header','Certificados')
@section('content')
    <treinamento-sgi></treinamento-sgi>
@stop
@push('js')
    <script src="{{mix('js/g/treinamentosgi/app.js')}}"></script>
@endpush
