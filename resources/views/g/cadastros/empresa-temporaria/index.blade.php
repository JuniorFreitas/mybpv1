@extends('layouts.sistema')
@section('title', 'Empresa Temporaria')
@section('content_header','Empresa Temporaria')
@section('content')
    <empresa-temporaria></empresa-temporaria>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/empresa-temporaria/app.js'])
@endpush
