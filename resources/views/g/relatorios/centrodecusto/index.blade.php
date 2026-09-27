@extends('layouts.sistema')
@section('title', 'Centros de Custo')
@section('content_header')
    <h4 class="text-default">Centros de Custo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <centro-custo-relatorio></centro-custo-relatorio>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/centrodecusto/app.js')}}"></script>
@endpush
