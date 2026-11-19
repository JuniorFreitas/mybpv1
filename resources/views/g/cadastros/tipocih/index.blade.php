@extends('layouts.sistema')
@section('title', 'Tipo CIH')
@section('content_header','Tipo CIH')
@section('content')
    <tipo-cih></tipo-cih>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/tipocih/app.js'])
@endpush
