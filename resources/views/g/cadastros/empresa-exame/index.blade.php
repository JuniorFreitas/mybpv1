@extends('layouts.sistema')
@section('title', 'Empresa Exame')
@section('content_header','Empresa Exame')
@section('content')
    <empresa-exame></empresa-exame>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/empresa-exame/app.js'])
@endpush
