@extends('layouts.sistema')
@section('title', 'Instrutores')
@section('content_header','Instrutores')
@section('content')
    <instrutor></instrutor>
@stop
@push('js')
    @vite(['resources/js/g/cadastros/instrutor/app.js'])
@endpush
