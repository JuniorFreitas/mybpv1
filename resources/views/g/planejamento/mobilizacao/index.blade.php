@extends('layouts.sistema')
@section('title', 'Mobilização')
@section('content_header','Mobilização')
@section('content')
    <mobilizacao></mobilizacao>
@stop
@push('js')
    @vite(['resources/js/g/planejamento/mobilizacao/app.js'])
@endpush
