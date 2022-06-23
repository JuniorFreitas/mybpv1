@extends('layouts.sistema')
@section('title', 'Treinamentos Vencimentos')
@section('content_header', 'Treinamentos Vencimentos')
@section('content')
    <treinamento></treinamento>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/treinamento/app.js')}}"></script>
@endpush
