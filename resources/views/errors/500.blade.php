@extends('errors.layout')

@section('title', 'Erro interno')
@section('theme', 'theme-danger')
@section('badge', 'Erro interno')
@section('icon', '500')
@section('heading', 'Algo deu errado')
@section('message', 'Ocorreu um erro inesperado ao processar sua solicitação. Nossa equipe pode já ter sido notificada.')
@section('hint', 'Tente novamente em instantes. Se o problema persistir, entre em contato com o suporte.')
@section('code', 'HTTP 500')

@section('actions')
    @include('errors.partials.actions-padrao')
@endsection
