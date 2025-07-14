@extends('pdf.treinamento.carteira.layout_carteira')
@section('titulo', 'Carteiras')
@section('conteudo')

    @if($tipo == 'treinamento')
        @include('pdf.treinamento.carteira.cart_treinamento')
    @endif

    @if($tipo == 'bloqueio')
        @include('pdf.treinamento.carteira.cart_bloqueio')
    @endif

    @if($tipo == 'treinamento_bloqueio')
        @include('pdf.treinamento.carteira.cart_treinamento')
        <div style="clear: both; margin: 0; padding: 0;"></div>
        <div style="page-break-after: always; margin: 0; padding: 0;"></div>
        @include('pdf.treinamento.carteira.cart_bloqueio')
    @endif

@endsection
