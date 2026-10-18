@extends('errors.layout')

@section('title', 'Página não encontrada')
@section('badge', 'Página não encontrada')
@section('icon', '404')
@section('heading', 'Não encontramos esta página')
@section('message', 'O endereço pode estar incorreto, ou a página foi removida ou movida. Confira o link e tente novamente.')
@section('hint', 'Se você veio de um e-mail ou favorito, o link pode estar desatualizado.')
@section('code', 'HTTP 404')

@section('actions')
    @include('errors.partials.actions-padrao')
@endsection
