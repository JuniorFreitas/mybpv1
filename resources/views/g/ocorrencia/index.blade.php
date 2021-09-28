@extends('layouts.sistema')
@section('title', 'Ocorrências')
@section('content_header', 'Ocorrências')
@section('content')
    <ocorrencia></ocorrencia>
@stop
@push('js')
    <script src="{{mix('js/g/ocorrencia/app.js')}}"></script>
@endpush
