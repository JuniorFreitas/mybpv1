@extends('layouts.sistema')
@section('title', 'Aniversariantes')
@section('content_header', 'Aniversariantes')
@section('content')
    <aniversariantes></aniversariantes>
@stop
@push('js')
    <script src="{{mix('js/g/aniversariantes/app.js')}}"></script>
@endpush
