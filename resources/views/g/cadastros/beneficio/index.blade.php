@extends('layouts.sistema')
@section('title', 'Benefícios')
@section('content_header','Benefícios')
@section('content')
    <beneficio></beneficio>
@stop
@push('js')
    <script src="{{mix('js/g/beneficio/app.js')}}"></script>
@endpush
