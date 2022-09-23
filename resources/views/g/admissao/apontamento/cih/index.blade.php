@extends('layouts.sistema')
@section('title', 'CIH')
@section('content_header')
    <h4 class="text-default">CIH</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
<cih></cih>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/apontamento/cih/app.js')}}"></script>
@endpush
