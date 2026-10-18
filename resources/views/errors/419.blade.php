@extends('errors.layout')

@section('title', 'Sessão expirada')
@section('theme', 'theme-warn')
@section('badge', 'Sessão expirada')
@section('icon', '419')
@section('heading', 'Sua sessão expirou')
@section('message', 'Por segurança, o formulário ficou inválido. Volte à tela anterior, recarregue e tente novamente.')
@section('hint', 'Isso costuma acontecer quando a tela fica aberta por muito tempo sem uso.')
@section('code', 'HTTP 419')

@section('actions')
    <a class="btn btn-primary" href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/g/login') }}">
        Voltar e tentar novamente
    </a>
    <a class="btn btn-secondary" href="{{ url('/g/login') }}">Ir para o login</a>
@endsection
