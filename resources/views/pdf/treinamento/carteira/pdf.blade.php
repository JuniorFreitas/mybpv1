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
        @include('pdf.treinamento.carteira.cart_bloqueio', ['aposTreinamento' => true])
    @endif

@endsection
