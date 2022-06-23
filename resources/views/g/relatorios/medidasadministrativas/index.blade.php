@extends('layouts.sistema')
@section('title', 'Medidas Administrativas')
@section('content_header', 'Medidas Administrativas')
@section('content')
    <medidas-administrativas></medidas-administrativas>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/medidasadministrativas/app.js')}}"></script>
@endpush
