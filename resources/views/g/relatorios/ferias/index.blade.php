@extends('layouts.sistema')
@section('title', 'Relatorio de Férias')
@section('content_header', 'Relatorio de Férias')
@section('content')
    <ferias></ferias>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/ferias/app.js')}}"></script>
@endpush
