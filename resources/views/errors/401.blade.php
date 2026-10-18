@extends('errors.layout')

@section('title', 'Não autenticado')
@section('theme', 'theme-warn')
@section('badge', 'Login necessário')
@section('icon', '401')
@section('heading', 'Você precisa entrar')
@section('message', 'Esta área é restrita a usuários autenticados. Faça login para continuar.')
@section('hint', 'Se você já estava logado, a sessão pode ter expirado.')
@section('code', 'HTTP 401')

@section('actions')
    <a class="btn btn-primary" href="{{ url('/g/login') }}">Ir para o login</a>
    <a class="btn btn-secondary" href="{{ url('/') }}">Página inicial</a>
@endsection
