@extends('layouts.sistema')
@section('title', 'CONTROLE DE EXAMES')
@section('content_header', 'CONTROLE DE EXAMES')
@section('content')
<controle-exames></controle-exames>
@stop
@push('js')
    @vite(['resources/js/g/controle-exames/app.js'])
@endpush
