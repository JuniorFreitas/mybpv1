@extends('layouts.sistema')
@section('title', 'Forma Contrato')
@section('content_header','Forma Contrato')
@section('content')
    <formacontrato></formacontrato>
@stop
@push('js')
    <script src="{{mix('js/g/documentoslegais/formacontrato/app.js')}}"></script>
@endpush
