@extends('layouts.sistema')
@section('title', 'Administração de Exames')
@section('content_header','Exames')
@section('content')
    <exames-admin-hub></exames-admin-hub>
@stop
@push('js')
    <script src="{{mix('js/g/exames-admin/app.js')}}"></script>
@endpush
