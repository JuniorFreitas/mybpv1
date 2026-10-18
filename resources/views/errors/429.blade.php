@extends('errors.layout')

@section('title', 'Muitas tentativas')
@section('theme', 'theme-warn')
@section('badge', 'Limite temporário')
@section('icon', '429')
@section('heading', 'Muitas tentativas em pouco tempo')
@section('message', 'Por segurança, bloqueamos temporariamente novas solicitações. Tente novamente mais tarde.')
@section('hint', 'Se o problema continuar, aguarde alguns minutos antes de uma nova tentativa.')
@section('code', 'HTTP 429')

@section('actions')
    @include('errors.partials.actions-padrao')
@endsection
