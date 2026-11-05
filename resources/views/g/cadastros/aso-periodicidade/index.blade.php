@extends('layouts.sistema')
@section('title', 'Periodicidade ASO')
@section('content_header','Periodicidade ASO')
@section('content')
    <aso-periodicidade></aso-periodicidade>
@stop
@push('js')
    <script src="{{mix('js/g/aso-periodicidade/app.js')}}"></script>
@endpush
