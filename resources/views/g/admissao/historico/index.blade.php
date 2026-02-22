@extends('layouts.sistema')
@section('title', 'Histórico')
@section('content_header')
    <h4 class="text-default">Histórico</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
<historico url-atualizar="{{ route('g.historico.atualizar') }}"
           :url-gerar-link-avaliacao-experiencia="'{{ url('g/historico/avaliacao-experiencia') }}'"></historico>
@stop
@push('js')
    <script src="{{ mix('js/g/admissao/historico/app.js') }}"></script>
@endpush
