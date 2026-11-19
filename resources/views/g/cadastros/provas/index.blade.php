@extends('layouts.sistema')
@section('title', 'Provas')
@section('content_header','Provas')
@section('content')
    <prova></prova>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/provas/app.js'])
@endpush
