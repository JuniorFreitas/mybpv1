@extends('layouts.sistema')
@section('title', 'CLOUD')
@section('content_header')
    <h4 class="text-default">CLOUD - {{ mb_strtoupper($cloud->nome) }}</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

{{--    <button class="btn btn-success" @click="atualizar(1)">Atualizar</button>--}}

    <cloud :cloud="{{ $cloud->id }}" :item-busca="itemAtual" ref="cloud" @abri-pasta="openFolder"></cloud>

@stop
@push('js')
    <script src="{{mix('js/g/cloud/app.js')}}"></script>
@endpush
