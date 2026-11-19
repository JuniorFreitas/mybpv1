@extends('layouts.sistema')
@section('title', 'Departamento')
@section('content_header','Departamento')
@section('content')
    <departamento></departamento>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/departamento/app.js'])
@endpush
