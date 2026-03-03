@extends('layouts.sistema')
@section('title', 'Documentos para Assinatura')
@section('content_header')
    <h4 class="text-default">Documentos para Assinatura</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <documento-assinatura :documento-id-inicial="{{ json_encode(request('id')) }}"></documento-assinatura>
@stop
@push('js')
    <script src="{{ mix('js/g/administracao/documento-assinatura/app.js') }}"></script>
@endpush
