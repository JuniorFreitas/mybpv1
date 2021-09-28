@extends('layouts.sistema')
@section('title', 'Intermitente')
@section('content_header','Intermitente')
@section('content')
    <intermitente></intermitente>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/apontamento/intermitente/app.js')}}"></script>
@endpush
