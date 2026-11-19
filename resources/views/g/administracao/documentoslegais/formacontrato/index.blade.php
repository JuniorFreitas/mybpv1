@extends('layouts.sistema')
@section('title', 'Forma Contrato')
@section('content_header','Forma Contrato')
@section('content')
    <formacontrato></formacontrato>
@stop
@push('js')
    @vite(['resources/js/g/administracao/documentoslegais/formacontrato/app.js'])
@endpush
