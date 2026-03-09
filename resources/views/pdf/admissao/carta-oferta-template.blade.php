@extends('layouts.pdf')
@section('title', 'Carta Oferta')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    {!! $html !!}
@endsection
