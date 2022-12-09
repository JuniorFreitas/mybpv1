@extends('layouts.sistema')
@section('title', 'Avaliar')
@section('content_header','Avaliar')
@section('content')
    <avaliar></avaliar>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliar/app.js')}}"></script>
@endpush
