@extends('layouts.sistema')
@section('title', 'Planejamento Diário')
@section('content_header', 'Planejamento Diário')
@section('content')
    <planejamento-diario></planejamento-diario>
@stop
@push('js')
    @vite(['resources/js/g/administracao/planejamentodiario/app.js'])
@endpush
