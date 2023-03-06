@extends('layouts.sistema')
@section('title', 'Relatorio Sintetico')
@section('content_header', "Relatorio Sintetico")
@section('content')
    <relatorio-sintetico></relatorio-sintetico>
@stop
@push('js')
    <script src="{{mix('js/g/controle-ponto/relatorio-sintetico/app.js')}}"></script>
@endpush
