@extends('layouts.sistema')
@section('title', 'Documentos Legais - SSMA')
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
    <h4 class="text-default">Documentos Legais - SSMA</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <documentossma></documentossma>
@stop

@push('js')
    <script src="{{mix('js/g/documentoslegais/documentossma/app.js')}}"></script>
@endpush

