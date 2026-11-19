@extends('layouts.sistema')
@section('title', 'Benefícios')
@section('content_header','Benefícios')
@section('content')
    <beneficio></beneficio>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/beneficio/app.js'])
@endpush
