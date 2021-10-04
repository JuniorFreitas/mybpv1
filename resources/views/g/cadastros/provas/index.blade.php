@extends('layouts.sistema')
@section('title', 'Provas')
@section('content_header','Provas')
@section('content')
    <prova></prova>
@stop
@push('js')
    <script src="{{mix('js/g/cadastro/provas/app.js')}}"></script>
@endpush
