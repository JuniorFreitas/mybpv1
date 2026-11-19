@extends('layouts.sistema')
@section('title', 'Intermitente')
@section('content_header','Intermitente')
@section('content')
    <intermitente></intermitente>
@stop
@push('js')
    @vite(['resources/js/g/admissao/apontamento/intermitente/app.js'])
@endpush
