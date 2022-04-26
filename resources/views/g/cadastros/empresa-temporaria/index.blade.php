@extends('layouts.sistema')
@section('title', 'Empresa Temporaria')
@section('content_header','Empresa Temporaria')
@section('content')
    <empresa-temporaria></empresa-temporaria>
@stop
@push('js')
    <script src="{{mix('js/g/empresatemporaria/app.js')}}"></script>
@endpush
