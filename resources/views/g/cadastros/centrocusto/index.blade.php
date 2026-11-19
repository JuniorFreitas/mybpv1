@extends('layouts.sistema')
@section('title', 'Centro de Custos')
@section('content_header','Centro de Custos')
@section('content')
    <centro-custo></centro-custo>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/centrocusto/app.js'])
@endpush
