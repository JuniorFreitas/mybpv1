@extends('layouts.sistema')
@section('title', 'Centro de Custos')
@section('content_header','Centro de Custos')
@section('content')
    <centro-custo></centro-custo>
@stop
@push('js')
    <script src="{{mix('js/g/centrocusto/app.js')}}"></script>
@endpush
