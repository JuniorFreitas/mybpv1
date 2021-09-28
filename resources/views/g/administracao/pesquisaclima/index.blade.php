@extends('layouts.sistema')
@section('title', 'Pesquisa de Clima')
@section('content_header')
    <h4 class="text-default titulo">PESQUISA DE CLIMA</h4>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <pesquisa-clima></pesquisa-clima>
        </div>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/pesquisaclima/app.js')}}"></script>
@endpush
