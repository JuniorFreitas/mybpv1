@extends('layouts.sistema')
@section('title', 'Efetivo')
@section('content_header')
    <h4 class="text-default">Efetivo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <efetivo-relatorio></efetivo-relatorio>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/efetivo/app.js')}}"></script>
@endpush
