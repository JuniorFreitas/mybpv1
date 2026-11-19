{{--@extends('layouts.sistema')--}}
{{--@section('title', 'Ocorrências')--}}
{{--@section('content_header', 'Ocorrências')--}}
@section('content')
    <perfil></perfil>
@stop
@push('js')
    @vite(['resources/js/g/perfil/app.js'])
@endpush
