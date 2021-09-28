@extends('layouts.sistema')
@section('title', 'Ata Reunião')
@section('content_header','Ata Reunião')
@section('content')
    <ata-reuniao></ata-reuniao>
@stop
@push('js')
    <script src="{{mix('js/g/atareuniao/app.js')}}"></script>
@endpush
