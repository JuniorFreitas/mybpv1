@extends('layouts.sistema')
@section('title', 'Segmentos de Treinamento')
@section('content_header', 'Segmentos de Treinamento')
@section('content')
    <p class="text-muted mb-3">Cadastro <strong>global</strong> de segmentos (ALUMAR, VALE, Hidro, etc.) — sem vínculo com cliente ou empresa. Aqui são definidos os padrões para carteira e etiqueta de bloqueio. Definir quais segmentos cada empresa utiliza é feito no cadastro de <strong>Cliente</strong>; na admissão define-se o padrão de cada funcionário.</p>
    <segmentos-treinamento-cadastro></segmentos-treinamento-cadastro>
@stop
@push('js')
    <script src="{{ mix('js/g/segmentostreinamento/app.js') }}"></script>
@endpush
