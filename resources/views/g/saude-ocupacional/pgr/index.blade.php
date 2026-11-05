@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — PGR: riscos por cargo')
@section('content_header','Saúde Ocupacional — PGR: riscos por cargo')
@section('content')
    <pgr-riscos></pgr-riscos>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/pgr/app.js')}}"></script>
@endpush
