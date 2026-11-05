@extends('layouts.sistema')
@section('title', 'Saúde Ocupacional — Conformidade de ASO')
@section('content_header','Saúde Ocupacional — Conformidade de ASO')
@section('content')
    <conformidade-aso></conformidade-aso>
@stop
@push('js')
    <script src="{{mix('js/g/saude-ocupacional/conformidade/app.js')}}"></script>
@endpush
