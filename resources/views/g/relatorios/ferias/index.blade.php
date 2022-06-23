@extends('layouts.sistema')
@section('title', 'Vencimentos de Férias')
@section('content_header', 'Vencimentos de Férias')
@section('content')
    <ferias></ferias>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/ferias/app.js')}}"></script>
@endpush
