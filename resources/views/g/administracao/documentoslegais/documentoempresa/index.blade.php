@extends('layouts.sistema')
@section('title', 'Documentos Legais - Empresa')
@push('css')
    <style type="text/css">
        .card-header {
            background-color: #174257 !important;
            border-radius: 10px;
        }

        .btn-link {
            font-weight: 400 !important;
            color: #ffffff !important;
        }
    </style>
@endpush
@section('content_header')
    <h4 class="text-default">Documentos Legais - Empresa</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <documentoempresa></documentoempresa>
@stop

@push('js')
    <script src="{{mix('js/g/documentoslegais/documentoempresa/app.js')}}"></script>
@endpush

