@extends('layouts.sistema')
@section('title', 'Avaliação')
@section('content_header','Avaliação')
@section('content')
    <avaliacao :tipo-pj="true"></avaliacao>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliacao/app.js')}}"></script>
@endpush
