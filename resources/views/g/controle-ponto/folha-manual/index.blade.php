@extends('layouts.sistema')
@section('title', 'Folha de ponto manual')
@section('content_header', "Folha de ponto manual")
@section('content')
    <folha-manual></folha-manual>
@stop
@push('js')
    <script src="{{mix('js/g/controle-ponto/folha-manual/app.js')}}"></script>
@endpush
