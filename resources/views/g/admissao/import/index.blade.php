@extends('layouts.sistema')
@section('title', 'Importação de Admissões')
@section('content_header')
    <h4 class="text-default">Importação de Admissões</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
<importacao-admissoes
    url-upload="{{ route('g.admissao.admissao.import.upload') }}"
    :empresa-id="{{ auth()->user()->empresa_id ?? 0 }}"
></importacao-admissoes>
@stop
@push('js')
    <script src="{{ mix('js/g/admissao/import/app.js') }}"></script>
@endpush
