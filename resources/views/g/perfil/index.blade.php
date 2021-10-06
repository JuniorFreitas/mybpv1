{{--@extends('layouts.sistema')--}}
{{--@section('title', 'Ocorrências')--}}
{{--@section('content_header', 'Ocorrências')--}}
@section('content')
    <perfil></perfil>
@stop
@push('js')
    <script src="{{mix('js/g/perfil/app.js')}}"></script>
@endpush
