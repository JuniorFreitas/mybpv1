@extends('layouts.sistema')
@section('title', 'Ocorrências')
@section('content_header', 'Ocorrências')
@section('content')
    <ocorrencia></ocorrencia>
@stop
@push('js')
    @vite(['resources/js/g/ocorrencia/app.js'])
@endpush
