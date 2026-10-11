@extends('layouts.sistema')
@section('title', 'Importação de Treinamentos')
@section('content_header')
    <h4 class="text-default">Importação de Treinamentos</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
<importacao-treinamentos
    url-upload="{{ route('g.treinamentos.import.upload') }}"
    url-modelo="{{ route('g.treinamentos.import.modelo') }}"
    url-historico="{{ route('g.treinamentos.import.historico') }}"
    url-csv-base="{{ url('/g/treinamento/import/historico') }}/__ID__/csv/__TIPO__"
    :empresa-id="{{ auth()->user()->empresa_id ?? 0 }}"
></importacao-treinamentos>
@stop
@push('js')
    <script src="{{ mix('js/g/treinamentos/import/app.js') }}"></script>
@endpush
