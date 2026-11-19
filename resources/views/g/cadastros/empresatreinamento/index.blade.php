@extends('layouts.sistema')
@section('title', 'Empresa Treinamento')
@section('content_header','Empresa Treinamento')
@section('content')
    <empresa-treinamento></empresa-treinamento>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/empresatreinamento/app.js'])
@endpush
