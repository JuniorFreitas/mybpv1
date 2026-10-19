@extends('layouts.sistema')
@section('title', 'Padrões de Treinamento')
@section('content_header', 'Padrões de Treinamento')
@section('content')
    <p class="text-muted mb-3">
        Agrupe treinamentos da sua empresa, escolha se usa carteira e/ou etiqueta de bloqueio e defina
        <strong>onde vale</strong> (Matriz e/ou Filiais). Spec: <code>docs/PADRAO_TREINAMENTO.md</code>.
    </p>
    <padroes-treinamento-cadastro></padroes-treinamento-cadastro>
@stop
@push('js')
    <script src="{{ mix('js/g/padroestreinamento/app.js') }}"></script>
@endpush
