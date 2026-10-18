@extends('errors.layout')

@section('title', 'Em manutenção')
@section('theme', 'theme-info')
@section('badge', 'Manutenção')
@section('icon', '503')
@section('heading', 'Sistema em manutenção')
@section('message', 'Estamos realizando uma manutenção para melhorar o MyBPIN. Voltamos em breve.')
@section('hint', 'Nenhuma ação é necessária agora. Tente acessar novamente mais tarde.')
@section('code', 'HTTP 503')

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">Tentar novamente</a>
    <a class="btn btn-secondary" href="{{ url('/g/login') }}">Ir para o login</a>
@endsection
