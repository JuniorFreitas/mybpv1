@extends('layouts.pdf')
@section('title', 'Carta Oferta')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@push('style')
    <style>
        .conteudo p {
            font-size: 12pt;
            line-height: 22pt;
            margin: 0 0 0.25cm 0;
        }

        .conteudo hr {
            width: 10cm;
            margin: 0.8cm auto 0.4cm auto;
            border: none;
            border-top: 1px solid #333;
        }
    </style>
@endpush
@section('conteudo')
    {!! $html !!}
@endsection
