@extends('layouts.sistema')
@section('title', 'Workflow do Seletivo')
@section('content_header', 'Workflow do Seletivo')
@section('content')
    <etapas-seletivo></etapas-seletivo>
@stop
@push('js')
    <script src="{{ mix('js/g/administracao/etapas-seletivo/app.js') }}"></script>
@endpush
