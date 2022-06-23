@extends('layouts.sistema')
@section('title', 'Vencimento Asos')
@section('content_header', 'Vencimento Asos')
@section('content')
    <vencimento-asos></vencimento-asos>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/vencimentoasos/app.js')}}"></script>
@endpush
