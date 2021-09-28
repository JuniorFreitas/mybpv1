@extends('layouts.sistema')
@section('title', 'Empresa Treinamento')
@section('content_header','Empresa Treinamento')
@section('content')
    <empresa-treinamento></empresa-treinamento>
@stop
@push('js')
    <script src="{{mix('js/g/empresatreinamento/app.js')}}"></script>
@endpush
