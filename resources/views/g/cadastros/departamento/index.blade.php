@extends('layouts.sistema')
@section('title', 'Departamento')
@section('content_header','Departamento')
@section('content')
    <departamento></departamento>
@stop
@push('js')
    <script src="{{mix('js/g/departamento/app.js')}}"></script>
@endpush
