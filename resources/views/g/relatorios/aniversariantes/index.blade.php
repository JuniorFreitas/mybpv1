@extends('layouts.sistema')
@section('title', 'Relatório de Aniversariantes')
@section('content_header', "Relatório de Aniversariantes")
@section('content')
    <aniversariantes></aniversariantes>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/aniversariantes/app.js')}}"></script>
@endpush
