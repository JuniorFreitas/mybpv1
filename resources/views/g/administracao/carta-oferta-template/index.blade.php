@extends('layouts.sistema')
@section('title', 'Template Carta Oferta')
@section('content_header')
    <h4 class="text-default">Template Carta Oferta</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <carta-oferta-template></carta-oferta-template>
@stop
@push('js')
    <script src="{{ mix('js/g/administracao/carta-oferta-template/app.js') }}"></script>
@endpush
