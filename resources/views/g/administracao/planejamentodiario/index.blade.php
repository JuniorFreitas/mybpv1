@extends('layouts.sistema')
@section('title', 'Planejamento Diário')
@section('content_header', 'Planejamento Diário')
@section('content')
    <planejamento-diario></planejamento-diario>
@stop
@push('js')
    <script src="{{mix('js/g/planejamentodiario/app.js')}}"></script>
@endpush
