@extends('layouts.sistema')
@section('title', 'Instrutores')
@section('content_header','Instrutores')
@section('content')
    <instrutor></instrutor>
@stop
@push('js')
    <script src="{{mix('js/g/instrutor/app.js')}}"></script>
@endpush
