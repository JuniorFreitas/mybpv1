@extends('layouts.sistema')
@section('title', 'Tipo Documento')
@section('content_header','Tipo Documento')
@section('content')
    <tipodocumento></tipodocumento>
@stop
@push('js')
    <script src="{{mix('js/g/documentoslegais/tipodocumento/app.js')}}"></script>
@endpush
