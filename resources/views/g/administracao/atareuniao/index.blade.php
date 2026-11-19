@extends('layouts.sistema')
@section('title', 'Ata Reunião')
@section('content_header','Ata Reunião')
@section('content')
    <ata-reuniao></ata-reuniao>
@stop
@push('js')
    @vite(['resources/js/g/administracao/atareuniao/app.js'])
@endpush
