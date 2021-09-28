@extends('layouts.sistema')
@section('title', 'Controle de Usuário')
@section('content_header', 'Controle de Usuário')
@section('content')
    <controle-usuarios></controle-usuarios>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/controleusuarios/app.js')}}"></script>
@endpush
