@extends('layouts.sistema')
@section('title', 'Tipo Documento')
@section('content_header','Tipo Documento')
@section('content')
    <tipodocumento></tipodocumento>
@stop
@push('js')
    @vite(['resources/js/g/administracao/documentoslegais/tipodocumento/app.js'])
@endpush
