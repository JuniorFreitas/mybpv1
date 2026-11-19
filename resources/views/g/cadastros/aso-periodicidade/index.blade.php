@extends('layouts.sistema')
@section('title', 'Periodicidade ASO')
@section('content_header','Periodicidade ASO')
@section('content')
    <aso-periodicidade></aso-periodicidade>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/aso-periodicidade/app.js'])
@endpush
