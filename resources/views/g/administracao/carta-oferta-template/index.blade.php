@extends('layouts.sistema')
@section('title', 'Template Carta Oferta')
@section('content_header', 'Template Carta Oferta')
@section('content')
    <carta-oferta-template></carta-oferta-template>
@stop
@push('js')
    <script src="{{mix('js/g/administracao/carta-oferta-template/app.js')}}"></script>
@endpush
