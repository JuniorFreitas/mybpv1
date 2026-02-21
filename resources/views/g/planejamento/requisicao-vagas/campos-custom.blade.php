@extends('layouts.sistema')
@section('title', 'Campos personalizados - Requisição de Vagas')
@section('content_header', 'Campos personalizados - Requisição de Vagas')
@section('content')
<requisicao-vaga-campos-custom></requisicao-vaga-campos-custom>
@stop
@push('js')
<script src="{{ mix('js/g/planejamento/requisicao-vagas/campos-custom-app.js') }}"></script>
@endpush
