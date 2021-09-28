@extends('layouts.sistema')
@section('title', 'Empresa Exame')
@section('content_header','Empresa Exame')
@section('content')
    <empresa-exame></empresa-exame>
@stop
@push('js')
    <script src="{{mix('js/g/empresaexame/app.js')}}"></script>
@endpush
