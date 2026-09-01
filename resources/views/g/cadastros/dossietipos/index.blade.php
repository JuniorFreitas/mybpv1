@extends('layouts.sistema')
@section('title', 'Tipos de Dossiê')
@section('content_header','Tipos de Dossiê')
@section('content')
    <dossie-tipos></dossie-tipos>
@stop
@push('js')
    <script src="{{mix('js/g/dossietipos/app.js')}}"></script>
@endpush
