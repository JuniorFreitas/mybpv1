@extends('errors.layout')

@section('title', 'Acesso negado')
@section('theme', 'theme-danger')
@section('badge', 'Acesso negado')
@section('icon', '403')
@section('heading', 'Você não tem permissão')
@section('message', 'Esta área exige autorização. Se você acredita que deveria ter acesso, fale com o administrador da sua empresa.')
@section('hint', 'Sua sessão pode estar válida, mas o perfil atual não inclui esta funcionalidade.')
@section('code', 'HTTP 403')

@section('actions')
    @include('errors.partials.actions-padrao')
@endsection
