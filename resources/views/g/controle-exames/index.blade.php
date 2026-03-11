@extends('layouts.sistema')
@section('title', 'CONTROLE DE EXAMES')
@section('content_header', 'CONTROLE DE EXAMES')
@section('content')
<controle-exames></controle-exames>
@stop
@push('js')
    <script src="{{ mix('js/g/controle-exames/app.js') }}"></script>
@endpush
