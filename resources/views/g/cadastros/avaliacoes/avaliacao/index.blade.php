@extends('layouts.sistema')
@section('title', 'Avaliação')
@section('content_header','Avaliação')
@section('content')
    <avaliacao></avaliacao>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliacao/app.js')}}"></script>
@endpush
