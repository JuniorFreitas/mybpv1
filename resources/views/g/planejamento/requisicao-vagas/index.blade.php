@extends('layouts.sistema')
@section('title', 'Planejamento - Requisição de Vagas')
@section('content_header', 'Planejamento - Requisição de Vagas')
@section('content')
<requisicao-vaga url-atualizar="{{ route('g.requisicao_vagas.atualizar') }}"></requisicao-vaga>
@stop
@push('js')
<script src="{{ mix('js/g/planejamento/requisicao-vagas/requisicao-vaga-app.js') }}"></script>
@endpush
