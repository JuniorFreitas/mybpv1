@extends('layouts.sistema')
@section('title', 'Minhas Avaliações')
@section('content_header','Minhas Avaliações')
@section('content')
    <avaliar :current-user-id='@json(auth()->id())' :current-user-name='@json(auth()->user()->nome ?? "")'></avaliar>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/avaliacoes/avaliar/app.js'])
@endpush
